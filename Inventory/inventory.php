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
$isAdmin = $_SESSION['email'] === 'admin@mobilecare.com';

/* ===============================
   SEARCH & FILTER HANDLER
================================ */
$search = $_GET['search'] ?? '';
$typeFilter = $_GET['type'] ?? '';

$sql = "SELECT * FROM inventory WHERE 1=1";
$params = [];
$types = ['adhesive', 'fixed asset', 'consumables', 'others'];

// Apply site filter for non-admins
if (!$isAdmin) {
    $sql .= " AND site = ?";
    $params[] = $site;
}

// Apply search filter
if ($search !== '') {
    $sql .= " AND (item_name LIKE ? OR serial_no LIKE ? OR part_no LIKE ?)";
    $likeSearch = "%$search%";
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
}

// Apply type filter
if ($typeFilter !== '' && in_array($typeFilter, $types)) {
    $sql .= " AND type = ?";
    $params[] = $typeFilter;
}

$sql .= " ORDER BY created_at DESC";

if (!$isAdmin || $search || $typeFilter) {
    $stmt = $conn->prepare($sql);

    if ($params) {
        $typesStr = str_repeat('s', count($params));
        $stmt->bind_param($typesStr, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box;font-family:Poppins;margin:0;padding:0}
body{background:#f0f4f8;color:#1e293b}

.main{
    margin-left:260px;
    padding:30px;
}
@media(max-width:768px){.main{margin-left:0;padding:15px}}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
    flex-wrap:wrap;
    gap:10px;
}

.header h2{font-weight:600}

.form-inline{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.form-inline input, .form-inline select{
    padding:8px 12px;
    border-radius:12px;
    border:1px solid #cbd5e1;
    font-size:14px;
}

.btn{
    padding:10px 20px;
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

.card{
    background:#fff;
    border-radius:16px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
    overflow:hidden;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:14px;
    font-size:13px;
    text-align:left;
    border-bottom:1px solid #e5e7eb;
}

th{
    background:#f8fafc;
    text-transform:uppercase;
    font-size:12px;
    color:#475569;
}

tr:last-child td{border-bottom:none}

.actions{
    display:flex;
    gap:8px;
}

.actions a{
    padding:6px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
    text-decoration:none;
}

.view{background:#e0f2fe;color:#0369a1}
.edit{background:#ede9fe;color:#5b21b6}
.delete{background:#fee2e2;color:#991b1b}
</style>
</head>
<body>

<?php require_once __DIR__.'/../sidebar/sidebar.php'; ?>

<div class="main">

<div class="header">
    <h2>Inventory</h2>
    <a href="inventory_add.php" class="btn primary">+ Add Item</a>
</div>

<!-- Search & Filter -->
<form class="form-inline" method="GET" style="margin-bottom:15px;">
    <input type="text" name="search" placeholder="Search by Name, Serial, Part" value="<?= htmlspecialchars($search) ?>">
    <select name="type">
        <option value="">All Types</option>
        <?php foreach($types as $t): ?>
            <option value="<?= $t ?>" <?= $typeFilter === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn secondary">Filter</button>
</form>

<div class="card">
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Item Name</th>
    <th>Serial No</th>
    <th>Part No</th>
    <th>Quantity</th>
    <th>Type</th>
    <th>Ownership</th> <!-- New column added -->
    <th>Unit Price</th>
    <th>Total Price</th>
    <th>Created At</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php if ($result->num_rows === 0): ?>
<tr>
    <td colspan="10" style="text-align:center;color:#64748b;padding:30px">
        No inventory items found
    </td>
</tr>
<?php else: ?>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['item_name']) ?></td>
    <td><?= htmlspecialchars($row['serial_no'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['part_no'] ?? '') ?></td>
    <td><?= $row['quantity'] ?></td>
    <td><?= htmlspecialchars($row['type']) ?></td>
    <td><?= htmlspecialchars($row['ownership'] ?? 'Not specified') ?></td> <!-- Display Ownership -->
    <td><?= number_format($row['unit_price'],2) ?></td>
    <td><?= number_format($row['total_price'],2) ?></td>
    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
    <td class="actions">
        <a class="view" href="inventory_view.php?id=<?= $row['id'] ?>">View</a>
          <a class="edit" href="inventory_edit.php?id=<?= $row['id'] ?>">Edit</a>
        <?php if($isAdmin): ?>
          
            <a class="delete" href="inventory_delete.php?id=<?= $row['id'] ?>"
               onclick="return confirm('Delete this item?')">Delete</a>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
<?php endif; ?>
</tbody>
</table>
</div>

</div>

</body>
</html>
