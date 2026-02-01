<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    die("Access denied. Only admin can manage sites.");
}


// =====================
// HANDLE ADD / EDIT / DELETE
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $site_id = intval($_POST['site_id'] ?? 0);
    $site_code = trim($_POST['site_code'] ?? '');
    $site_name = trim($_POST['site_name'] ?? '');

    if ($action === 'add' && $site_code !== '' && $site_name !== '') {
        $stmt = $conn->prepare("INSERT INTO sites (site_code, site_name, active) VALUES (?, ?, 1)");
        $stmt->bind_param("ss", $site_code, $site_name);
        $stmt->execute();
        $stmt->close();
    }

    if ($action === 'edit' && $site_id > 0 && $site_code !== '' && $site_name !== '') {
        $stmt = $conn->prepare("UPDATE sites SET site_code=?, site_name=? WHERE id=?");
        $stmt->bind_param("ssi", $site_code, $site_name, $site_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: site.php");
    exit;
}

// DELETE SITE
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['site_id'])) {
    $site_id = intval($_GET['site_id']);
    $stmt = $conn->prepare("DELETE FROM sites WHERE id=?");
    $stmt->bind_param("i", $site_id);
    $stmt->execute();
    $stmt->close();
    header("Location: site.php");
    exit;
}

// FETCH ALL SITES
$sites = $conn->query("SELECT * FROM sites ORDER BY site_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Sites | MobileCare</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;font-family:Poppins,sans-serif;margin:0;padding:0;}
body{background:#f1f5f9;color:#1e293b;}
.main{margin-left:260px;padding:30px;}
@media(max-width:768px){.main{margin-left:0;padding:20px;}}
.page-title{font-size:28px;font-weight:600;margin-bottom:25px;}
.card{background:#fff;padding:25px;border-radius:16px;box-shadow:0 15px 40px rgba(0,0,0,.08);margin-bottom:25px;}
.card h2{margin-bottom:15px;}
table{width:100%;border-collapse:collapse;}
th,td{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left;}
th{color:#475569;}
button,a{padding:6px 12px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;text-decoration:none;}
.add-btn{background:#4f46e5;color:#fff;margin-bottom:15px;}
.add-btn:hover{background:#4338ca;}
.edit-btn{background:#f97316;color:#fff;}
.edit-btn:hover{opacity:.9;}
.delete-btn{background:#ef4444;color:#fff;}
.delete-btn:hover{opacity:.9;}
.back{display:inline-block;margin-bottom:20px;text-decoration:none;color:#4f46e5;font-weight:600;}
.modal-bg{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);display:none;align-items:center;justify-content:center;z-index:999;}
.modal{background:#fff;padding:25px;border-radius:16px;max-width:400px;width:90%;box-shadow:0 10px 25px rgba(0,0,0,.2);position:relative;}
.modal h3{margin-top:0;margin-bottom:15px;font-size:20px;}
.modal input{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;margin-bottom:15px;font-size:14px;}
.modal button{width:100%;padding:10px 12px;border-radius:12px;background:#4f46e5;color:#fff;font-weight:600;font-size:14px;border:none;cursor:pointer;}
.modal button:hover{background:#4338ca;}
.close-modal{position:absolute;top:10px;right:15px;font-size:18px;font-weight:600;cursor:pointer;color:#475569;}
</style>
</head>
<body>

<?php require_once __DIR__ . '/../sidebar/sidebar.php'; ?>

<div class="main">
    <a href="settings.php" class="back">← Back to Settings</a>
    <div class="page-title">🏢 Manage Sites</div>

    <div class="card">
        <button class="add-btn" id="btnAddSite">+ Add New Site</button>
        <table>
            <tr>
                <th>Active Users</th>
                <th>Site Code</th>
                <th>Site Name</th>
                <th>Actions</th>
            </tr>
            <?php while($s = $sites->fetch_assoc()): 
                // Count active users assigned to this site
                $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE account_type=? AND active=1");
                $stmt->bind_param("s", $s['site_code']);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                $activeUsers = $res['cnt'] ?? 0;
                $stmt->close();
            ?>
            <tr>
                <td><?= $activeUsers ?></td>
                <td><?= htmlspecialchars($s['site_code']) ?></td>
                <td><?= htmlspecialchars($s['site_name']) ?></td>
                <td>
                    <button class="edit-btn" data-id="<?= $s['id'] ?>" data-code="<?= htmlspecialchars($s['site_code']) ?>" data-name="<?= htmlspecialchars($s['site_name']) ?>">Edit</button>
                    <a class="delete-btn" href="?action=delete&site_id=<?= $s['id'] ?>" onclick="return confirm('Delete this site?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- MODAL -->
<div class="modal-bg" id="modalBg">
    <div class="modal">
        <span class="close-modal" id="closeModal">&times;</span>
        <h3 id="modalTitle">Add New Site</h3>
        <form method="post">
            <input type="hidden" name="site_id" id="siteId">
            <input type="hidden" name="action" id="modalAction" value="add">
            <input type="text" name="site_code" id="siteCode" placeholder="Enter site code" required>
            <input type="text" name="site_name" id="siteName" placeholder="Enter site name" required>
            <button type="submit" id="modalSubmit">Save</button>
        </form>
    </div>
</div>

<script>
const modalBg = document.getElementById('modalBg');
const btnAddSite = document.getElementById('btnAddSite');
const closeModal = document.getElementById('closeModal');
const modalTitle = document.getElementById('modalTitle');
const modalAction = document.getElementById('modalAction');
const siteIdInput = document.getElementById('siteId');
const siteCodeInput = document.getElementById('siteCode');
const siteNameInput = document.getElementById('siteName');

btnAddSite.addEventListener('click',()=>{
    modalTitle.textContent = 'Add New Site';
    modalAction.value = 'add';
    siteIdInput.value = '';
    siteCodeInput.value = '';
    siteNameInput.value = '';
    modalBg.style.display='flex';
});

closeModal.addEventListener('click',()=>{modalBg.style.display='none';});

document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        modalTitle.textContent='Edit Site';
        modalAction.value='edit';
        siteIdInput.value=btn.dataset.id;
        siteCodeInput.value=btn.dataset.code;
        siteNameInput.value=btn.dataset.name;
        modalBg.style.display='flex';
    });
});

modalBg.addEventListener('click', e=>{if(e.target===modalBg) modalBg.style.display='none';});
</script>

</body>
</html>
