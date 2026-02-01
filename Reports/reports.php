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
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

// ===============================
// COUNT DATA FOR DASHBOARD CARDS
// ===============================
$counts = [
    'chubb' => 0,
    'inventory' => 0,
    'escalations' => 0,
    'users' => 0,
    'endorsements' => 0
];

$countQueries = [
    'chubb' => "SELECT COUNT(*) as total FROM chubb_claims",
    'inventory' => "SELECT COUNT(*) as total FROM inventory",
    'escalations' => "SELECT COUNT(*) as total FROM escalations",
    'users' => "SELECT COUNT(*) as total FROM users",
    'endorsements' => "SELECT COUNT(*) as total FROM endorsements"
];

foreach($countQueries as $key => $query){
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $counts[$key] = $row['total'] ?? 0;
}

// ===============================
// DAILY ENDORSEMENTS (LAST 30 DAYS)
// ===============================
$labels = [];
$dailyTotals = [];
for ($i = 15; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M d', strtotime($date));

    // Correcting the query to fetch SUM of endorsements per day
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM endorsements WHERE DATE(created_at) = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $dailyTotals[] = (int)($row['total'] ?? 0);
    $stmt->close();
}

// ===============================
// ENDORSEMENTS PER ENGINEER
// ===============================
$engineerNames = [];
$engineerTotals = [];
$stmt = $conn->prepare("SELECT engineer_name, SUM(quantity) as total FROM endorsements GROUP BY engineer_name ORDER BY total DESC");
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()){
    $engineerNames[] = $row['engineer_name'];
    $engineerTotals[] = (int)$row['total'];
}
$stmt->close();

// ===============================
// INVENTORY TYPES PIE
// ===============================
$types = ['adhesive','fixed asset','consumables','others'];
$typeCounts = [];
foreach($types as $type){
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM inventory WHERE type=?");
    $stmt->bind_param("s",$type);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $typeCounts[] = (int)($row['total'] ?? 0);
    $stmt->close();
}

/* ===============================
   USERS PIE (by account_type)
================================ */
$userSites = [];
$userCounts = [];

