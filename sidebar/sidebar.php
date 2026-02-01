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
<style>
/* ====== SIDEBAR ====== */
.sidebar{
    position:fixed;
    top:0;left:0;
    width:260px;height:100vh;
    background:linear-gradient(180deg,#4f46e5,#0ea5e9);
    color:#fff;
    padding:25px;
    display:flex;
    flex-direction:column;
    z-index:100;
}

.profile{text-align:center;margin-bottom:30px}
.profile img{
    width:80px;height:80px;border-radius:50%;
    border:2px solid rgba(255,255,255,.5);
    object-fit:cover;margin-bottom:10px
}
.profile .name{font-weight:600;font-size:16px}
.profile .email{font-size:12px;opacity:.85;word-break:break-word}

.logo{font-size:22px;font-weight:600;margin-bottom:15px}

.nav a{
    display:block;
    padding:12px 15px;
    border-radius:12px;
    color:#e0e7ff;
    text-decoration:none;
    margin-bottom:10px;
    font-size:15px;
}
.nav a.active,.nav a:hover{
    background:rgba(255,255,255,.18);
    color:#fff;
}

.logout{
    margin-top:auto;
    padding:12px;
    border-radius:12px;
    background:rgba(0,0,0,.2);
    text-align:center;
    color:#fff;
    text-decoration:none;
}

/* ===== FLOATING MENU ===== */
.floating-menu{
    position:fixed;
    right:28px;
    bottom:28px;
    z-index:1000;
}

/* 🔥 UPGRADED MENU BUTTON 🔥 */
.menu-btn{
    width:64px;
    height:64px;
    border-radius:50%;
    border:none;
    cursor:pointer;

    background:linear-gradient(135deg,#4f46e5,#0ea5e9);
    color:#fff;
    font-size:34px;
    font-weight:300;

    display:flex;
    align-items:center;
    justify-content:center;

    box-shadow:
        0 12px 30px rgba(79,70,229,.45),
        0 0 0 0 rgba(79,70,229,.6);

    transition:
        transform .35s ease,
        box-shadow .35s ease;
}

/* Hover glow */
.menu-btn:hover{
    transform:scale(1.08);
    box-shadow:
        0 18px 45px rgba(79,70,229,.65),
        0 0 0 10px rgba(79,70,229,.15);
}

/* Active rotate */
.menu-btn.active{
    transform:rotate(135deg) scale(1.05);
}

/* MENU ITEMS */
.menu-items{
    position:absolute;
    bottom:84px;
    right:0;
    display:flex;
    flex-direction:column;
    gap:12px;

    opacity:0;
    visibility:hidden;
    transform:translateY(15px) scale(.95);
    transition:.3s ease;
}

.menu-items.show{
    opacity:1;
    visibility:visible;
    transform:translateY(0) scale(1);
}

.menu-item{
    background:#3b82f6;
    color:#fff;
    padding:12px 16px;
    border-radius:14px;
    text-decoration:none;
    box-shadow:0 8px 25px rgba(0,0,0,.2);
    transition:.25s;
}

.menu-item:hover{
    transform:translateX(-6px);
    background:#2563eb;
}

/* MOBILE */
@media(max-width:768px){
    .sidebar{left:-260px}
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
        <a href="/Mobilecare_Monitoring/dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>">Dashboard</a>
        <a href="/Mobilecare_Monitoring/Chubb/chubb.php" class="<?= $current==='chubb.php'?'active':'' ?>">Chubb</a>
        <a href="/Mobilecare_Monitoring/Escalations/escalations.php" class="<?= $current==='escalations.php'?'active':'' ?>">Escalations</a>
        <a href="/Mobilecare_Monitoring/Inventory/inventory.php" class="<?= $current==='inventory.php'?'active':'' ?>">Inventory</a>

        <?php if($isAdmin): ?>
            <a href="/Mobilecare_Monitoring/Reports/reports.php" class="<?= $current==='reports.php'?'active':'' ?>">Analytics</a>
        <?php else: ?>
            <a href="/Mobilecare_Monitoring/Endorsement_tally/endorsement_tally.php" class="<?= $current==='endorsement_tally.php'?'active':'' ?>">Endorsements</a>
        <?php endif; ?>

        <a href="/Mobilecare_Monitoring/settings/settings.php" class="<?= $current==='settings.php'?'active':'' ?>">Settings</a>
    </div>

    <a href="/Mobilecare_monitoring/Login/logout.php" class="logout">Logout</a>
</div>

<!-- FLOATING MENU -->
<div class="floating-menu">
    <button class="menu-btn" id="menuBtn">+</button>

    <div class="menu-items" id="menuItems">
        <?php if(!$isAdmin): ?>
            <a href="/Mobilecare_Monitoring/Endorsement_tally/endorsement_tally.php" class="menu-item">📋 Endorsement</a>
        <?php endif; ?>

        <?php if($isAdmin): ?>
            <a href="/Mobilecare_Monitoring/Reports/reports.php" class="menu-item">📊 Analytics</a>
        <?php endif; ?>

        <a href="/Mobilecare_Monitoring/Inventory/inventory.php" class="menu-item">📦 Inventory</a>
    </div>
</div>

<script>
const menuBtn = document.getElementById('menuBtn');
const menuItems = document.getElementById('menuItems');

menuBtn.addEventListener('click', () => {
    menuBtn.classList.toggle('active');
    menuItems.classList.toggle('show');
});
</script>
