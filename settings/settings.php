<?php
// =====================
// BOOTSTRAP
// =====================
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

// =====================
// AUTH CHECK
// =====================
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'Login/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MobileCare | Settings</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box;font-family:Poppins}

body{
    margin:0;
    background:#f1f5f9;
}

/* LAYOUT */
.main{
    margin-left:260px;
    padding:30px;
}

@media(max-width:768px){
    .main{
        margin-left:0;
        padding:20px;
    }
}

/* PAGE HEADER */
.page-title{
    font-size:26px;
    font-weight:600;
    margin-bottom:20px;
}

/* GRID */
.settings-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:22px;
}

/* CARD */
.card{
    background:#fff;
    padding:28px;
    border-radius:20px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
}

.card h3{
    margin-top:0;
    font-size:18px;
}

.card p{
    color:#475569;
    font-size:14px;
    margin-bottom:18px;
}

.btn{
    display:inline-block;
    padding:10px 16px;
    border-radius:12px;
    text-decoration:none;
    font-size:14px;
    font-weight:500;
    color:#fff;
    background:#4f46e5;
    transition:.2s;
}

.btn:hover{
    background:#4338ca;
}

/* ICON */
.icon{
    font-size:28px;
    margin-bottom:10px;
}
</style>
</head>

<body>

<?php
// Canonical sidebar include
require_once __DIR__ . '/../sidebar/sidebar.php';
?>

<div class="main">

    <div class="page-title">⚙️ Settings</div>

   <div class="settings-grid">

    <!-- PROFILE (Visible to all) -->
    <div class="card">
        <div class="icon">👤</div>
        <h3>Edit Profile</h3>
        <p>
            Update your personal information, email address, and password.
        </p>
        <a href="edit-profile.php" class="btn">Edit Profile</a>
    </div>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- ENGINEERS (Admin Only) -->
        <div class="card">
            <div class="icon">🧑‍🔧</div>
            <h3>Manage Users</h3>
            <p>
                Add, edit, or deactivate engineers assigned to your site.
            </p>
            <a href="engineers.php" class="btn">Manage Users</a>
        </div>

        <!-- SITE (Admin Only) -->
        <div class="card">
            <div class="icon">🏢</div>
            <h3>Manage Site</h3>
            <p>
                Configure site details, permissions, and operational settings.
            </p>
            <a href="site.php" class="btn">Manage Site</a>
        </div>
    <?php endif; ?>

</div>

</div>

</body>
</html>
