<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: '.BASE_URL.'Login/index.php');
    exit;
}


$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $site  = $_POST['site'];

    $stmt = $conn->prepare("UPDATE engineers SET name=?, email=?, site=? WHERE id=?");
    $stmt->bind_param("sssi",$name,$email,$site,$id);
    $stmt->execute();
    $stmt->close();

    header("Location: engineers.php");
    exit;
}

$engineer = $conn->query("SELECT * FROM engineers WHERE id=$id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Engineer</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body{margin:0;background:#f1f5f9;font-family:Poppins}
.main{margin-left:260px;padding:30px}
.card{background:#fff;padding:30px;border-radius:20px}
input{width:100%;padding:10px;margin-bottom:15px}
button{padding:10px 16px;border:none;background:#4f46e5;color:#fff;border-radius:10px}
a{color:#4f46e5;text-decoration:none}
</style>
</head>

<body>

<?php require_once __DIR__.'/../sidebar/sidebar.php'; ?>

<div class="main">
<div class="card">
<a href="engineers.php">← Back</a>
<h2>Edit Engineer</h2>

<form method="post">
    <input name="name" value="<?= htmlspecialchars($engineer['name']) ?>" required>
    <input name="email" value="<?= htmlspecialchars($engineer['email']) ?>" required>
    <input name="site" value="<?= htmlspecialchars($engineer['site']) ?>" required>
    <button>Save Changes</button>
</form>

</div>
</div>

</body>
</html>
