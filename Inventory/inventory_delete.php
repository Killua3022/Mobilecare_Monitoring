<?php
session_start();

/* ===============================
   AUTH CHECK
================================ */
if (!isset($_SESSION['user_id'])) {
    header("Location: /Mobilecare_monitoring/Login/index.php");
    exit;
}

/* ===============================
   DB CONNECTION
================================ */
$conn = new mysqli("localhost", "root", "", "Mobilecare_monitoring");
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

/* ===============================
   ADMIN CHECK
================================ */
if ($_SESSION['email'] !== 'admin@mobilecare.com') {
    die("Access denied. Only admin can delete items.");
}

/* ===============================
   GET ITEM ID
================================ */
if (!isset($_GET['id'])) {
    die("Invalid inventory ID.");
}

$id = (int)$_GET['id'];

/* ===============================
   DELETE ITEM
================================ */
$stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: inventory.php?deleted=1");
    exit;
} else {
    die("Delete failed: " . $stmt->error);
}
