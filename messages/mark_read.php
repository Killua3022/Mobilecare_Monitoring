<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])) exit;

$user_id = intval($_SESSION['user_id']);
$sender_id = intval($_POST['sender_id'] ?? 0);

if($sender_id <= 0) exit;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if($conn->connect_error) exit;

$stmt = $conn->prepare("UPDATE messages SET read_status=1 WHERE sender_id=? AND receiver_id=? AND read_status=0");
$stmt->bind_param("ii", $sender_id, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();
?>
