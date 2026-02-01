<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

/* ===============================
   AUTH CHECK
================================ */
if(!isset($_SESSION['user_id'])){
    header("Location: /Mobilecare_monitoring/Login/index.php");
    exit;
}

/* ===============================
   DB CONNECTION
================================ */
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

/* ===============================
   GET CURRENT USER'S ACCOUNT TYPE
================================ */
$current_user_id = $_SESSION['user_id'];
$current_account_type = '';

$stmt = $conn->prepare("SELECT account_type FROM users WHERE id = ?");
$stmt->bind_param("i",$current_user_id);
$stmt->execute();
$result = $stmt->get_result();
if($row = $result->fetch_assoc()){
    $current_account_type = $row['account_type'] ?? '';
}
$stmt->close();

/* ===============================
   FETCH ENGINEERS FOR CURRENT ACCOUNT TYPE
================================ */
$participants = [];
if(!empty($current_account_type)){
    $stmt = $conn->prepare("
        SELECT full_name
        FROM users
        WHERE account_type = ? AND position = 'Engineer'
        ORDER BY full_name ASC
    ");
    $stmt->bind_param("s",$current_account_type);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        if(!empty($row['full_name'])) $participants[] = $row['full_name'];
    }
    $stmt->close();
}

/* ===============================
   DEVICE TYPES
================================ */
$types = ['iphone','macbook','ios','imac'];

/* ===============================
   GET SELECTED DATE (default today)
================================ */
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

