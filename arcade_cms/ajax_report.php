<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/assets/includes/db.php';

$conn = new ArcadeDatabase([]);
if ($conn->connect_errno) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$game_id     = (int)($_POST['game_id'] ?? 0);
$game_name   = trim((string)($_POST['game_name'] ?? ''));
$user_email  = trim((string)($_POST['user_email'] ?? ''));
$report_type = trim((string)($_POST['report_type'] ?? ''));
$subject     = trim((string)($_POST['subject'] ?? ''));
$message     = trim((string)($_POST['message'] ?? ''));

if (!$game_id || !$game_name || !$user_email || !$report_type || !$subject || !$message) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
    exit;
}

$game_id     = (int)$game_id;
$game_name   = $conn->real_escape_string($game_name);
$user_email  = $conn->real_escape_string($user_email);
$report_type = $conn->real_escape_string($report_type);
$subject     = $conn->real_escape_string($subject);
$message     = $conn->real_escape_string($message);

$sql = "INSERT INTO gm_reports
(game_id, game_name, user_email, report_type, subject, message)
VALUES
('$game_id', '$game_name', '$user_email', '$report_type', '$subject', '$message')";

if (!$conn->query($sql)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Report could not be saved.']);
    exit;
}

echo json_encode([
    'status' => 'ok',
    'message' => 'Your report has been submitted successfully.'
]);
