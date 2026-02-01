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
   DB
===================== */
$conn = new mysqli("localhost", "root", "", "Mobilecare_monitoring");
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

/* =====================
   USER CONTEXT
===================== */
$userEmail = $_SESSION['email'] ?? '';
$userSite  = $_SESSION['site'] ?? '';
$isAdmin   = ($userEmail === 'admin@mobilecare.com');

/* =====================
   FETCH CLAIMS
===================== */
if ($isAdmin) {
    $sql = "SELECT * FROM chubb_claims 
            WHERE is_deleted = 0 
            ORDER BY created_at DESC";
    $result = $conn->query($sql);
} else {
    $stmt = $conn->prepare(
        "SELECT * FROM chubb_claims 
         WHERE site = ? AND is_deleted = 0 
         ORDER BY created_at DESC"
    );
    $stmt->bind_param("s", $userSite);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Chubb Claims</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
*{font-family:Poppins;box-sizing:border-box}
body{margin:0;background:#f1f5f9}

.main{
    margin-left:260px;
    padding:40px;
    max-width:1200px;
}

.card{
    background:#fff;
    border-radius:18px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
    overflow:hidden;
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

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:14px;
    text-align:left;
    font-size:14px;
}

th{
    background:#f8fafc;
    text-transform:uppercase;
    font-size:12px;
    color:#475569;
}

tr{border-bottom:1px solid #e5e7eb}
tr:last-child{border-bottom:none}

.badge{
    padding:6px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
}

.badge.pending{background:#fef3c7;color:#92400e}
.badge.approved{background:#dcfce7;color:#166534}
.badge.rejected{background:#fee2e2;color:#991b1b}

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
        <h2>Chubb Claims</h2>
        <a href="chubb_add.php" class="btn">+ Add Claim</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Claim No</th>
                    <th>Serial</th>
                    <th>Model</th>
                    <th>Site</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center;color:#64748b;padding:30px">
                        No claims found
                    </td>
                </tr>
            <?php else: ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['claim_no']) ?></td>
                    <td><?= htmlspecialchars($row['serial_number']) ?></td>
                    <td><?= htmlspecialchars($row['model']) ?></td>
                    <td><?= htmlspecialchars($row['site']) ?></td>
                    <td>
                        <span class="badge <?= strtolower($row['status']) ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td class="actions">

                        <!-- VIEW: ALWAYS -->
                        <a class="view" href="chubb_view.php?id=<?= $row['id'] ?>">View</a>
                         <a class="edit" href="chubb_edit.php?id=<?= $row['id'] ?>">Edit</a>
                        <!-- ADMIN ONLY -->
                        <?php if ($isAdmin): ?>
                            <a class="edit" href="chubb_edit.php?id=<?= $row['id'] ?>">Edit</a>
                            <a class="delete"
                               href="chubb_delete.php?id=<?= $row['id'] ?>"
                               onclick="return confirm('Are you sure you want to delete this claim?')">
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
