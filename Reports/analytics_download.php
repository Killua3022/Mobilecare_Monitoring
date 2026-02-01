<?php
session_start();

// ===============================
// AUTH CHECK
// ===============================
if (!isset($_SESSION['user_id'])) {
    header("Location: /Mobilecare_monitoring/Login/index.php");
    exit;
}

// ===============================
// DB CONNECTION
// ===============================
$conn = new mysqli("localhost", "root", "", "Mobilecare_monitoring");
if ($conn->connect_error) die("DB Connection failed: ".$conn->connect_error);

// ===============================
// CSV HEADERS
// ===============================
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="mobilecare_report_'.date('Y-m-d').'.csv"');
$output = fopen('php://output', 'w');

// ===============================
// DASHBOARD COUNTS
// ===============================
fputcsv($output, ['Dashboard Counts']);
$countsTables = ['chubb_claims'=>'Chubb Claims','inventory'=>'Inventory Items','escalations'=>'Escalations','users'=>'Users','endorsements'=>'Endorsements'];

foreach($countsTables as $table=>$label){
    $res = $conn->query("SELECT COUNT(*) as total FROM $table");
    $row = $res->fetch_assoc();
    fputcsv($output, [$label, $row['total'] ?? 0]);
}

fputcsv($output, []); // blank line

// ===============================
// DAILY ENDORSEMENTS (Last 15 Days)
// ===============================
fputcsv($output, ['Daily Endorsements (Last 15 Days)']);
fputcsv($output, ['Date','Total Endorsements']);
for($i=15;$i>=0;$i--){
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM endorsements WHERE DATE(created_at)=?");
    $stmt->bind_param("s",$date);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    fputcsv($output, [$date, $row['total'] ?? 0]);
    $stmt->close();
}
fputcsv($output, []);

// ===============================
// ENDORSEMENTS PER ENGINEER
// ===============================
// Endorsements per Engineer with Account Type
fputcsv($output, ['Endorsements per Engineer with Account Type']);
fputcsv($output, ['Engineer Name', 'Account Type', 'Total Endorsements']);

$res = $conn->query("
    SELECT engineer_name, account_type, SUM(quantity) as total 
    FROM endorsements 
    GROUP BY engineer_name, account_type 
    ORDER BY total DESC
");

while ($row = $res->fetch_assoc()) {
    fputcsv($output, [
        $row['engineer_name'], 
        $row['account_type'], 
        $row['total']
    ]);
}

fputcsv($output, []);

// ===============================
// INVENTORY BY TYPE
// ===============================
fputcsv($output, ['Inventory by Type']);
fputcsv($output, ['Type','Count']);
$types = ['adhesive','fixed asset','consumables','others'];
foreach($types as $type){
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM inventory WHERE type=?");
    $stmt->bind_param("s",$type);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    fputcsv($output,[ucfirst($type),$row['total'] ?? 0]);
    $stmt->close();
}
fputcsv($output, []);

// ===============================
// USERS BY ACCOUNT TYPE
// ===============================
fputcsv($output, ['Users by Account Type']);
fputcsv($output, ['Account Type','Count']);
$res = $conn->query("SELECT account_type, COUNT(*) as total FROM users GROUP BY account_type");
while($row = $res->fetch_assoc()){
    fputcsv($output, [$row['account_type'] ?: 'Unknown', $row['total']]);
}

fclose($output);
exit;
?>
