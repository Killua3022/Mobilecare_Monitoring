<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

// Get user info
$user_id = $_SESSION['user_id'] ?? 0;
$profile_image = $full_name = $email = $role = '';

if($user_id){
    $stmt = $conn->prepare("SELECT full_name, email, profile_image, role FROM users WHERE id=?");
    if($stmt){
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $stmt->bind_result($full_name, $email, $profile_image, $role);
        $stmt->fetch();
        $stmt->close();
    }
}

$isAdmin = ($role === 'admin');
$display_name = $full_name ?: $email;
$profile_image_url = $profile_image
    ? "/Mobilecare_Monitoring/uploads/".htmlspecialchars($profile_image)
    : "/Mobilecare_Monitoring/uploads/default.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MobileCare | Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
/* ===== BASE ===== */
* { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin:0; padding:0; }
body { background: #f1f5f9; }

/* ===== SIDEBAR ===== */
.sidebar {
    position: fixed;
    top: 0; left: 0;
    width: 260px;
    height: 100vh;
    background: linear-gradient(180deg, #4f46e5, #0ea5e9);
    color: #fff;
    padding: 30px 20px;
    display: flex;
    flex-direction: column;
    box-shadow: 4px 0 25px rgba(0,0,0,0.15);
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
    z-index: 100;
    transition: all 0.3s ease;
}

/* PROFILE */
.profile {
    text-align: center;
    margin-bottom: 35px;
}
.profile img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.6);
    object-fit: cover;
    margin-bottom: 12px;
    transition: transform 0.3s ease;
}
.profile img:hover { transform: scale(1.05); }
.profile .name { font-weight: 600; font-size: 18px; }
.profile .email { font-size: 13px; opacity: 0.85; word-break: break-word; }

/* LOGO */
.logo {
    font-size: 24px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 25px;
    letter-spacing: 1px;
}

/* NAV LINKS */
.nav a {
    display: flex;
    align-items: center;
    padding: 12px 18px;
    border-radius: 12px;
    color: #e0e7ff;
    text-decoration: none;
    margin-bottom: 12px;
    font-size: 15px;
    transition: all 0.3s ease;
    position: relative;
}
.nav a i {
    margin-right: 12px;
    font-size: 20px;
}
.nav a.active, .nav a:hover {
    background: rgba(255,255,255,0.18);
    color: #fff;
}
.nav a::after {
    content: '';
    position: absolute;
    right: 0; top: 0;
    width: 4px; height: 100%;
    background: #fff;
    border-radius: 2px;
    opacity: 0;
    transition: opacity 0.3s;
}
.nav a.active::after, .nav a:hover::after { opacity: 1; }

/* LOGOUT */
.logout {
    margin-top: auto;
    padding: 12px;
    border-radius: 12px;
    background: rgba(0,0,0,0.25);
    text-align: center;
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s;
}
.logout:hover { background: rgba(0,0,0,0.35); }

/* ===== FLOATING MENU ===== */
.floating-menu {
    position: fixed;
    right: 28px;
    bottom: 28px;
    z-index: 1000;
}

.menu-btn {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg,#4f46e5,#0ea5e9);
    color: #fff;
    font-size: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 12px 30px rgba(79,70,229,0.45);
    transition: transform 0.35s ease, box-shadow 0.35s ease;
}
.menu-btn:hover { transform: scale(1.08); box-shadow:0 18px 45px rgba(79,70,229,0.65);}
.menu-btn.active { transform: rotate(135deg) scale(1.05); }

.menu-items {
    position: absolute;
    bottom: 84px; /* above the button */
    right: 0;
    display: flex;
    flex-direction: column-reverse; /* menu pops upwards */
    gap: 12px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(15px) scale(0.95);
    transition: all 0.3s ease;
}
.menu-items.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.menu-item {
    background: #3b82f6;
    color: #fff;
    padding: 14px;
    border-radius: 50%;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    transition: all 0.25s;
    cursor: pointer;
    font-size: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.menu-item:hover { background: #2563eb; transform: translateX(-6px); }

/* RESPONSIVE */
@media(max-width:768px){
    .sidebar{ left: -260px; }
}
</style>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="profile">
        <img src="<?= $profile_image_url ?>">
        <div class="name"><?= htmlspecialchars($display_name) ?></div>
        <div class="email"><?= htmlspecialchars($email) ?></div>
    </div>

    <div class="logo">MobileCare</div>

    <div class="nav">
        <a href="/Mobilecare_Monitoring/dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>"><i class='bx bx-home'></i> Dashboard</a>
        <a href="/Mobilecare_Monitoring/Chubb/chubb.php" class="<?= $current==='chubb.php'?'active':'' ?>"><i class='bx bx-shield'></i> Chubb</a>
        <a href="/Mobilecare_Monitoring/Escalations/escalations.php" class="<?= $current==='escalations.php'?'active':'' ?>"><i class='bx bx-bell'></i> Escalations</a>
        <a href="/Mobilecare_Monitoring/Inventory/inventory.php" class="<?= $current==='inventory.php'?'active':'' ?>"><i class='bx bx-box'></i> Inventory</a>

        <?php if($isAdmin): ?>
            <a href="/Mobilecare_Monitoring/Reports/reports.php" class="<?= $current==='reports.php'?'active':'' ?>"><i class='bx bx-bar-chart-alt-2'></i> Analytics</a>
        <?php else: ?>
            <a href="/Mobilecare_Monitoring/Endorsement_tally/endorsement_tally.php" class="<?= $current==='endorsement_tally.php'?'active':'' ?>"><i class='bx bx-list-check'></i> Endorsements</a>
        <?php endif; ?>

        <a href="/Mobilecare_Monitoring/settings/settings.php" class="<?= $current==='settings.php'?'active':'' ?>"><i class='bx bx-cog'></i> Settings</a>
    </div>

    <a href="/Mobilecare_monitoring/Login/logout.php" class="logout"><i class='bx bx-log-out'></i> Logout</a>
</div>

