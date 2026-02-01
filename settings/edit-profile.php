<?php
// =====================
// DEBUG ERRORS
// =====================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'Login/index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch user info
$sql = "SELECT email, password, full_name, personal_id, profile_image, position, account_type FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $hashed_password, $full_name, $personal_id, $profile_image, $position, $account_type);
$stmt->fetch();
$stmt->close();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_full_name = trim($_POST['full_name'] ?? '');
    $new_personal_id = trim($_POST['personal_id'] ?? '');
    $new_position = trim($_POST['position'] ?? '');
    $upload_error = false;

    if (empty($new_full_name)) {
        $message = "❌ Full Name is required.";
    } elseif (empty($new_position)) {
        $message = "❌ Position is required.";
    } else {

        // Profile Image Upload
        if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === 0) {
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_'.$user_id.'_'.time().'.'.$ext;
            $upload_dir = $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/uploads/';

            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            if (!is_writable($upload_dir)) {
                $upload_error = true;
                $message = "❌ Cannot write to uploads folder. Please check folder permissions.";
            } elseif (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir.$filename)) {
                $upload_error = true;
                $message = "❌ Failed to upload profile image. Please check folder permissions.";
            } else {
                $profile_image = $filename;
            }
        }

        // Update profile info (account_type stays same)
        $update = $conn->prepare("UPDATE users SET full_name=?, personal_id=?, position=?, profile_image=? WHERE id=?");
        $update->bind_param("ssssi", $new_full_name, $new_personal_id, $new_position, $profile_image, $user_id);
        $update->execute();
        $update->close();

        // Password change
        if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
            $current = $_POST['current_password'];
            $new     = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];

            if (!password_verify($current, $hashed_password)) {
                $message = "❌ Current password is incorrect.";
            } elseif (strlen($new) < 6) {
                $message = "❌ New password must be at least 6 characters.";
            } elseif ($new !== $confirm) {
                $message = "❌ New passwords do not match.";
            } else {
                $new_hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->bind_param("si", $new_hash, $user_id);
                $stmt->execute();
                $stmt->close();
                $message = "✅ Profile and password updated successfully.";
            }
        } else {
            if (empty($message) && !$upload_error) $message = "✅ Profile updated successfully.";
            if ($upload_error && strpos($message,'❌') === false) {
                $message = "✅ Profile updated successfully, but image upload failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MobileCare | Edit Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;font-family:Poppins}
body{margin:0;background:#f0f4f8}
.main{margin-left:260px;padding:30px;display:flex;justify-content:center;gap:30px;flex-wrap:wrap;}
@media(max-width:1024px){.main{flex-direction:column;margin-left:0;padding:15px}}
.card{background:#fff;padding:30px;border-radius:20px;box-shadow:0 15px 40px rgba(0,0,0,.08);flex:1;min-width:300px}
.card:hover{box-shadow:0 20px 50px rgba(0,0,0,.12)}
.back{display:inline-block;margin-bottom:20px;font-size:14px;text-decoration:none;color:#4f46e5;font-weight:500;transition:.2s}
.back:hover{text-decoration:underline;color:#4338ca}
h2{margin-bottom:20px;font-weight:600;color:#1e293b}
.msg{margin-bottom:15px;padding:10px;border-radius:10px;font-size:14px}
.success{background:#dcfce7;color:#16a34a}
.error{background:#fee2e2;color:#dc2626}
label{display:block;margin-top:15px;font-size:14px;font-weight:500;color:#334155}
input,select{width:100%;padding:12px;margin-top:6px;border-radius:12px;border:1px solid #cbd5f5;font-size:14px;transition:border .2s}
input:focus, select:focus{border-color:#4f46e5;outline:none;box-shadow:0 0 0 2px rgba(79,70,229,.2)}
button{margin-top:20px;padding:14px 20px;border:none;border-radius:12px;background:#4f46e5;color:#fff;font-weight:500;cursor:pointer;transition:all .2s}
button:hover{background:#4338ca;transform:translateY(-1px)}
.profile-section{display:flex;align-items:center;margin-bottom:20px;gap:20px}
.profile-preview{width:100px;height:100px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;transition:all .2s}
.profile-preview:hover{transform:scale(1.05)}
.file-input{display:none}
.upload-label{padding:8px 12px;background:#e5e7eb;color:#1e293b;border-radius:12px;cursor:pointer;transition:all .2s;display:inline-block;margin-top:5px}
.upload-label:hover{background:#d1d5db}
</style>
</head>
<body>

<?php require_once __DIR__ . '/../sidebar/sidebar.php'; ?>

<div class="main">
    <form method="post" enctype="multipart/form-data" style="display:flex;gap:30px;flex-wrap:wrap;width:100%;justify-content:center">
        <div class="card col">
            <a href="<?= BASE_URL ?>settings/settings.php" class="back">← Back to Settings</a>
            <h2>👤 Edit Profile</h2>

            <?php if ($message): ?>
            <div class="msg <?= strpos($message,'✅') !== false ? 'success':'error' ?>">
                <?= $message ?>
            </div>
            <?php endif; ?>

            <p>Email: <strong><?= htmlspecialchars($email) ?></strong></p>

            <div class="profile-section">
                <img id="profilePreview" src="../uploads/<?= !empty($profile_image)?htmlspecialchars($profile_image):'default.png' ?>" class="profile-preview" alt="Profile Image">
                <div>
                    <label for="profile_image" class="upload-label">Change Profile Picture</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="file-input" onchange="previewImage(event)">
                </div>
            </div>

            <label>Full Name <span style="color:red">*</span></label>
            <input name="full_name" value="<?= htmlspecialchars($full_name ?? '') ?>" required>

            <label>Personal ID (optional)</label>
            <input name="personal_id" value="<?= htmlspecialchars($personal_id ?? '') ?>">

            <label>Position <span style="color:red">*</span></label>
            <input name="position" value="<?= htmlspecialchars($position ?? '') ?>" required>

            <label>Account Site</label>
            <input type="text" value="<?= htmlspecialchars($account_type ?? '') ?>" disabled>
        </div>

        <div class="card col">
            <h2>🔒 Change Password</h2>

            <label>Current Password</label>
            <input type="password" name="current_password">

            <label>New Password</label>
            <input type="password" name="new_password">

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password">

            <button type="submit">Save Changes</button>
        </div>
    </form>
</div>

<script>
function previewImage(event){
    const output = document.getElementById('profilePreview');
    output.src = URL.createObjectURL(event.target.files[0]);
    output.onload = function(){ URL.revokeObjectURL(output.src) }
}
</script>

</body>
</html>
