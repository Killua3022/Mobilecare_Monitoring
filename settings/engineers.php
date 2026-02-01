<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =====================
   AUTH CHECK
===================== */
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'Login/index.php');
    exit;
}

$isAdmin = ($_SESSION['email'] ?? '') === 'admin@mobilecare.com';
if (!$isAdmin) die("Access denied. Only admin can manage users.");


/* =====================
   UPDATE ACTIVITY
===================== */
$uid = intval($_SESSION['user_id']);
$conn->query("UPDATE users SET last_activity = NOW() WHERE id = $uid");

/* =====================
   USER ACTIONS
===================== */
if (isset($_GET['deactivate'])) {
    $uid = intval($_GET['deactivate']);
    $res = $conn->query("SELECT email FROM users WHERE id=$uid LIMIT 1");
    $user = $res->fetch_assoc();
    if ($user['email'] !== 'admin@mobilecare.com') {
        $conn->query("UPDATE users SET active=0 WHERE id=$uid");
        $conn->query("DELETE FROM engineers WHERE user_id=$uid");
    }
    header("Location: engineers.php"); exit;
}

if (isset($_GET['activate'])) {
    $uid = intval($_GET['activate']);
    $res = $conn->query("SELECT email FROM users WHERE id=$uid LIMIT 1");
    $user = $res->fetch_assoc();
    if ($user['email'] !== 'admin@mobilecare.com') {
        $conn->query("UPDATE users SET active=1 WHERE id=$uid");
    }
    header("Location: engineers.php"); exit;
}

if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    $res = $conn->query("SELECT email FROM users WHERE id=$uid LIMIT 1");
    $user = $res->fetch_assoc();
    if ($user['email'] !== 'admin@mobilecare.com') {
        $conn->query("DELETE FROM engineers WHERE user_id=$uid");
        $conn->query("DELETE FROM users WHERE id=$uid");
    }
    header("Location: engineers.php"); exit;
}

/* =====================
   FETCH USERS + ACTIVITY
===================== */
$allUsers = $conn->query("
    SELECT 
        id,
        full_name,
        email,
        account_type,
        position,
        active,
        last_activity,
        IF(last_activity >= (NOW() - INTERVAL 5 MINUTE), 1, 0) AS is_online
    FROM users
    ORDER BY email ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users Management | MobileCare</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*{box-sizing:border-box;font-family:Poppins,sans-serif;}
body{margin:0;background:#f1f5f9;color:#1e293b;}
.main{margin-left:260px;padding:30px;}
@media(max-width:768px){.main{margin-left:0;padding:20px;}}
.card{background:#fff;padding:25px;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,.08);margin-bottom:25px;}
h2{margin:0 0 15px;font-weight:600;}
table{width:100%;border-collapse:collapse;}
th,td{padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;text-align:left;}
th{color:#475569;}

.action-btns a{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:36px;height:36px;
    margin-right:6px;
    border-radius:8px;
    font-size:14px;
    text-decoration:none;
}
.deactivate{background:#f97316;color:#fff;}
.activate{background:#10b981;color:#fff;}
.delete{background:#ef4444;color:#fff;}
.deactivate:hover,.activate:hover,.delete:hover{opacity:.85;}

.back{display:inline-block;margin-bottom:20px;text-decoration:none;color:#4f46e5;font-weight:600;}

.badge{
    padding:4px 10px;
    border-radius:6px;
    font-size:12px;
    font-weight:600;
    color:#fff;
    display:inline-flex;
    align-items:center;
    gap:6px;
}
.active-badge{background:#10b981;}
.inactive-badge{background:#f97316;}
.online-badge{background:#22c55e;}
.offline-badge{background:#94a3b8;}
.badge i{font-size:8px;}

.note{font-size:14px;color:#64748b;}
</style>
</head>
<body>

<?php require_once __DIR__ . '/../sidebar/sidebar.php'; ?>

<div class="main">

<a href="settings.php" class="back">← Back to Settings</a>

<div class="card">
<h2>All Users</h2>

<table>
<tr>
    <th>Full Name</th>
    <th>Email</th>
    <th>Site</th>
    <th>Position</th>
    <th>Status</th>
    <th>Activity</th>
    <th>Action</th>
</tr>

<?php while($u = $allUsers->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($u['full_name'] ?: '-') ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= htmlspecialchars($u['account_type']) ?></td>
    <td><?= htmlspecialchars($u['position'] ?: '-') ?></td>

    <td>
        <?php if($u['active']): ?>
            <span class="badge active-badge">Active</span>
        <?php else: ?>
            <span class="badge inactive-badge">Inactive</span>
        <?php endif; ?>
    </td>

    <td>
        <?php if($u['is_online']): ?>
            <span class="badge online-badge"><i class="fas fa-circle"></i> Online</span>
        <?php else: ?>
            <span class="badge offline-badge"><i class="fas fa-circle"></i> Offline</span>
        <?php endif; ?>
    </td>

    <td class="action-btns">
        <?php if($u['email'] !== 'admin@mobilecare.com'): ?>
            <?php if($u['active']): ?>
                <a class="deactivate" href="?deactivate=<?= $u['id'] ?>" onclick="return confirm('Deactivate this user?')">
                    <i class="fas fa-user-slash"></i>
                </a>
            <?php else: ?>
                <a class="activate" href="?activate=<?= $u['id'] ?>" onclick="return confirm('Activate this user?')">
                    <i class="fas fa-user-check"></i>
                </a>
            <?php endif; ?>
            <a class="delete" href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete this user permanently?')">
                <i class="fas fa-trash-alt"></i>
            </a>
        <?php else: ?>
            <span class="note">Admin protected</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

<p class="note">Online = active within last 5 minutes. Admin account is protected.</p>
</div>
</div>

</body>
</html>
