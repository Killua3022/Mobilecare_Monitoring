<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

$conn = new mysqli("localhost", "root", "", "Mobilecare_monitoring");
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

if (!isset($_SESSION['user_id'])) {
    header("Location: /Mobilecare_monitoring/Login/index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$site = $_SESSION['site'] ?? '';
// Fetch inventory item
$stmt = $conn->prepare("SELECT * FROM inventory WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) die("Item not found.");

// Handle form submission
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name   = trim($_POST['item_name']);
    $quantity    = (int)$_POST['quantity'];
    $type        = $_POST['type'];
    $unit_price  = (float)$_POST['unit_price'];
    $serial_no   = trim($_POST['serial_no']) ?: null;
    $part_no     = trim($_POST['part_no']) ?: null;
    $ownership   = trim($_POST['ownership']) ?: null;
    $total_price = $quantity * $unit_price;

    if ($item_name === '' || $quantity < 0 || $unit_price < 0) {
        $error = "Please fill in all required fields with valid values.";
    } else {
        $stmt = $conn->prepare("
            UPDATE inventory
            SET item_name=?, quantity=?, type=?, unit_price=?, total_price=?, serial_no=?, part_no=?, ownership=?
            WHERE id=?
        ");
        if (!$stmt) die("Prepare failed: " . $conn->error);

        $stmt->bind_param(
            "sdsdssssi",
            $item_name,
            $quantity,
            $type,
            $unit_price,
            $total_price,
            $serial_no,
            $part_no,
            $ownership,
            $id
        );

        if ($stmt->execute()) {
            $success = "✅ Inventory item successfully updated.";
            // Refresh data
            $item['item_name'] = $item_name;
            $item['quantity'] = $quantity;
            $item['type'] = $type;
            $item['unit_price'] = $unit_price;
            $item['total_price'] = $total_price;
            $item['serial_no'] = $serial_no;
            $item['part_no'] = $part_no;
            $item['ownership'] = $ownership;
        } else {
            $error = "Update failed: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Inventory Item</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

<script>
function updateTotal() {
    let quantity = parseFloat(document.getElementById('quantity').value) || 0;
    let unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    document.getElementById('total_price').value = (quantity * unitPrice).toFixed(2);
}

// Show/hide Ownership field based on type
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('select[name="type"]');
    const ownershipGroup = document.getElementById('ownership-group');
    
    // Show or hide based on current type
    if (typeSelect.value === 'fixed asset') {
        ownershipGroup.style.display = 'flex';
    } else {
        ownershipGroup.style.display = 'none';
    }

    typeSelect.addEventListener('change', function() {
        if (this.value === 'fixed asset') {
            ownershipGroup.style.display = 'flex';
        } else {
            ownershipGroup.style.display = 'none';
            ownershipGroup.querySelector('input').value = ''; // Clear ownership if hidden
        }
    });
});
</script>
</head>

<body>

<?php require_once __DIR__ . '/../sidebar/sidebar.php'; ?>

<div class="main">
    <div class="card">

        <a href="inventory.php" class="back">← Back</a>
        <h2>✏️ Edit Inventory Item</h2>

        <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>

        <form method="POST" oninput="updateTotal()">

            <div class="section">
                <div class="section-title">Basic Info</div>
                <div class="grid">
                    <div>
                        <label>Item Name *</label>
                        <input name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>" required>
                    </div>
                    <div>
                        <label>Quantity *</label>
                        <input type="number" id="quantity" name="quantity" min="0" step="1" value="<?= $item['quantity'] ?>" required>
                    </div>
                    <div>
                        <label>Type *</label>
                        <select name="type" required>
                            <option value="">Select Type</option>
                            <option value="adhesive" <?= $item['type'] == 'adhesive' ? 'selected' : '' ?>>Adhesive</option>
                            <option value="fixed asset" <?= $item['type'] == 'fixed asset' ? 'selected' : '' ?>>Fixed Asset</option>
                            <option value="consumables" <?= $item['type'] == 'consumables' ? 'selected' : '' ?>>Consumables</option>
                            <option value="others" <?= $item['type'] == 'others' ? 'selected' : '' ?>>Others</option>
                        </select>
                    </div>
                    <div>
                        <label>Unit Price *</label>
                        <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" value="<?= $item['unit_price'] ?>" required>
                    </div>
                    <div>
                        <label>Serial Number</label>
                        <input name="serial_no" value="<?= htmlspecialchars($item['serial_no'] ?? '') ?>" placeholder="Optional">
                    </div>
                    <div>
                        <label>Part Number</label>
                        <input name="part_no" value="<?= htmlspecialchars($item['part_no'] ?? '') ?>" placeholder="Optional">
                    </div>

                    <div class="form-group" id="ownership-group" style="display: <?= $item['type'] === 'fixed asset' ? 'flex' : 'none' ?>;">
                        <label>Ownership</label>
                        <input name="ownership" value="<?= htmlspecialchars($item['ownership'] ?? '') ?>" placeholder="Optional" style="width: 100%; padding: 14px 16px; border-radius: 14px; border: 1px solid var(--border); margin-top: 25px; font-size: 14px;">
                    </div>


                    <div>
                        <label>Total Price</label>
                        <input id="total_price" name="total_price" readonly value="<?= $item['total_price'] ?>">
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Site Information</div>
                <div class="grid">
                    <div>
                        <label>Site</label>
                        <input value="<?= htmlspecialchars($site) ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="inventory.php" class="btn cancel">Cancel</a>
                <button type="submit" class="btn save">Save Changes</button>
            </div>

        </form>

    </div>
</div>

</body>
</html>