/* ===============================
   FETCH ENDORSEMENT TALLY FOR CURRENT ACCOUNT TYPE
================================ */
$tally = [];
if(!empty($current_account_type)){
    $stmt = $conn->prepare("
        SELECT engineer_name, type, SUM(quantity) AS total
        FROM endorsements
        WHERE account_type = ? AND DATE(created_at) = ?
        GROUP BY engineer_name, type
    ");
    $stmt->bind_param("ss",$current_account_type,$selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $eng = trim($row['engineer_name']);
        $type = strtolower(trim($row['type']));
        $count = (int)$row['total'];
        if(!isset($tally[$eng])) $tally[$eng] = [];
        $tally[$eng][$type] = $count;
    }
    $stmt->close();
}

// Fill missing device types
foreach($tally as $eng => $data){
    foreach($types as $type){
        if(!isset($tally[$eng][$type])) $tally[$eng][$type] = 0;
    }
}

/* ===============================
   FETCH RECENT ENDORSEMENTS
================================ */
$recent_endorsements = [];
if(!empty($current_account_type)){
    $stmt = $conn->prepare("
        SELECT engineer_name, type, quantity, created_at
        FROM endorsements
        WHERE account_type = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->bind_param("s",$current_account_type);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $recent_endorsements[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Endorsement Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<style>
/* Scrollable recent endorsements */
.recent-endorsements {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 15px;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background-color: #f9fafb;
}
.recent-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 8px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}
.recent-item:last-child { border-bottom: none; }
.recent-item span { font-weight: 500; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>Endorsement Dashboard</h1>
            <p class="header-subtitle">Manage device endorsements with round-robin distribution</p>
        </div>
        <a href="/Mobilecare_monitoring/dashboard.php" class="back-btn">&larr; Back to Dashboard</a>
    </div>

    <div class="date-filter-bar">
        <div class="date-filter-group">
            <span class="date-label">View Date:</span>
            <input type="date" id="dateFilter" class="date-input" value="<?= htmlspecialchars($selected_date) ?>">
            <button class="btn-apply" id="btnApplyDate">Apply</button>
        </div>
        <div class="account-badge">
            Account Type: <?= htmlspecialchars($current_account_type ?: 'N/A') ?>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Queue Panel -->
        <div class="queue-panel">
            <div class="panel-header">
                <div class="panel-title">Queue Management</div>
                <p class="panel-description">Next engineer in rotation</p>
            </div>

            <div class="next-engineer-card" id="nextEngineerCard">
                <div class="next-label">Next in Queue</div>
                <div class="next-engineer-name" id="nextEngineerName">—</div>
                <div class="next-position" id="nextPosition">Select engineers to start</div>
            </div>

            <div class="queue-display">
                <div class="queue-label">Queue Order</div>
                <div class="queue-list" id="queueList">
                    <span class="empty-queue">No engineers selected</span>
                </div>
            </div>

            <div class="engineer-selector-section">
                <div class="selector-title">Available Engineers</div>
                <div class="engineer-selector" id="engineerSelector">
                    <?php if(empty($participants)): ?>
                        <p style="color:#94a3b8;font-size:14px;">No engineers found for your site</p>
                    <?php else: ?>
                        <?php foreach($participants as $eng): ?>
                            <div class="engineer-chip" data-name="<?= htmlspecialchars($eng) ?>">
                                <?= htmlspecialchars($eng) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Endorsement Form -->
        <div class="queue-section">
            <div class="panel-header">
                <div class="panel-title">New Endorsement</div>
                <p class="panel-description">Assign device to next engineer in queue</p>
            </div>

            <div class="endorsement-form">
                <div class="form-group">
                    <label>Device Type</label>
                    <select class="form-select" id="deviceType">
                        <?php foreach($types as $type): ?>
                            <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full-width">
                    <button class="btn-endorse" id="btnEndorse">Endorse to Next Engineer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-section">
        <div class="stats-header">
            <h2>Engineer Statistics (<?= date('M d, Y', strtotime($selected_date)) ?>)</h2>
        </div>
        <div class="stats-grid" id="statsGrid"></div>
    </div>

    <!-- Recent Endorsements -->
    <div class="recent-endorsements" id="recentEndorsements">
        <?php foreach($recent_endorsements as $e): ?>
            <div class="recent-item">
                <span><?= htmlspecialchars($e['engineer_name']) ?></span>
                <span><?= htmlspecialchars($e['type']) ?></span>
                <span><?= (int)$e['quantity'] ?></span>
                <span><?= date('H:i', strtotime($e['created_at'])) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
let queue=[];
let currentIndex=0;
let tally=<?= json_encode($tally) ?>;
const types=<?= json_encode($types) ?>;
const selectedDate='<?= $selected_date ?>';
const currentAccount='<?= $current_account_type ?>';

const engineerSelector=document.getElementById('engineerSelector');
const queueList=document.getElementById('queueList');
const nextEngineerName=document.getElementById('nextEngineerName');
const nextPosition=document.getElementById('nextPosition');
const statsGrid=document.getElementById('statsGrid');
const btnEndorse=document.getElementById('btnEndorse');
const recentEndorsements=document.getElementById('recentEndorsements');
const dateFilter=document.getElementById('dateFilter');
const btnApplyDate=document.getElementById('btnApplyDate');

btnApplyDate.addEventListener('click',()=>{
    const date=dateFilter.value;
    if(date) window.location.href=`?date=${date}`;
});

function saveQueueState(){
    localStorage.setItem(`endorsement_queue_${currentAccount}_${selectedDate}`,JSON.stringify({queue,currentIndex}));
}

function loadQueueState(){
    const saved=localStorage.getItem(`endorsement_queue_${currentAccount}_${selectedDate}`);
    if(saved){
        const data=JSON.parse(saved);
        queue=data.queue||[];
        currentIndex=data.currentIndex||0;
        engineerSelector.querySelectorAll('.engineer-chip').forEach(chip=>{
            if(queue.includes(chip.dataset.name)) chip.classList.add('active');
        });
        renderQueue();
    }
}

function renderQueue(){
    if(queue.length===0){
        queueList.innerHTML='<span class="empty-queue">No engineers selected</span>';
        nextEngineerName.textContent='—';
        nextPosition.textContent='Select engineers to start';
        btnEndorse.disabled=true;
        renderStats();
        return;
    }
    btnEndorse.disabled=false;
    nextEngineerName.textContent=queue[currentIndex];
    nextPosition.textContent=`Position ${currentIndex+1} of ${queue.length}`;
    let html='';
    queue.forEach((name,index)=>{
        html+=`<div class="queue-item ${index===currentIndex?'next':''}">
                    <div class="queue-position">${index+1}</div>
                    <div class="queue-item-name">${name}</div>
               </div>`;
    });
    queueList.innerHTML=html;
    renderStats();
}

function renderStats(){
    statsGrid.innerHTML='';
    if(queue.length===0){
        statsGrid.innerHTML='<p style="color:#94a3b8;font-size:14px;grid-column:1/-1;text-align:center;padding:40px;">Select engineers to view statistics</p>';
        return;
    }
    queue.forEach((eng,index)=>{
        const data=tally[eng]||{};
        const total=Object.values(data).reduce((a,b)=>a+b,0);
        const card=document.createElement('div');
        card.className='stat-card';
        card.innerHTML=`
            <div class="stat-card-header">
                <div class="stat-name-section">
                    <div class="stat-card-name">${eng}</div>
                    ${index===currentIndex?'<span class="stat-badge">Next in Queue</span>':''}
                </div>
                <div class="stat-card-total">${total}</div>
            </div>
            <div class="stat-types">
                ${types.map(t=>`<div class="stat-type-row">
                    <span class="stat-type-label">${t}</span>
                    <span class="stat-type-value">${data[t]||0}</span>
                </div>`).join('')}
            </div>
        `;
        statsGrid.appendChild(card);
    });
}

engineerSelector.querySelectorAll('.engineer-chip').forEach(chip=>{
    chip.addEventListener('click',()=>{
        const name=chip.dataset.name;
        if(queue.includes(name)){
            queue=queue.filter(e=>e!==name);
            chip.classList.remove('active');
            if(currentIndex>=queue.length) currentIndex=0;
        }else{
            queue.push(name);
            chip.classList.add('active');
        }
        saveQueueState();
        renderQueue();
    });
});

btnEndorse.addEventListener('click',()=>{
    if(queue.length===0) return alert('Select at least one engineer');
    const eng=queue[currentIndex];
    const type=document.getElementById('deviceType').value;

    fetch('endorsement_add.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({engineer_name:eng,type:type,quantity:1,account_type:currentAccount})
    }).then(res=>res.json()).then(data=>{
        if(data.success){
            if(!tally[eng]) tally[eng]={};
            tally[eng][type]=(tally[eng][type]||0)+1;

            currentIndex=(currentIndex+1)%queue.length;
            saveQueueState();
            renderQueue();

            // Update recent endorsements safely
            const item=document.createElement('div');
            item.className='recent-item';
            const now=new Date();
            item.innerHTML=`<span>${eng}</span><span>${type}</span><span>1</span><span>${now.getHours()}:${now.getMinutes().toString().padStart(2,'0')}</span>`;
            const recentList=recentEndorsements;
            if(recentList) recentList.prepend(item);
        }else alert('Failed: '+data.message);
    }).catch(err=>alert('Error: '+err));
});

loadQueueState();
renderQueue();
</script>
</body>
</html>
