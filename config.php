<?php
// =====================
// CONFIG (LOAD ONCE)
// =====================

// Prevent double include
if (defined('CONFIG_LOADED')) {
    return;
}
define('CONFIG_LOADED', true);

// =====================
// SESSION
// =====================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================
// BASE URL
// =====================
define('BASE_URL', '/Mobilecare_monitoring/');

// =====================
// DATABASE
// =====================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'Mobilecare_monitoring');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// =====================
// GET USER IP
// =====================
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}

// =====================
// UPDATE USER ACTIVITY + IP
// =====================
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $ip = $conn->real_escape_string(getUserIP());
    $conn->query("UPDATE users SET last_activity = NOW(), last_ip = '$ip' WHERE id = $uid");
}
