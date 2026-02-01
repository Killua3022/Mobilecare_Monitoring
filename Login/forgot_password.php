<?php
date_default_timezone_set('Asia/Manila');

$conn = new mysqli('localhost', 'root', '', 'Mobilecare_monitoring');
if ($conn->connect_error) die('Database error');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id);

    if ($stmt->fetch()) {
        $stmt->close();

        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $stmt = $conn->prepare(
            "UPDATE users SET reset_token=?, reset_expires=? WHERE id=?"
        );
        $stmt->bind_param("ssi", $token, $expires, $user_id);
        $stmt->execute();

        // DEV MODE – show reset link
        $message = '
        <strong>Password reset link:</strong><br>
        <a href="reset_password.php?token=' . $token . '">
            http://localhost/Mobilecare_monitoring/login/reset_password.php?token=' . $token . '
        </a>';
    } else {
        // Security: don’t reveal if email exists
        $message = 'If this email exists, a reset link has been generated.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:Poppins;background:#f1f5f9;display:flex;align-items:center;justify-content:center;height:100vh}
.box{background:#fff;padding:30px;border-radius:16px;width:100%;max-width:400px;box-shadow:0 15px 30px rgba(0,0,0,.15)}
input,button{width:100%;padding:12px;margin-top:10px;border-radius:10px;border:1px solid #cbd5e1}
button{background:#4f46e5;color:#fff;border:none;font-weight:600}
.msg{margin-top:15px;font-size:14px;word-break:break-all}
a{color:#4f46e5;font-weight:600}
</style>
</head>
<body>
<div class="box">
<h2>Forgot Password</h2>
<form method="POST">
    <input type="email" name="email" placeholder="Your email" required>
    <button type="submit">Send Reset Link</button>
</form>
<div class="msg"><?= $message ?></div>
</div>
</body>
</html>
