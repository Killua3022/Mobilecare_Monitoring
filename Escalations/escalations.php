<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =====================
   AUTH
===================== */
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'Login/index.php');
    exit;
}



/* =====================
   USER CONTEXT
===================== */
$userEmail = $_SESSION['email'] ?? '';
$userSite  = $_SESSION['site'] ?? '';
$isAdmin   = ($userEmail === 'admin@mobilecare.com');

/* =====================
   FETCH ESCALATIONS
===================== */
if ($isAdmin) {
    $sql = "SELECT * FROM escalations ORDER BY created_at DESC";
    $result = $conn->query($sql);
} else {
    $stmt = $conn->prepare(
        "SELECT * FROM escalations WHERE site = ? ORDER BY created_at DESC"
    );
    if (!$stmt) die("Prepare failed: " . $conn->error);

    $stmt->bind_param("s", $userSite);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Escalations</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
*{font-family:Poppins;box-sizing:border-box}
body{margin:0;background:#f1f5f9}

.main{
    margin-left:260px;
    padding:40px;
    max-width:1400px;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.header h2{margin:0}

.btn{
    background:#4f46e5;
    color:#fff;
    padding:10px 18px;
    border-radius:999px;
    text-decoration:none;
    font-weight:600;
}

.card{
    background:#fff;
    border-radius:18px;
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
}

th{
    background:#f8fafc;
    text-transform:uppercase;
    font-size:11px;
    color:#475569;
}

tr{border-bottom:1px solid #e5e7eb}
tr:last-child{border-bottom:none}

.badge{
    padding:6px 12px;
    border-radius:999px;
    font-size:11px;
    font-weight:600;
}

.badge.open{background:#e0e7ff;color:#3730a3}
.badge.in\ progress{background:#fef3c7;color:#92400e}
.badge.closed{background:#dcfce7;color:#166534}

.actions{
    display:flex;
    gap:8px;
}

.actions a{
    padding:6px 12px;
    border-radius:999px;
    font-size:11px;
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
    <h2>Escalations</h2>
    <!-- Add button always visible -->
    <a href="escalation_add.php" class="btn">+ Add Escalation</a>
</div>

<div class="card">
<table>
<thead>
<tr>
    <th>AR No</th>
    <th>Engineer</th>
    <th>Dispatch ID</th>
    <th>Escalation ID</th>
    <th>Status</th>
    <th>Serial</th>
    <th>Date</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>
<?php if ($result->num_rows === 0): ?>
<tr>
    <td colspan="8" style="text-align:center;color:#64748b;padding:30px">
        No escalation records found
    </td>
</tr>
<?php else: ?>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['ar_number']) ?></td>
    <td><?= htmlspecialchars($row['engineer_name']) ?></td>
    <td><?= htmlspecialchars($row['dispatch_id']) ?></td>
    <td><?= htmlspecialchars($row['escalation_id']) ?></td>

    <td>
        <span class="badge <?= strtolower($row['escalation_status']) ?>">
            <?= htmlspecialchars($row['escalation_status']) ?>
        </span>
    </td>

    <td><?= htmlspecialchars($row['serial_number']) ?></td>
    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>

    <td class="actions">
        <a class="view" href="escalation_view.php?id=<?= $row['id'] ?>">View</a>
          <a class="edit" href="escalation_edit.php?id=<?= $row['id'] ?>">Edit</a>

        <!-- Only show Edit/Delete for admin -->
        <?php if($isAdmin): ?>
            <a class="edit" href="escalation_edit.php?id=<?= $row['id'] ?>">Edit</a>
            <a class="delete"
               href="escalation_delete.php?id=<?= $row['id'] ?>"
               onclick="return confirm('Delete this escalation?')">
               Delete
            </a>
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