$stmt = $conn->prepare("SELECT account_type, COUNT(*) as total FROM users GROUP BY account_type");
if (!$stmt) {
    die("Prepare failed: ".$conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()){
    $userSites[] = $row['account_type'] ?: 'Unknown';
    $userCounts[] = (int)$row['total'];
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Analytics | MobileCare</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{box-sizing:border-box;font-family:Poppins;margin:0;padding:0}
body{background:#f0f4f8;color:#1e293b}
.main{margin-left:260px;padding:30px}
@media(max-width:768px){.main{margin-left:0;padding:15px}}

h2{margin-bottom:20px;font-weight:600}

/* DASHBOARD CARDS */
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px}
.card{background:#fff;padding:25px;border-radius:16px;box-shadow:0 15px 40px rgba(0,0,0,.08);transition:.2s;cursor:pointer}
.card:hover{transform:translateY(-5px);box-shadow:0 25px 60px rgba(0,0,0,.15)}
.card h3{font-weight:600;margin-bottom:10px;font-size:16px;color:#334155}
.card .count{font-size:28px;font-weight:700;color:#4f46e5}

/* CHART CARDS */
.chart-card{background:#fff;padding:25px;border-radius:16px;box-shadow:0 15px 40px rgba(0,0,0,.08);margin-bottom:30px}
.chart-card h3{font-weight:600;margin-bottom:10px;font-size:16px;color:#334155}
.chart-card .analysis{font-size:13px;color:#64748b;margin-top:8px}
.chart-container{position:relative;height:300px}
canvas{max-height:300px}

.download-btn{
    padding:12px 25px;
    border-radius:12px;
    background:#4f46e5;
    color:#fff;
    font-weight:600;
    text-decoration:none;
    margin-bottom:20px;
    display:inline-block;
}
.download-btn:hover{background:#4338ca}
</style>
</head>
<body>

<?php require_once __DIR__.'/../sidebar/sidebar.php'; ?>

<div class="main">

<h2>📊 Analytics Dashboard</h2>

<a href="analytics_download.php" class="download-btn">📥 Download Report (CSV)</a>

<!-- DASHBOARD CARDS -->
<div class="grid">
    <div class="card">
        <h3>Chubb Claims</h3>
        <div class="count"><?= $counts['chubb'] ?></div>
    </div>
    <div class="card">
        <h3>Inventory Items</h3>
        <div class="count"><?= $counts['inventory'] ?></div>
    </div>
    <div class="card">
        <h3>Escalations</h3>
        <div class="count"><?= $counts['escalations'] ?></div>
    </div>
    <div class="card">
        <h3>Users</h3>
        <div class="count"><?= $counts['users'] ?></div>
    </div>
    <div class="card">
        <h3>Endorsements</h3>
        <div class="count"><?= $counts['endorsements'] ?></div>
    </div>
</div>

<!-- CHARTS -->
<div class="chart-card">
    <h3>Endorsements (Last 15 Days)</h3>
    <div class="chart-container">
        <canvas id="dailyChart"></canvas>
    </div>
    <div class="analysis">📈 Endorsements trend shows activity over the last 15 days. Peaks indicate days with high endorsement submissions.</div>
</div>

<div class="chart-card">
    <h3>Endorsements per Engineer</h3>
    <div class="chart-container">
        <canvas id="engineerChart"></canvas>
    </div>
    <div class="analysis">👷‍♂️ Engineers with more endorsements indicate higher workload or activity.</div>
</div>

<div class="chart-card">
    <h3>Inventory by Type</h3>
    <div class="chart-container">
        <canvas id="typeChart"></canvas>
    </div>
    <div class="analysis">📦 Consumables or fixed assets dominance shows the type of stock majority held.</div>
</div>

<div class="chart-card">
    <h3>Users by Site</h3>
    <div class="chart-container">
        <canvas id="usersChart"></canvas>
    </div>
    <div class="analysis">🏢 Pie chart shows the user distribution by site, helping to identify larger or smaller teams.</div>
</div>

<script>
// Daily Endorsements Line Chart with Hover Effects
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
const dailyGradient = dailyCtx.createLinearGradient(0, 0, 0, 300);
dailyGradient.addColorStop(0, 'rgba(79,70,229,0.5)');
dailyGradient.addColorStop(1, 'rgba(79,70,229,0.05)');
new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Endorsements',
            data: <?= json_encode($dailyTotals) ?>,
            borderColor: '#4f46e5',
            backgroundColor: dailyGradient,
            tension: 0.4,
            fill: true,
            pointRadius: 5,
            pointHoverRadius: 8,
            pointBackgroundColor: '#4f46e5'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                enabled: true,
                callbacks: {
                    label: function(tooltipItem) {
                        return `Endorsements: ${tooltipItem.raw}`;
                    }
                }
            }
        },
        hover: {
            mode: 'nearest',
            intersect: false,
            animationDuration: 300
        },
        interaction: {
            mode: 'nearest',
            intersect: false
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                ticks: {
                    color: '#334155'
                },
                grid: {
                    display: false
                }
            }
        }
    }
});

// Endorsements per Engineer Bar Chart with Hover Effects
const engCtx = document.getElementById('engineerChart').getContext('2d');
new Chart(engCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($engineerNames) ?>,
        datasets: [{
            label: 'Total Endorsements',
            data: <?= json_encode($engineerTotals) ?>,
            backgroundColor: 'rgba(34,197,94,0.8)',
            borderRadius: 12
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                enabled: true,
                callbacks: {
                    label: function(tooltipItem) {
                        return `${tooltipItem.label}: ${tooltipItem.raw} endorsements`;
                    }
                }
            }
        },
        hover: {
            mode: 'index',
            intersect: false,
            animationDuration: 300
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Inventory Type Pie Chart with Hover Effects
const typeCtx = document.getElementById('typeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_map('ucfirst', $types)) ?>,
        datasets: [{
            label: 'Inventory',
            data: <?= json_encode($typeCounts) ?>,
            backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#ef4444'],
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                enabled: true
            }
        },
        hover: {
            mode: 'nearest',
            intersect: true,
            animationDuration: 400
        }
    }
});

// Users by Site Pie Chart with Hover Effects
const usersCtx = document.getElementById('usersChart').getContext('2d');
new Chart(usersCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($userSites) ?>,
        datasets: [{
            label: 'Users',
            data: <?= json_encode($userCounts) ?>,
            backgroundColor: ['#6366f1', '#14b8a6', '#facc15', '#f87171', '#a78bfa', '#34d399'],
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                enabled: true,
                callbacks: {
                    label: function(tooltipItem) {
                        return `${tooltipItem.label}: ${tooltipItem.raw} users`;
                    }
                }
            }
        },
        hover: {
            mode: 'nearest',
            intersect: true,
            animationDuration: 400
        }
    }
});
</script>

</div>
</body>
</html>
