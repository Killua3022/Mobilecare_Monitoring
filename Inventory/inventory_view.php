<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

$conn = new mysqli("localhost", "root", "", "Mobilecare_monitoring");
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM inventory WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    die("Item not found.");
}

$site = $_SESSION['site'] ?? '';
$isAdmin = $_SESSION['email'] === 'admin@mobilecare.com';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Inventory Item</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; font-family: Poppins, sans-serif; margin: 0; padding: 0; }
        
        body {
            background-color: #f0f4f8;
            color: #1e293b;
        }
        
        .main {
            margin-left: 260px;
            padding: 30px;
            display: flex;
            justify-content: center;
            max-width: 100%;
        }
        
        @media(max-width: 768px) {
            .main {
                margin-left: 0;
                padding: 15px;
            }
        }
        
        .card {
            background-color: #fff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 1000px;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 26px;
            color: #4f46e5;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
        }

        .header p {
            color: #64748b;
            font-size: 14px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 22px;
        }

        .item {
            font-size: 14px;
        }

        .item label {
            color: #64748b;
            font-weight: 600;
            font-size: 13px;
        }

        .item div {
            margin-top: 6px;
            font-weight: 600;
            font-size: 15px;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .open {
            background: #3b82f6;
            color: #fff;
        }

        .in-progress {
            background: #facc15;
            color: #92400e;
        }

        .closed {
            background: #16a34a;
            color: #fff;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 999px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: #fff;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #111827;
            transition: 0.2s;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<?php require_once __DIR__ . '/../sidebar/sidebar.php'; ?>

<div class="main">
    <div class="card">
        <div class="header">
            <div>
                <h2>🔍 Inventory Item Details</h2>
                <p>Complete inventory item information</p>
            </div>
            <div class="actions">
                <a href="inventory.php" class="btn btn-secondary">← Back</a>
                <?php if($isAdmin): ?>
                    <a href="inventory_edit.php?id=<?= $item['id'] ?>" class="btn btn-primary">Edit</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid">
            <div class="item"><label>Item Name</label><div><?= htmlspecialchars($item['item_name']) ?></div></div>
            <div class="item"><label>Serial Number</label><div><?= htmlspecialchars($item['serial_no'] ?? '') ?></div></div>
            <div class="item"><label>Part Number</label><div><?= htmlspecialchars($item['part_no'] ?? '') ?></div></div>
            <div class="item"><label>Quantity</label><div><?= $item['quantity'] ?></div></div>
            <div class="item"><label>Type</label><div><?= htmlspecialchars($item['type']) ?></div></div>
            <div class="item"><label>Unit Price</label><div><?= number_format($item['unit_price'], 2) ?></div></div>
            <div class="item"><label>Total Price</label><div><?= number_format($item['total_price'], 2) ?></div></div>
            <div class="item"><label>Ownership</label><div><?= htmlspecialchars($item['ownership'] ?? 'Not specified') ?></div></div>
            <div class="item"><label>Site</label><div><?= htmlspecialchars($item['site']) ?></div></div>
            <div class="item"><label>Created At</label><div><?= date('M d, Y H:i', strtotime($item['created_at'])) ?></div></div>
        </div>
    </div>
</div>

</body>
</html>
