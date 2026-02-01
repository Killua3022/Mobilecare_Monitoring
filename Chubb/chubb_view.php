<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: '.BASE_URL.'Login/index.php');
    exit;
}

$conn = new mysqli('localhost','root','','Mobilecare_monitoring');
if ($conn->connect_error) {
    die('Database connection failed');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid request');
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM chubb_claims WHERE id = ? AND is_deleted = 0 LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die('Claim not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Chubb Claim</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box;font-family:Poppins}
body{margin:0;background:#f1f5f9}

/* LAYOUT */
.main{
    margin-left:260px;
    padding:40px;
    max-width:1200px;
}
@media(max-width:768px){
    .main{margin-left:0;padding:20px}
}

/* CARD */
.card{
    background:#fff;
    border-radius:22px;
    padding:30px;
    box-shadow:0 18px 40px rgba(0,0,0,.08);
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:30px;
}
.header h2{margin:0;font-size:26px}
.header p{margin:6px 0 0;color:#64748b;font-size:14px}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:22px;
}

.item label{
    font-size:13px;
    color:#64748b;
}
.item div{
    margin-top:6px;
    font-weight:600;
    font-size:15px;
}

/* BADGES */
.badge{
    display:inline-block;
    padding:6px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
}
.pending{background:#fef3c7;color:#92400e}
.approved{background:#dcfce7;color:#166534}
.rejected{background:#fee2e2;color:#991b1b}

/* BUTTONS */
.actions{
    display:flex;
    gap:10px;
}
.btn{
    text-decoration:none;
    padding:10px 18px;
    border-radius:999px;
    font-size:14px;
    font-weight:600;
}
.btn-primary{background:#4f46e5;color:#fff}
.btn-secondary{background:#e5e7eb;color:#111827}
</style>
</head>

<body>

<?php require_once __DIR__.'/../sidebar/sidebar.php'; ?>

<div class="main">
    <div class="card">

        <div class="header">
            <div>
                <h2>Chubb Claim Details</h2>
                <p>Complete insurance claim information</p>
            </div>
            <div class="actions">
                <a href="chubb.php" class="btn btn-secondary">← Back</a>
                <a href="chubb_edit.php?id=<?= $data['id'] ?>" class="btn btn-primary">Edit</a>
            </div>
        </div>

        <div class="grid">
            <div class="item"><label>AR No</label><div><?= htmlspecialchars($data['claim_no']) ?></div></div>
            <div class="item"><label>Claim Date</label><div><?= htmlspecialchars($data['claim_date']) ?></div></div>
            <div class="item"><label>Serial Number</label><div><?= htmlspecialchars($data['serial_number']) ?></div></div>
            <div class="item"><label>Model</label><div><?= htmlspecialchars($data['model']) ?></div></div>
            <div class="item"><label>Unit Replacement</label><div><?= htmlspecialchars($data['unit_replacement']) ?></div></div>
            <div class="item"><label>Claimant</label><div><?= htmlspecialchars($data['claimant']) ?></div></div>
            <div class="item"><label>KGB Serial</label><div><?= htmlspecialchars($data['kgb_serial']) ?></div></div>
            <div class="item"><label>Special Price</label><div>₱<?= number_format($data['special_price'],2) ?></div></div>
            <div class="item"><label>Chubb PF</label><div>₱<?= number_format($data['chubb_pf'],2) ?></div></div>
            <div class="item"><label>Storage Location</label><div><?= htmlspecialchars($data['storage_location']) ?></div></div>
            <div class="item"><label>Site</label><div><?= htmlspecialchars($data['site']) ?></div></div>
            <div class="item">
                <label>Status</label>
                <div>
                    <span class="badge <?= strtolower($data['status']) ?>">
                        <?= htmlspecialchars($data['status']) ?>
                    </span>
                </div>
            </div>
            <div class="item"><label>Created At</label><div><?= date('M d, Y h:i A', strtotime($data['created_at'])) ?></div></div>
        </div>

    </div>
</div>

</body>
</html>
