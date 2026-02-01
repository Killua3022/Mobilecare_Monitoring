<?php
$conn = new mysqli('localhost', 'root', '', 'Mobilecare_monitoring');
if ($conn->connect_error) die('Database error');

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (!$token) {
    die('Invalid or missing token.');
}

$stmt = $conn->prepare(
    "SELECT id FROM users WHERE reset_token=? AND reset_expires > NOW()"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->bind_result($user_id);

if (!$stmt->fetch()) {
    die('Invalid or expired token.');
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass1 = $_POST['password'];
    $pass2 = $_POST['confirm_password'];

    if ($pass1 !== $pass2) {
        $error = 'Passwords do not match.';
    } elseif (strlen($pass1) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "UPDATE users 
             SET password=?, reset_token=NULL, reset_expires=NULL 
             WHERE id=?"
        );
        $stmt->bind_param("si", $hash, $user_id);
        $stmt->execute();

        $success = 'Password successfully reset. You may now log in.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:Poppins;background:#f1f5f9;display:flex;align-items:center;justify-content:center;height:100vh}
.box{background:#fff;padding:30px;border-radius:16px;width:100%;max-width:400px;box-shadow:0 15px 30px rgba(0,0,0,.15)}
input,button{width:100%;padding:12px;margin-top:10px;border-radius:10px;border:1px solid #cbd5e1}
button{background:#4f46e5;color:#fff;border:none;font-weight:600}
.error{color:#dc2626;margin-top:10px}
.success{color:#16a34a;margin-top:10px}
a{display:block;margin-top:15px;text-align:center;color:#4f46e5;font-weight:600}
</style>
</head>
<body>
<div class="box">
<h2>Reset Password</h2>

<?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
<?php if($success): ?>
    <div class="success"><?= $success ?></div>
    <a href="index.php">Back to Login</a>
<?php else: ?>
<form method="POST">
    <input type="password" name="password" placeholder="New password" required>
    <input type="password" name="confirm_password" placeholder="Confirm password" required>
    <button type="submit">Reset Password</button>
</form>
<?php endif; ?>

</div>
</body>
</html>
