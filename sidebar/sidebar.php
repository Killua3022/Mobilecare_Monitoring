<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);

// Database connection
$conn = new mysqli('localhost','root','','Mobilecare_monitoring');
if ($conn->connect_error) die("Database error: ".$conn->connect_error);

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
:root {
    --sidebar-glass-bg: rgba(10, 17, 40, 0.95);
    --sidebar-glass-border: rgba(59, 130, 246, 0.2);
    --sidebar-text: #f1f5f9;
    --sidebar-text-secondary: #94a3b8;
    --sidebar-accent: #3b82f6;
    --sidebar-accent-light: #60a5fa;
    --sidebar-hover: rgba(59, 130, 246, 0.15);
    --sidebar-active: rgba(59, 130, 246, 0.25);
}

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: linear-gradient(180deg, rgba(10, 17, 40, 0.95) 0%, rgba(15, 23, 42, 0.95) 50%, rgba(10, 17, 40, 0.95) 100%);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-right: 1px solid var(--sidebar-glass-border);
    color: var(--sidebar-text);
    padding: 28px 20px;
    display: flex;
    flex-direction: column;
    z-index: 100;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.profile {
    text-align: center;
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid rgba(59, 130, 246, 0.2);
}

.profile img {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    border: 3px solid var(--sidebar-accent);
    object-fit: cover;
    margin-bottom: 12px;
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.profile img:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 30px rgba(59, 130, 246, 0.6);
}

.profile .name {
    font-weight: 600;
    font-size: 16px;
    color: var(--sidebar-text);
    margin-bottom: 4px;
}

.profile .email {
    font-size: 12px;
    color: var(--sidebar-text-secondary);
    word-break: break-word;
}

.logo {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 24px;
    background: linear-gradient(135deg, var(--sidebar-accent-light) 0%, var(--sidebar-accent) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav {
    flex: 1;
    overflow-y: auto;
}

.nav::-webkit-scrollbar {
    width: 4px;
}

.nav::-webkit-scrollbar-track {
    background: transparent;
}

.nav::-webkit-scrollbar-thumb {
    background: var(--sidebar-accent);
    border-radius: 2px;
}

.nav a {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-radius: 10px;
    color: var(--sidebar-text-secondary);
    text-decoration: none;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
}

.nav a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 0;
    background: var(--sidebar-accent);
    border-radius: 0 2px 2px 0;
    transition: height 0.2s ease;
}

.nav a:hover {
    background: var(--sidebar-hover);
    color: var(--sidebar-text);
    padding-left: 20px;
}

.nav a.active {
    background: var(--sidebar-active);
    color: var(--sidebar-text);
    padding-left: 20px;
}

.nav a.active::before,
.nav a:hover::before {
    height: 24px;
}

.logout {
    margin-top: 16px;
    padding: 12px;
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    text-align: center;
    color: #fca5a5;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.logout:hover {
    background: rgba(239, 68, 68, 0.25);
    transform: translateY(-2px);
}

/* ===== FLOATING MENU ===== */
.floating-menu {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 1000;
}

.menu-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg, var(--sidebar-accent) 0%, var(--sidebar-accent-light) 100%);
    color: #fff;
    font-size: 24px;
    font-weight: 300;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    transition: all 0.35s ease;
}

.menu-btn:hover {
    transform: scale(1.08);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5);
}

.menu-btn.active {
    transform: rotate(45deg) scale(1.05);
}

.menu-items {
    position: absolute;
    bottom: 70px;
    right: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
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
    background: linear-gradient(135deg, rgba(10, 17, 40, 0.95) 0%, rgba(15, 23, 42, 0.95) 100%);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: var(--sidebar-text);
    padding: 12px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
    transition: all 0.25s ease;
    white-space: nowrap;
}

.menu-item:hover {
    transform: translateX(-6px);
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.3) 0%, rgba(96, 165, 250, 0.2) 100%);
    border-color: var(--sidebar-accent-light);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

/* MOBILE */
@media(max-width: 768px) {
    .sidebar {
        left: -280px;
        transition: left 0.3s ease;
    }
    
    .sidebar.mobile-open {
        left: 0;
    }
    
    .mobile-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99;
    }
    
    .mobile-overlay.active {
        display: block;
    }
}
</style>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="profile">
        <img src="<?= $profile_image_url ?>" alt="Profile">
        <div class="name"><?= htmlspecialchars($display_name) ?></div>
        <div class="email"><?= htmlspecialchars($email) ?></div>
    </div>

    <div class="logo">MobileCare</div>

    <div class="nav">
        <a href="/Mobilecare_Monitoring/dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Dashboard
        </a>
        <a href="/Mobilecare_Monitoring/Chubb/chubb.php" class="<?= $current==='chubb.php'?'active':'' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            Chubb
        </a>
        <a href="/Mobilecare_Monitoring/Escalations/escalations.php" class="<?= $current==='escalations.php'?'active':'' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            Escalations
        </a>
        <a href="/Mobilecare_Monitoring/Inventory/inventory.php" class="<?= $current==='inventory.php'?'active':'' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
            Inventory
        </a>

        <?php if($isAdmin): ?>
            <a href="/Mobilecare_Monitoring/Reports/reports.php" class="<?= $current==='reports.php'?'active':'' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                Analytics
            </a>
        <?php else: ?>
            <a href="/Mobilecare_Monitoring/Endorsement_tally/endorsement_tally.php" class="<?= $current==='endorsement_tally.php'?'active':'' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                </svg>
                Endorsements
            </a>
        <?php endif; ?>

        <a href="/Mobilecare_Monitoring/settings/settings.php" class="<?= $current==='settings.php'?'active':'' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 1v6m0 6v6m8.66-15.66l-4.24 4.24m-4.24 4.24l-4.24 4.24M23 12h-6m-6 0H1m20.66 8.66l-4.24-4.24m-4.24-4.24l-4.24-4.24"></path>
            </svg>
            Settings
        </a>
    </div>

    <a href="/Mobilecare_monitoring/Login/logout.php" class="logout">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        Logout
    </a>
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

// Close menu when clicking outside
document.addEventListener('click', (e) => {
    if (!menuBtn.contains(e.target) && !menuItems.contains(e.target)) {
        menuBtn.classList.remove('active');
        menuItems.classList.remove('show');
    }
});
</script>