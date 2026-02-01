<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

$conn = new mysqli("localhost","root","","Mobilecare_monitoring");
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM escalations WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$escalation = $result->fetch_assoc();
$stmt->close();

if (!$escalation) die("Escalation not found");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Escalation</title>
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
.open{background:#3b82f6;color:#fff}
.in-progress{background:#facc15;color:#92400e}
.closed{background:#16a34a;color:#fff}

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
                <h2>Escalation Details</h2>
                <p>Complete escalation information</p>
            </div>
            <div class="actions">
                <a href="escalations.php" class="btn btn-secondary">← Back</a>
                <a href="escalation_edit.php?id=<?= $escalation['id'] ?>" class="btn btn-primary">Edit</a>
            </div>
        </div>

        <div class="grid">
            <div class="item"><label>AR Number</label><div><?= htmlspecialchars($escalation['ar_number']) ?></div></div>
            <div class="item"><label>Engineer</label><div><?= htmlspecialchars($escalation['engineer_name']) ?></div></div>
            <div class="item"><label>Dispatch ID</label><div><?= htmlspecialchars($escalation['dispatch_id']) ?></div></div>
            <div class="item"><label>Escalation ID</label><div><?= htmlspecialchars($escalation['escalation_id']) ?></div></div>
            <div class="item"><label>Status</label>
                <div>
                    <span class="badge <?= strtolower($escalation['escalation_status']) ?>">
                        <?= htmlspecialchars($escalation['escalation_status']) ?>
                    </span>
                </div>
            </div>
            <div class="item"><label>Serial Number</label><div><?= htmlspecialchars($escalation['serial_number']) ?></div></div>
            <div class="item"><label>Unit Description</label><div><?= htmlspecialchars($escalation['unit_description']) ?></div></div>
            <div class="item"><label>CSS Response</label><div><?= htmlspecialchars($escalation['css_response']) ?></div></div>
            <div class="item" style="grid-column:1 / -1"><label>Remarks</label><div><?= nl2br(htmlspecialchars($escalation['remarks'])) ?></div></div>
            <div class="item"><label>Site</label><div><?= htmlspecialchars($escalation['site']) ?></div></div>
            <div class="item"><label>Created At</label><div><?= date('M d, Y h:i A', strtotime($escalation['created_at'])) ?></div></div>
        </div>

    </div>
</div>

</body>
</html>
