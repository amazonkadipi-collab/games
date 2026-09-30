<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/assets/includes/db.php';
$conn = new ArcadeDatabase([]);
if ($conn->connect_errno) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

function gps_vote_cookie(string $name, string $prefix, int $bytes = 16): string
{
    if (!empty($_COOKIE[$name]) && preg_match('/^[A-Za-z0-9_-]{10,100}$/', (string)$_COOKIE[$name])) {
        return (string)$_COOKIE[$name];
    }
    try {
        $random = bin2hex(random_bytes($bytes));
    } catch (Throwable $exception) {
        $random = hash('sha256', uniqid('', true) . '|' . mt_rand());
    }
    $value = $prefix . substr($random, 0, 48);
    setcookie($name, $value, [
        'expires' => time() + (86400 * 3650),
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE[$name] = $value;
    return $value;
}

function gps_vote_favorite_active(ArcadeDatabase $conn, int $gameId, string $token): bool
{
    $safeToken = $conn->real_escape_string($token);
    $action = $conn->query("SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$safeToken}' AND action_type='favorite' LIMIT 1");
    return $action && $action->num_rows > 0;
}

$visitorToken = gps_vote_cookie('visitor_token', 'visitor_');

if (isset($_GET['action']) && $_GET['action'] === 'get_counts') {
    $gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
    if ($gameId <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing game_id.']);
        exit;
    }
    $result = $conn->query("SELECT like_count, dislike_count, favorite_count, plays FROM gm_games WHERE game_id='{$gameId}' LIMIT 1");
    $row = $result ? $result->fetch_assoc() : null;
    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Game not found.']);
        exit;
    }
    echo json_encode([
        'status' => 'ok',
        'likes' => (int)($row['like_count'] ?? 0),
        'dislikes' => (int)($row['dislike_count'] ?? 0),
        'favorites' => (int)($row['favorite_count'] ?? 0),
        'plays' => (int)($row['plays'] ?? 0),
        'favorite_active' => gps_vote_favorite_active($conn, $gameId, $visitorToken),
    ]);
    exit;
}

$gameId = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$type = isset($_POST['type']) ? trim((string)$_POST['type']) : '';
if ($gameId <= 0 || !in_array($type, ['like', 'dislike', 'favorite'], true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid vote request.']);
    exit;
}

$token = $conn->real_escape_string($visitorToken);
$favoriteAction = '';

if (!$conn->begin_transaction()) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Transaction could not start.']);
    exit;
}

try {
    if ($type === 'like') {
        $hasLike = $conn->query("SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='like' LIMIT 1");
        if ($hasLike && $hasLike->num_rows > 0) {
            $conn->rollback();
            echo json_encode(['status' => 'exists', 'message' => 'Already liked.']);
            exit;
        }
        $hasDislike = $conn->query("SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='dislike' LIMIT 1");
        if ($hasDislike && $hasDislike->num_rows > 0) {
            $conn->query("DELETE FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='dislike'");
            $conn->query("UPDATE gm_games SET dislike_count=GREATEST(dislike_count-1,0) WHERE game_id='{$gameId}'");
        }
        $conn->query("INSERT INTO gm_game_actions (game_id, visitor_token, action_type) VALUES ('{$gameId}','{$token}','like')");
        $conn->query("UPDATE gm_games SET like_count=like_count+1 WHERE game_id='{$gameId}'");
    } elseif ($type === 'dislike') {
        $hasDislike = $conn->query("SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='dislike' LIMIT 1");
        if ($hasDislike && $hasDislike->num_rows > 0) {
            $conn->rollback();
            echo json_encode(['status' => 'exists', 'message' => 'Already disliked.']);
            exit;
        }
        $hasLike = $conn->query("SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='like' LIMIT 1");
        if ($hasLike && $hasLike->num_rows > 0) {
            $conn->query("DELETE FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='like'");
            $conn->query("UPDATE gm_games SET like_count=GREATEST(like_count-1,0) WHERE game_id='{$gameId}'");
        }
        $conn->query("INSERT INTO gm_game_actions (game_id, visitor_token, action_type) VALUES ('{$gameId}','{$token}','dislike')");
        $conn->query("UPDATE gm_games SET dislike_count=dislike_count+1 WHERE game_id='{$gameId}'");
    } else {
        $hasAction = $conn->query("SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='favorite' LIMIT 1");
        $exists = $hasAction && $hasAction->num_rows > 0;
        if ($exists) {
            $conn->query("DELETE FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='favorite'");
            $conn->query("UPDATE gm_games SET favorite_count=GREATEST(favorite_count-1,0) WHERE game_id='{$gameId}'");
            $favoriteAction = 'removed';
        } else {
            $conn->query("INSERT INTO gm_game_actions (game_id, visitor_token, action_type) VALUES ('{$gameId}','{$token}','favorite')");
            $conn->query("UPDATE gm_games SET favorite_count=favorite_count+1 WHERE game_id='{$gameId}'");
            $favoriteAction = 'added';
        }
    }

    $result = $conn->query("SELECT like_count, dislike_count, favorite_count, plays FROM gm_games WHERE game_id='{$gameId}' LIMIT 1");
    $row = $result ? $result->fetch_assoc() : null;
    if (!$row) {
        throw new RuntimeException('Game not found.');
    }
    if (!$conn->commit()) {
        throw new RuntimeException('Transaction commit failed.');
    }

    echo json_encode([
        'status' => 'ok',
        'likes' => (int)$row['like_count'],
        'dislikes' => (int)$row['dislike_count'],
        'favorites' => (int)$row['favorite_count'],
        'plays' => (int)$row['plays'],
        'favorite_active' => $type === 'favorite' ? $favoriteAction === 'added' : gps_vote_favorite_active($conn, $gameId, $visitorToken),
        'favorite_action' => $favoriteAction,
    ]);
} catch (Throwable $exception) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'The request could not be saved.']);
}
