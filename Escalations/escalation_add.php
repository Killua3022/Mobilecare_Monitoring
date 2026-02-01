<?php
session_start();

$conn = new mysqli("localhost","root","","Mobilecare_monitoring");
if ($conn->connect_error) die("Database connection failed");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/index.php");
    exit;
}

$site = $_SESSION['site'] ?? 'ADMIN';

/* =======================
   FETCH ENGINEERS
======================= */
$engineers = [];
$sql = "SELECT full_name FROM users WHERE full_name IS NOT NULL AND full_name != '' ORDER BY full_name ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $engineers[] = $row['full_name'];
    }
}

/* SAVE ESCALATION */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("
        INSERT INTO escalations
        (ar_number, engineer_name, dispatch_id, escalation_id,
         escalation_status, serial_number, unit_description,
         css_response, remarks, site)
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ");

    if (!$stmt) die("Prepare failed: " . $conn->error);

    $stmt->bind_param(
        "ssssssssss",
        $_POST['ar_number'],
        $_POST['engineer_name'],
        $_POST['dispatch_id'],
        $_POST['escalation_id'],
        $_POST['escalation_status'],
        $_POST['serial_number'],
        $_POST['unit_description'],
        $_POST['css_response'],
        $_POST['remarks'],
        $site
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
<title>Add Escalation | MobileCare</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#f1f5f9;
    --card:#fff;
    --primary:#4f46e5;
    --primary-hover:#4338ca;
    --muted:#6b7280;
    --border:#cbd5e1;
    --shadow:0 15px 40px rgba(0,0,0,.08);
}

*{box-sizing:border-box;font-family:Poppins;margin:0;padding:0}
body{background:var(--bg);color:#111827}

.main{margin-left:260px;padding:30px;display:flex;justify-content:center}
@media(max-width:768px){.main{margin-left:0;padding:15px}}

.card{
    background:var(--card);
    border-radius:16px;
    box-shadow:var(--shadow);
    width:100%;
    max-width:1100px;
    padding:30px;
    transition:all .2s;
}
.card:hover{box-shadow:0 20px 50px rgba(0,0,0,.12)}

.header{
    font-size:20px;
    font-weight:600;
    background:var(--primary);
    color:#fff;
    padding:15px 20px;
    border-radius:12px;
    margin:-30px -30px 25px -30px;
}

/* BACK BUTTON */
.back{
    display:inline-block;
    margin-bottom:20px;
    color:var(--primary);
    font-weight:500;
    text-decoration:none;
    transition:.2s;
}
.back:hover{color:var(--primary-hover)}

form{display:grid;gap:20px}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}
@media(max-width:768px){.form-grid{grid-template-columns:1fr}}

input, select, textarea{
    width:100%;
    padding:12px 14px;
    border-radius:12px;
    border:1px solid var(--border);
    font-size:14px;
    transition:all .2s;
}
input:focus, select:focus, textarea:focus{
    border-color:var(--primary);
    outline:none;
    box-shadow:0 0 0 2px rgba(79,70,229,.15);
}
textarea{grid-column:1 / -1;resize:none;height:100px}

button{
    background:var(--primary);
    color:#fff;
    border:none;
    border-radius:12px;
    padding:14px 20px;
    font-weight:600;
    cursor:pointer;
    transition:all .2s;
}
button:hover{background:var(--primary-hover);transform:translateY(-1px)}

label{
    font-weight:500;
    margin-bottom:5px;
    display:block;
}
</style>
</head>
<body>

<?php require_once __DIR__.'/../sidebar/sidebar.php'; ?>

<div class="main">
<div class="card">

    <div class="header">➕ Add Escalation Entry</div>

    <!-- BACK BUTTON -->
    <a href="escalations.php" class="back">← Back to Escalations</a>

    <form method="POST">

        <div class="form-grid">
            <div>
                <label>AR Number <span style="color:red">*</span></label>
                <input name="ar_number" placeholder="AR Number" required>
            </div>

            <div>
                <label>Engineer <span style="color:red">*</span></label>
                <select name="engineer_name" required>
                    <option value="">Select Engineer</option>
                    <?php foreach ($engineers as $eng): ?>
                        <option value="<?= htmlspecialchars($eng) ?>"><?= htmlspecialchars($eng) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Dispatch ID</label>
                <input name="dispatch_id" placeholder="Dispatch ID">
            </div>

            <div>
                <label>Escalation ID</label>
                <input name="escalation_id" placeholder="Escalation ID">
            </div>

            <div>
                <label>Escalation Status <span style="color:red">*</span></label>
                <select name="escalation_status" required>
                    <option value="">Select Status</option>
                    <option>Open</option>
                    <option>In Progress</option>
                    <option>Closed</option>
                </select>
            </div>

            <div>
                <label>Serial Number</label>
                <input name="serial_number" placeholder="Serial Number">
            </div>

            <div>
                <label>Unit Description</label>
                <input name="unit_description" placeholder="Unit Description">
            </div>

            <div>
                <label>CSS Response</label>
                <input name="css_response" placeholder="CSS Response">
            </div>

            <div style="grid-column:1 / -1">
                <label>Remarks</label>
                <textarea name="remarks" placeholder="Remarks"></textarea>
            </div>
        </div>

        <button type="submit">Add Escalation Entry</button>

    </form>
</div>
</div>

</body>
</html>
