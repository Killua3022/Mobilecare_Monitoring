<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$selected_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($selected_user <= 0) {
    echo json_encode([]);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

/* Mark messages as read */
$mark = $conn->prepare("
    UPDATE messages 
    SET read_status = 1 
    WHERE receiver_id = ? 
      AND sender_id = ? 
      AND read_status = 0
");
$mark->bind_param("ii", $user_id, $selected_user);
$mark->execute();
$mark->close();

/* Fetch messages */
$stmt = $conn->prepare("
    SELECT 
        m.id,
        m.sender_id,
        m.receiver_id,
        m.message,
        m.file_name,
        m.file_path,
        m.read_status,
        m.sent_at,
        u.full_name AS sender_name
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE 
        (m.sender_id = ? AND m.receiver_id = ?)
        OR
        (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.sent_at ASC
");
$stmt->bind_param("iiii", $user_id, $selected_user, $selected_user, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($messages);
