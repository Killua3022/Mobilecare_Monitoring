<?php
session_start();

/* =====================
   DATABASE CONNECTION
===================== */
$conn = new mysqli('localhost', 'root', '', 'Mobilecare_monitoring');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$error = $success = '';

/* =====================
   FETCH MASTER SITE LIST
===================== */
$sites = [];
$res = $conn->query("SELECT site_code, site_name FROM sites WHERE active = 1 ORDER BY site_name");
while ($row = $res->fetch_assoc()) {
    $sites[] = $row;
}

/* =====================
   LOGIN
===================== */
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, email, password, role, account_type, active FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($uid, $dbEmail, $hash, $role, $site, $active);

    if ($stmt->fetch()) {
        if ($active == 0) {
            $error = "❌ Your account is inactive. Please contact admin.";
        } elseif (password_verify($password, $hash)) {
            $_SESSION['user_id'] = $uid;
            $_SESSION['email']   = $dbEmail;
            $_SESSION['role']    = $role;
            $_SESSION['site']    = $site;

            header("Location: ../dashboard.php");
            exit;
        } else {
            $error = "❌ Invalid email or password";
        }
    } else {
        $error = "❌ Invalid email or password";
    }

    $stmt->close();
}

/* =====================
   SIGN UP
===================== */
if (isset($_POST['action']) && $_POST['action'] === 'signup') {
    $email  = trim($_POST['email']);
    $site   = $_POST['account_type'];
    $position = $_POST['position'];
    $pass1  = $_POST['password'];
    $pass2  = $_POST['confirm_password'];
    $role   = 'user';

    if ($pass1 !== $pass2) {
        $error = "❌ Passwords do not match";
    } elseif (strlen($pass1) < 8) {
        $error = "❌ Password must be at least 8 characters";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "❌ Email already exists";
        } else {
            // Save user with selected site and position
            $hashed = password_hash($pass1, PASSWORD_DEFAULT);
            $insert = $conn->prepare("
                INSERT INTO users (
                    email, password, role, account_type, is_engineer, full_name, personal_id, profile_image, position, active
                ) VALUES (?, ?, ?, ?, 0, NULL, NULL, NULL, ?, 1)
            ");
            $insert->bind_param("sssss", $email, $hashed, $role, $site, $position);

            if ($insert->execute()) {
                $success = "✅ Account created successfully. You can now log in.";
            } else {
                $error = "❌ Something went wrong: " . $conn->error;
            }

            $insert->close();
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MobileCare | Auth</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;font-family:Poppins}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#4f46e5,#0ea5e9)}
.card{width:100%;max-width:430px;background:#fff;border-radius:22px;box-shadow:0 25px 50px rgba(0,0,0,.25);overflow:hidden}
.header{text-align:center;padding:30px;background:#f8fafc}
.header h1{margin:0;font-weight:600}
.tabs{display:flex}
.tabs button{flex:1;padding:15px;border:0;background:#e5e7eb;font-weight:600;cursor:pointer}
.tabs button.active{background:#fff}
.form{padding:30px;display:none}
.form.active{display:block}
input,select{width:100%;padding:13px;margin:10px 0;border-radius:12px;border:1px solid #cbd5e1}
button.submit{width:100%;padding:13px;border-radius:12px;border:0;background:#4f46e5;color:#fff;font-size:16px;font-weight:600;cursor:pointer}
.message{text-align:center;margin:10px;font-size:14px}
.error{color:#dc2626}
.success{color:#16a34a}
.small{text-align:center;font-size:14px;margin-top:12px}
@media(max-width:480px){.card{margin:12px}}
</style>
</head>
<body>

<div class="card">
  <div class="header">
    <h1>MobileCare</h1>
    <p>Secure access per site</p>
  </div>

  <div class="tabs">
    <button class="tab active" onclick="switchForm('login',this)">Login</button>
    <button class="tab" onclick="switchForm('signup',this)">Sign Up</button>
  </div>

  <?php if($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if($success): ?><div class="message success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <!-- LOGIN -->
  <form class="form active" id="login" method="POST">
    <input type="hidden" name="action" value="login">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button class="submit">Login</button>
    <div class="small">
        <a href="forgot_password.php">Forgot Password?</a>
    </div>
  </form>

  <!-- SIGN UP -->
  <form class="form" id="signup" method="POST">
    <input type="hidden" name="action" value="signup">
    <input type="email" name="email" placeholder="Email" required>

    <!-- Site Dropdown -->
    <select name="account_type" required>
      <option value="">Select MobileCare Site</option>
      <?php foreach ($sites as $s): ?>
        <option value="<?= htmlspecialchars($s['site_code']) ?>">
            <?= htmlspecialchars($s['site_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- Position Dropdown -->
    <select name="position" required>
      <option value="">Select Position</option>
      <option value="Engineer">Engineer</option>
      <option value="Supervisor">Supervisor</option>
      <option value="Customer Service">Customer Service</option>
      <option value="Parts Management Analyst">Parts Management Analyst</option>
    </select>

    <input type="password" name="password" placeholder="Password (min 8 chars)" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button class="submit">Create Account</button>
  </form>
</div>

<script>
function switchForm(id,btn){
 document.querySelectorAll('.form').forEach(f=>f.classList.remove('active'));
 document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
 document.getElementById(id).classList.add('active');
 btn.classList.add('active');
}
</script>

</body>
</html>
