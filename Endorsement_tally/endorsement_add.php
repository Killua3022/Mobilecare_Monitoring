<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

/* ===============================
   AUTH CHECK
================================ */
if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}

/* ===============================
   DB CONNECTION
================================ */
$conn = new mysqli("localhost","root","","Mobilecare_monitoring");
if($conn->connect_error){
    echo json_encode(['success'=>false,'message'=>'DB connection failed']);
    exit;
}

/* ===============================
   GET POST DATA
================================ */
$engineer_name = $_POST['engineer_name'] ?? '';
$type = $_POST['type'] ?? '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$account_type = $_POST['account_type'] ?? '';

if(empty($engineer_name) || empty($type) || empty($account_type)){
    echo json_encode(['success'=>false,'message'=>'Missing required fields']);
    exit;
}

/* ===============================
   INSERT ENDORSEMENT
================================ */
$stmt = $conn->prepare("
    INSERT INTO endorsements (engineer_name, type, quantity, account_type, created_at)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("ssis", $engineer_name, $type, $quantity, $account_type);

if($stmt->execute()){
    echo json_encode(['success'=>true]);
}else{
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}

$stmt->close();
$conn->close();
?>
