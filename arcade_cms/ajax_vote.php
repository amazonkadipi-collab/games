<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=UTF-8');

require $_SERVER['DOCUMENT_ROOT'] . '/assets/includes/config.php';

$conn = mysqli_connect($dbGM['host'], $dbGM['user'], $dbGM['pass'], $dbGM['name']);
if (!$conn) {
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
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE[$name] = $value;
    return $value;
}

function gps_vote_favorites_table_exists(mysqli $conn): bool
{
    static $exists = null;
    if ($exists !== null) {
        return $exists;
    }
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'favorite_games'");
    $exists = $result && mysqli_num_rows($result) > 0;
    return $exists;
}

function gps_vote_favorite_active(mysqli $conn, int $gameId, string $token, string $favoriteUid, string $ip): bool
{
    $safeToken = mysqli_real_escape_string($conn, $token);
    $action = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$safeToken}' AND action_type='favorite' LIMIT 1");
    if ($action && mysqli_num_rows($action) > 0) {
        return true;
    }

    if (!gps_vote_favorites_table_exists($conn)) {
        return false;
    }
    $identities = array_values(array_unique(array_filter([$favoriteUid, $ip])));
    if (!$identities) {
        return false;
    }
    $safeIdentities = array_map(static function ($identity) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, (string)$identity) . "'";
    }, $identities);
    $favorite = mysqli_query($conn, "SELECT id FROM favorite_games WHERE game_id='{$gameId}' AND user_ip IN (" . implode(',', $safeIdentities) . ') LIMIT 1');
    return $favorite && mysqli_num_rows($favorite) > 0;
}

$visitorToken = gps_vote_cookie('visitor_token', 'visitor_');
$favoriteUid = gps_vote_cookie('gm_fav_uid', 'fav_', 24);
$ipAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '');

if (isset($_GET['action']) && $_GET['action'] === 'get_counts') {
    $gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
    if ($gameId <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing game_id.']);
        exit;
    }

    $result = mysqli_query($conn, "SELECT like_count, dislike_count, favorite_count, plays FROM gm_games WHERE game_id='{$gameId}' LIMIT 1");
    $row = $result ? mysqli_fetch_assoc($result) : null;
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
        'favorite_active' => gps_vote_favorite_active($conn, $gameId, $visitorToken, $favoriteUid, $ipAddress),
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

$token = mysqli_real_escape_string($conn, $visitorToken);
$ip = mysqli_real_escape_string($conn, $ipAddress);
$favoriteIdentity = mysqli_real_escape_string($conn, $favoriteUid);
$favoriteAction = '';

mysqli_begin_transaction($conn);
try {
    if ($type === 'like') {
        $hasLike = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='like' LIMIT 1");
        if ($hasLike && mysqli_num_rows($hasLike) > 0) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'exists', 'message' => 'Already liked.']);
            exit;
        }
        $hasDislike = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='dislike' LIMIT 1");
        if ($hasDislike && mysqli_num_rows($hasDislike) > 0) {
            mysqli_query($conn, "DELETE FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='dislike'");
            mysqli_query($conn, "UPDATE gm_games SET dislike_count=GREATEST(dislike_count-1,0) WHERE game_id='{$gameId}'");
        }
        mysqli_query($conn, "INSERT INTO gm_game_actions (game_id, visitor_token, ip_address, action_type) VALUES ('{$gameId}','{$token}','{$ip}','like')");
        mysqli_query($conn, "UPDATE gm_games SET like_count=like_count+1 WHERE game_id='{$gameId}'");
    } elseif ($type === 'dislike') {
        $hasDislike = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='dislike' LIMIT 1");
        if ($hasDislike && mysqli_num_rows($hasDislike) > 0) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'exists', 'message' => 'Already disliked.']);
            exit;
        }
        $hasLike = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='like' LIMIT 1");
        if ($hasLike && mysqli_num_rows($hasLike) > 0) {
            mysqli_query($conn, "DELETE FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='like'");
            mysqli_query($conn, "UPDATE gm_games SET like_count=GREATEST(like_count-1,0) WHERE game_id='{$gameId}'");
        }
        mysqli_query($conn, "INSERT INTO gm_game_actions (game_id, visitor_token, ip_address, action_type) VALUES ('{$gameId}','{$token}','{$ip}','dislike')");
        mysqli_query($conn, "UPDATE gm_games SET dislike_count=dislike_count+1 WHERE game_id='{$gameId}'");
    } else {
        $hasAction = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='favorite' LIMIT 1");
        $actionExists = $hasAction && mysqli_num_rows($hasAction) > 0;
        $favoriteExists = gps_vote_favorite_active($conn, $gameId, $visitorToken, $favoriteUid, $ipAddress);

        if ($favoriteExists) {
            mysqli_query($conn, "DELETE FROM gm_game_actions WHERE game_id='{$gameId}' AND visitor_token='{$token}' AND action_type='favorite'");
            if (gps_vote_favorites_table_exists($conn)) {
                mysqli_query($conn, "DELETE FROM favorite_games WHERE game_id='{$gameId}' AND user_ip IN ('{$favoriteIdentity}','{$ip}')");
            }
            if ($actionExists) {
                mysqli_query($conn, "UPDATE gm_games SET favorite_count=GREATEST(favorite_count-1,0) WHERE game_id='{$gameId}'");
            }
            $favoriteAction = 'removed';
        } else {
            mysqli_query($conn, "INSERT INTO gm_game_actions (game_id, visitor_token, ip_address, action_type) VALUES ('{$gameId}','{$token}','{$ip}','favorite')");
            if (gps_vote_favorites_table_exists($conn)) {
                mysqli_query($conn, "INSERT IGNORE INTO favorite_games (game_id,user_ip,favorited_at) VALUES ('{$gameId}','{$favoriteIdentity}',NOW())");
            }
            mysqli_query($conn, "UPDATE gm_games SET favorite_count=favorite_count+1 WHERE game_id='{$gameId}'");
            $favoriteAction = 'added';
        }
    }

    $result = mysqli_query($conn, "SELECT like_count, dislike_count, favorite_count, plays FROM gm_games WHERE game_id='{$gameId}' LIMIT 1");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if (!$row) {
        throw new RuntimeException('Game not found.');
    }
    mysqli_commit($conn);

    echo json_encode([
        'status' => 'ok',
        'likes' => (int)$row['like_count'],
        'dislikes' => (int)$row['dislike_count'],
        'favorites' => (int)$row['favorite_count'],
        'plays' => (int)$row['plays'],
        'favorite_active' => $type === 'favorite' ? $favoriteAction === 'added' : gps_vote_favorite_active($conn, $gameId, $visitorToken, $favoriteUid, $ipAddress),
        'favorite_action' => $favoriteAction,
    ]);
} catch (Throwable $exception) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'The request could not be saved.']);
}
