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

// Fetch escalation
$stmt = $conn->prepare("SELECT * FROM escalations WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$escalation = $result->fetch_assoc();
$stmt->close();

if (!$escalation) die("Escalation not found");

// Fetch engineers
$engineers = [];
$sql = "SELECT full_name FROM users WHERE full_name IS NOT NULL AND full_name != '' ORDER BY full_name ASC";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) $engineers[] = $row['full_name'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("
        UPDATE escalations SET
        ar_number=?, engineer_name=?, dispatch_id=?, escalation_id=?,
        escalation_status=?, serial_number=?, unit_description=?,
        css_response=?, remarks=?
        WHERE id=?
    ");
    $stmt->bind_param(
        "sssssssssi",
        $_POST['ar_number'],
        $_POST['engineer_name'],
        $_POST['dispatch_id'],
        $_POST['escalation_id'],
        $_POST['escalation_status'],
        $_POST['serial_number'],
        $_POST['unit_description'],
        $_POST['css_response'],
        $_POST['remarks'],
        $id
    );
    $stmt->execute();
    $stmt->close();

    header("Location: escalations.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Escalation</title>
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
    border-radius:22px;
    padding:40px;
    box-shadow:0 20px 50px rgba(0,0,0,.08);
}

h2{margin:0 0 25px;font-size:26px}

/* SECTION */
.section{margin-bottom:35px}
.section-title{
    font-size:13px;
    font-weight:600;
    color:#1e293b;
    margin-bottom:18px;
    text-transform:uppercase;
    letter-spacing:.05em;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:22px;
}

label{font-size:13px;color:var(--muted)}
input,select,textarea{
    width:100%;
    padding:14px 16px;
    border-radius:14px;
    border:1px solid var(--border);
    margin-top:6px;
    font-size:14px;
}

textarea{resize:none;height:100px}

input:focus,select:focus,textarea:focus{
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(79,70,229,.15);
}

/* BUTTONS */
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

.back{
    display:inline-block;
    margin-bottom:20px;
    color:var(--primary);
    text-decoration:none;
    font-weight:500;
}
.back:hover{color:#4338ca}

@media(max-width:900px){
    .main{margin-left:0;padding:20px}
    .grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<?php require_once __DIR__.'/../sidebar/sidebar.php'; ?>

<div class="main">
<div class="card">

<a href="escalations.php" class="back">← Back</a>
<h2>✏️ Edit Escalation</h2>

<form method="POST">

<!-- BASIC INFO -->
<div class="section">
    <div class="section-title">Basic Info</div>
    <div class="grid">
        <div>
            <label>AR Number</label>
            <input name="ar_number" value="<?= htmlspecialchars($escalation['ar_number']) ?>" required>
        </div>
        <div>
            <label>Engineer</label>
            <select name="engineer_name" required>
                <option value="">Select Engineer</option>
                <?php foreach($engineers as $eng): ?>
                    <option value="<?= htmlspecialchars($eng) ?>" <?= $eng==$escalation['engineer_name']?'selected':'' ?>>
                        <?= htmlspecialchars($eng) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Dispatch ID</label>
            <input name="dispatch_id" value="<?= htmlspecialchars($escalation['dispatch_id']) ?>">
        </div>
        <div>
            <label>Escalation ID</label>
            <input name="escalation_id" value="<?= htmlspecialchars($escalation['escalation_id']) ?>">
        </div>
        <div>
            <label>Status</label>
            <select name="escalation_status" required>
                <option value="">Select Status</option>
                <option <?= $escalation['escalation_status']=='Open'?'selected':'' ?>>Open</option>
                <option <?= $escalation['escalation_status']=='In Progress'?'selected':'' ?>>In Progress</option>
                <option <?= $escalation['escalation_status']=='Closed'?'selected':'' ?>>Closed</option>
            </select>
        </div>
    </div>
</div>

<!-- DETAILS -->
<div class="section">
    <div class="section-title">Details</div>
    <div class="grid">
        <div>
            <label>Serial Number</label>
            <input name="serial_number" value="<?= htmlspecialchars($escalation['serial_number']) ?>">
        </div>
        <div>
            <label>Unit Description</label>
            <input name="unit_description" value="<?= htmlspecialchars($escalation['unit_description']) ?>">
        </div>
        <div>
            <label>CSS Response</label>
            <input name="css_response" value="<?= htmlspecialchars($escalation['css_response']) ?>">
        </div>
        <div style="grid-column:1 / -1">
            <label>Remarks</label>
            <textarea name="remarks"><?= htmlspecialchars($escalation['remarks']) ?></textarea>
        </div>
    </div>
</div>

<div class="actions">
    <button class="btn save">Save Changes</button>
    <a href="escalations.php" class="btn cancel">Cancel</a>
</div>

</form>
</div>
</div>

</body>
</html>
