<?php
session_start();

/* ===============================
   AUTH CHECK
================================ */
if (!isset($_SESSION['user_id'])) {
    header("Location: /Mobilecare_monitoring/Login/index.php");
    exit;
}

/* ===============================
   DB CONNECTION
================================ */
$conn = new mysqli("localhost", "root", "", "Mobilecare_monitoring");
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

/* ===============================
   SESSION DATA
================================ */
$site = $_SESSION['site'] ?? '';
if (!$site) {
    die("Site not set. Please re-login.");
}

/* ===============================
   FORM HANDLER
================================ */
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $claim_no         = trim($_POST['claim_no']);
    $claim_date       = $_POST['claim_date'];
    $serial_number    = trim($_POST['serial_number']);
    $model            = trim($_POST['model']);
    $unit_replacement = trim($_POST['unit_replacement']);
    $claimant         = trim($_POST['claimant']);
    $kgb_serial       = trim($_POST['kgb_serial']);
    $special_price    = $_POST['special_price'] !== '' ? $_POST['special_price'] : null;
    $chubb_pf         = trim($_POST['chubb_pf']);
    $storage_location = trim($_POST['storage_location']);
    $status           = $_POST['status'];

    if (
        $claim_no === '' ||
        $serial_number === '' ||
        $model === '' ||
        $claimant === '' ||
        $claim_date === ''
    ) {
        $error = "Please fill in all required fields.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO chubb_claims (
                claim_no, claim_date, serial_number, model,
                unit_replacement, claimant, kgb_serial, special_price,
                chubb_pf, storage_location, site, status
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        if (!$stmt) die("Prepare failed: " . $conn->error);

        $stmt->bind_param(
            "ssssssssssss",
            $claim_no,
            $claim_date,
            $serial_number,
            $model,
            $unit_replacement,
            $claimant,
            $kgb_serial,
            $special_price,
            $chubb_pf,
            $storage_location,
            $site,
            $status
        );

        if ($stmt->execute()) {
            $success = "✅ Chubb claim successfully added.";
        } else {
            $error = "Insert failed: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Chubb Claim</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box;font-family:Poppins;margin:0;padding:0}
body{background:#f0f4f8;color:#1e293b}

.main{
    margin-left:260px;
    padding:30px;
    display:flex;
    justify-content:center;
}
@media(max-width:768px){.main{margin-left:0;padding:15px}}

.card{
    background:#fff;
    border-radius:16px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
    width:100%;
    max-width:1100px;
    padding:30px;
    transition:all .2s;
}
.card:hover{box-shadow:0 20px 50px rgba(0,0,0,.12)}

.header{
    font-size:20px;
    font-weight:600;
    background:#4f46e5;
    color:#fff;
    padding:15px 20px;
    border-radius:12px;
    margin:-30px -30px 25px -30px;
}

form{display:grid;gap:20px}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}
@media(max-width:768px){.form-grid{grid-template-columns:1fr}}

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group.full{grid-column:1 / -1}

label{
    font-weight:500;
    margin-bottom:6px;
}

input, select, textarea{
    width:100%;
    padding:12px 14px;
    border-radius:12px;
    border:1px solid #cbd5e1;
    font-size:14px;
    transition:all .2s;
}
input:focus, select:focus, textarea:focus{
    outline:none;
    border-color:#4f46e5;
    box-shadow:0 0 0 2px rgba(79,70,229,.15);
}

textarea{grid-column:1 / -1;height:100px;resize:none}

.actions{
    display:flex;
    justify-content:space-between;
    margin-top:30px;
}

.btn{
    padding:12px 22px;
    border-radius:999px;
    border:none;
    font-weight:600;
    cursor:pointer;
    text-decoration:none;
    text-align:center;
    display:inline-block;
}

.btn.primary{background:#4f46e5;color:#fff;transition:.2s}
.btn.primary:hover{background:#4338ca;transform:translateY(-1px)}

.btn.secondary{background:#e5e7eb;color:#111;transition:.2s}
.btn.secondary:hover{background:#d1d5db;transform:translateY(-1px)}

.alert{
    padding:12px 16px;
    border-radius:12px;
    margin-bottom:20px;
    font-size:14px;
}

.alert.success{background:#dcfce7;color:#166534}
.alert.error{background:#fee2e2;color:#991b1b}
</style>
</head>
<body>

<?php require_once __DIR__ . '/../sidebar/sidebar.php'; ?>

<div class="main">
    <div class="card">
        <div class="header">➕ Add Chubb Claim</div>

        <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-grid">

                <div class="form-group">
                    <label>AR No *</label>
                    <input name="claim_no" placeholder="AR Number" required>
                </div>

                <div class="form-group">
                    <label>Claim Date *</label>
                    <input type="date" name="claim_date" required>
                </div>

                <div class="form-group">
                    <label>Serial Number *</label>
                    <input name="serial_number" placeholder="Serial Number" required>
                </div>

                <div class="form-group">
                    <label>Model *</label>
                    <input name="model" placeholder="Model" required>
                </div>

                <div class="form-group">
                    <label>Unit Replacement</label>
                    <input name="unit_replacement" placeholder="Unit Replacement">
                </div>

                <div class="form-group">
                    <label>Claimant *</label>
                    <input name="claimant" placeholder="Claimant" required>
                </div>

                <div class="form-group">
                    <label>KGB Serial</label>
                    <input name="kgb_serial" placeholder="KGB Serial">
                </div>

                <div class="form-group">
                    <label>Special Price</label>
                    <input type="number" step="0.01" name="special_price" placeholder="Special Price">
                </div>

                <div class="form-group">
                    <label>Chubb PF</label>
                    <input name="chubb_pf" placeholder="Chubb PF">
                </div>

                <div class="form-group">
                    <label>Storage Location</label>
                    <input name="storage_location" placeholder="Storage Location">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Released">Released</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label>Site</label>
                    <input value="<?= htmlspecialchars($site) ?>" readonly>
                </div>

            </div>

            <div class="actions">
                <a href="chubb.php" class="btn secondary">← Back</a>
                <button type="submit" class="btn primary">Save Claim</button>
            </div>

        </form>
    </div>
</div>

</body>
</html>
