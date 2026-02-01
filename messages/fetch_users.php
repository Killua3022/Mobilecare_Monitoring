<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$admin_id = intval($_SESSION['user_id']);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$sql = "
SELECT 
    u.id,
    u.full_name,
    UNIX_TIMESTAMP(u.last_activity) AS last_activity,
    (
        SELECT COUNT(*) 
        FROM messages m 
        WHERE m.sender_id = u.id 
          AND m.receiver_id = ?
          AND m.read_status = 0
    ) AS unread_count
FROM users u
WHERE u.role != 'admin'
ORDER BY u.full_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();

$res = $stmt->get_result();
$users = [];

while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($users);
