<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status'=>'error','message'=>'User not logged in']);
    exit;
}

$receiver_id = $_POST['receiver_id'] ?? null;

if (!$receiver_id) {
    echo json_encode(['status'=>'error','message'=>'Receiver ID missing']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "Mobilecare_monitoring");
if ($conn->connect_error) {
    echo json_encode(['status'=>'error','message'=>'DB connection failed']);
    exit;
}

// Initialize variables
$message = $_POST['message'] ?? null;
$file_name = null;
$file_path = null;

// Handle file upload
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Mobilecare_monitoring/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $tmpName = $_FILES['file']['tmp_name'];
    $originalName = basename($_FILES['file']['name']);

    // Avoid overwriting: prepend timestamp
    $file_name = $originalName;
    $file_path = '/Mobilecare_monitoring/uploads/' . time() . '_' . $originalName;

    if (!move_uploaded_file($tmpName, $_SERVER['DOCUMENT_ROOT'] . $file_path)) {
        echo json_encode(['status'=>'error','message'=>'Failed to upload file']);
        exit;
    }
}

// Insert into messages table
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, file_name, file_path, read_status, sent_at) VALUES (?,?,?,?,?,0,NOW())");
$stmt->bind_param(
    "iisss",
    $user_id,
    $receiver_id,
    $message,
    $file_name,
    $file_path
);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>$stmt->error]);
}

$stmt->close();
$conn->close();
