<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'Login/index.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

$id = intval($_GET['id'] ?? 0);

$userEmail = $_SESSION['email'];
$userSite  = $_SESSION['site'];
$isAdmin   = $userEmail === 'admin@mobilecare.com';

/* FETCH RECORD */
if ($isAdmin) {
    $stmt = $conn->prepare("SELECT * FROM chubb_claims WHERE id=? AND is_deleted=0");
    $stmt->bind_param("i",$id);
} else {
    $stmt = $conn->prepare("SELECT * FROM chubb_claims WHERE id=? AND site=? AND is_deleted=0");
    $stmt->bind_param("is",$id,$userSite);
}

$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) die("Access denied");

/* UPDATE */
if ($_SERVER['REQUEST_METHOD']==='POST') {

    if ($isAdmin) {
        $stmt = $conn->prepare("
            UPDATE chubb_claims SET
                claim_no=?,
                claim_date=?,
                serial_number=?,
                model=?,
                unit_replacement=?,
                claimant=?,
                kgb_serial=?,
                special_price=?,
                chubb_pf=?,
                storage_location=?,
                site=?,
                status=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "sssssssdssssi",
            $_POST['claim_no'],
            $_POST['claim_date'],
            $_POST['serial_number'],
            $_POST['model'],
            $_POST['unit_replacement'],
            $_POST['claimant'],
            $_POST['kgb_serial'],
            $_POST['special_price'],
            $_POST['chubb_pf'],
            $_POST['storage_location'],
            $_POST['site'],
            $_POST['status'],
            $id
        );
    } else {
        $stmt = $conn->prepare("
            UPDATE chubb_claims SET
                claim_no=?,
                claim_date=?,
                serial_number=?,
                model=?,
                unit_replacement=?,
                claimant=?,
                kgb_serial=?,
                special_price=?,
                chubb_pf=?,
                storage_location=?,
                status=?
            WHERE id=? AND site=?
        ");
        $stmt->bind_param(
            "sssssssdsssis",
            $_POST['claim_no'],
            $_POST['claim_date'],
            $_POST['serial_number'],
            $_POST['model'],
            $_POST['unit_replacement'],
            $_POST['claimant'],
            $_POST['kgb_serial'],
            $_POST['special_price'],
            $_POST['chubb_pf'],
            $_POST['storage_location'],
            $_POST['status'],
            $id,
            $userSite
        );
    }

    $stmt->execute();
    header("Location: chubb.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Chubb Claim</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#f1f5f9;
    --card:#ffffff;
    --primary:#4f46e5;
    --muted:#64748b;
    --border:#e2e8f0;
}

*{box-sizing:border-box;font-family:Poppins}

body{margin:0;background:var(--bg)}

.main{
    margin-left:260px;
    padding:40px;
    max-width:1200px;
}

.card{
    background:var(--card);
    border-radius:26px;
    padding:40px;
    box-shadow:0 20px 50px rgba(0,0,0,.08);
}

h2{margin:0 0 25px;font-size:26px}

.section{margin-bottom:35px}

.section-title{
    font-size:13px;
    font-weight:600;
    color:#1e293b;
    margin-bottom:18px;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:22px;
}

label{font-size:12px;color:var(--muted)}

input,select{
    width:100%;
    padding:14px 16px;
    border-radius:14px;
    border:1px solid var(--border);
    margin-top:6px;
    font-size:14px;
}

input:focus,select:focus{
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(79,70,229,.15);
}

.actions{
    display:flex;
    gap:15px;
    margin-top:35px;
}

.btn{
    padding:14px 30px;
    border-radius:999px;
    border:none;
    font-weight:600;
    cursor:pointer;
    font-size:14px;
}

.save{background:var(--primary);color:#fff}
.cancel{background:#e5e7eb;color:#111827;text-decoration:none}

@media(max-width:900px){
    .main{margin-left:0;padding:20px}
}
</style>
</head>

<body>

<?php require_once __DIR__.'/../sidebar/sidebar.php'; ?>

<div class="main">
<div class="card">

<h2>Edit Chubb Claim</h2>

<form method="POST">

<!-- CLAIM INFO -->
<div class="section">
    <div class="section-title">Claim Information</div>
    <div class="grid">
        <div>
            <label>Claim No (AR No)</label>
            <input name="claim_no" value="<?= htmlspecialchars($row['claim_no']) ?>" required>
        </div>
        <div>
            <label>Claim Date</label>
            <input type="date" name="claim_date" value="<?= $row['claim_date'] ?>">
        </div>
    </div>
</div>

<!-- DEVICE -->
<div class="section">
    <div class="section-title">Device Information</div>
    <div class="grid">
        <div>
            <label>Serial Number</label>
            <input name="serial_number" value="<?= htmlspecialchars($row['serial_number']) ?>">
        </div>
        <div>
            <label>Model</label>
            <input name="model" value="<?= htmlspecialchars($row['model']) ?>">
        </div>
        <div>
            <label>Unit Replacement</label>
            <input name="unit_replacement" value="<?= htmlspecialchars($row['unit_replacement']) ?>">
        </div>
        <div>
            <label>KGB Serial</label>
            <input name="kgb_serial" value="<?= htmlspecialchars($row['kgb_serial']) ?>">
        </div>
    </div>
</div>

<!-- FINANCIAL -->
<div class="section">
    <div class="section-title">Financial</div>
    <div class="grid">
        <div>
            <label>Special Price</label>
            <input type="number" step="0.01" name="special_price" value="<?= $row['special_price'] ?>">
        </div>
        <div>
            <label>Chubb PF</label>
            <input name="chubb_pf" value="<?= htmlspecialchars($row['chubb_pf']) ?>">
        </div>
    </div>
</div>

<!-- META -->
<div class="section">
    <div class="section-title">Meta</div>
    <div class="grid">
        <div>
            <label>Claimant</label>
            <input name="claimant" value="<?= htmlspecialchars($row['claimant']) ?>">
        </div>
        <div>
            <label>Storage Location</label>
            <input name="storage_location" value="<?= htmlspecialchars($row['storage_location']) ?>">
        </div>

        <?php if($isAdmin): ?>
        <div>
            <label>Site</label>
            <input name="site" value="<?= htmlspecialchars($row['site']) ?>">
        </div>
        <?php endif; ?>

        <div>
            <label>Status</label>
            <select name="status">
                <?php foreach(['Pending','Approved','Rejected','Closed'] as $s): ?>
                    <option <?= $row['status']===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="actions">
    <button class="btn save">Save Changes</button>
    <a href="chubb.php" class="btn cancel">Cancel</a>
</div>

</form>

</div>
</div>

</body>
</html>
