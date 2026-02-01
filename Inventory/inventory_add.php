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
if (!$site) die("Site not set. Please re-login.");

/* ===============================
   FORM HANDLER
================================ */
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $item_name   = trim($_POST['item_name']);
    $quantity    = (int) $_POST['quantity'];
    $type        = strtolower($_POST['type'] ?? 'others'); // ensure lowercase to match ENUM
    $unit_price  = (float) $_POST['unit_price'];
    $serial_no   = trim($_POST['serial_no']) ?: null; // optional
    $part_no     = trim($_POST['part_no']) ?: null;   // optional
    $ownership   = trim($_POST['ownership']) ?: null;  // ownership if applicable
    $total_price = $quantity * $unit_price;

    if ($item_name === '' || $type === '' || $quantity < 0 || $unit_price < 0) {
        $error = "Please fill in all required fields with valid values.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO inventory 
            (item_name, quantity, type, unit_price, total_price, site, serial_no, part_no, ownership)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");

        if (!$stmt) die("Prepare failed: " . $conn->error);

        $stmt->bind_param(
            "sdsdsssss",
            $item_name,
            $quantity,
            $type,
            $unit_price,
            $total_price,
            $site,
            $serial_no,
            $part_no,
            $ownership
        );

        if ($stmt->execute()) {
            $success = "✅ Inventory item successfully added.";
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
<title>Add Inventory Item</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box;font-family:Poppins;margin:0;padding:0}
body{background:#f1f5f9;color:#1e293b}

.main{
    margin-left:260px;
    padding:30px;
    max-width:950px;
}
@media(max-width:768px){.main{margin-left:0;padding:20px}}

.card{
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 20px 40px rgba(0,0,0,.08);
}

h2{margin-bottom:20px}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}
@media(max-width:768px){.form-grid{grid-template-columns:1fr}}

.form-group{display:flex;flex-direction:column}
.form-group.full{grid-column:1 / -1}

label{font-size:14px;font-weight:600;margin-bottom:6px}

input, select{
    padding:12px;
    border-radius:12px;
    border:1px solid #cbd5e1;
}
input:focus, select:focus{border-color:#4f46e5;outline:none;box-shadow:0 0 0 2px rgba(79,70,229,.15)}

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
}

.btn.primary{background:#4f46e5;color:#fff;transition:.2s}
.btn.primary:hover{background:#4338ca;transform:translateY(-1px)}

.btn.secondary{background:#e5e7eb;color:#111;transition:.2s}
.btn.secondary:hover{background:#d1d5db;transform:translateY(-1px)}

.alert{padding:12px;border-radius:12px;margin-bottom:20px}
.alert.success{background:#dcfce7;color:#166534}
.alert.error{background:#fee2e2;color:#991b1b}

#ownershipField { display: none; }
</style>

<script>
function toggleOwnershipField() {
    const type = document.querySelector('select[name="type"]').value;
    const ownershipField = document.getElementById('ownershipField');
    
    if (type === 'fixed asset') {
        ownershipField.style.display = 'block';
    } else {
        ownershipField.style.display = 'none';
    }
}
</script>

</head>
<body>

<?php require_once __DIR__ . '/../sidebar/sidebar.php'; ?>

<div class="main">
<div class="card">

    <h2>➕ Add Inventory Item</h2>

    <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>

    <form method="POST" oninput="toggleOwnershipField()">

        <div class="form-grid">

            <div class="form-group">
                <label>Item Name *</label>
                <input name="item_name" required>
            </div>

            <div class="form-group">
                <label>Quantity *</label>
                <input type="number" name="quantity" min="0" value="0" required>
            </div>

            <div class="form-group">
                <label>Type *</label>
                <select name="type" required>
                    <option value="adhesive">Adhesive</option>
                    <option value="fixed asset">Fixed Asset</option>
                    <option value="consumables">Consumables</option>
                    <option value="others" selected>Others</option>
                </select>
            </div>

            <div class="form-group">
                <label>Unit Price *</label>
                <input type="number" step="0.01" name="unit_price" min="0" value="0.00" required>
            </div>

            <div class="form-group">
                <label>Serial Number</label>
                <input name="serial_no" placeholder="Optional">
            </div>

            <div class="form-group">
                <label>Part Number</label>
                <input name="part_no" placeholder="Optional">
            </div>

            <!-- Ownership field shown only when 'Fixed Asset' is selected -->
            <div class="form-group" id="ownershipField">
                <label>Ownership</label>
                <input name="ownership" placeholder="Required for Fixed Assets">
            </div>

            <div class="form-group full">
                <label>Site</label>
                <input value="<?= htmlspecialchars($site) ?>" readonly>
            </div>

        </div>

        <div class="actions">
            <a href="inventory.php" class="btn secondary">← Back</a>
            <button class="btn primary">Save Item</button>
        </div>

    </form>

</div>
</div>

</body>
</html>
