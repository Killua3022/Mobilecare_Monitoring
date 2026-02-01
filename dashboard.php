<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'Login/index.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);


// Update last activity
$stmt = $conn->prepare("UPDATE users SET last_activity=NOW() WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Get user info
$stmt = $conn->prepare("SELECT email, account_type, role, full_name, profile_image FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $site, $role, $full_name, $profile_image);
$stmt->fetch();
$stmt->close();

$isAdmin = ($role === 'admin');
$admin_id = null;
$admin_full_name = null;

if (!$isAdmin) {
    // Get admin id and name for non-admin users
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE role='admin' LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($admin_id, $admin_full_name);
    $stmt->fetch();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MobileCare | Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<link rel="stylesheet" href="css/style.css">
<style>
/* Quick inline styles for chat images */
.chat-body .message-text img {
    border-radius: 5px;
    margin-top: 5px;
}
</style>
</head>
<body>

<div class="header">
    <div>
        <h1>Welcome!</h1>
        <p><?= htmlspecialchars($email) ?> • Site: <?= htmlspecialchars($site) ?></p>
    </div>
    <form action="<?= BASE_URL ?>Login/logout.php" method="post">
        <button class="logout">Logout</button>
    </form>
</div>

<!-- Dashboard Main Grid -->
<div class="main">
    <div class="grid">
        <a href="<?= BASE_URL ?>Chubb/chubb.php" class="card">
            <div class="icon chubb"><i class='bx bx-shield'></i></div>
            <h3>Chubb</h3>
            <p>Manage Chubb claims</p>
        </a>

        <a href="<?= BASE_URL ?>Escalations/escalations.php" class="card">
            <div class="icon escalation"><i class='bx bx-bell'></i></div>
            <h3>Escalations</h3>
            <p>View escalated cases</p>
        </a>

        <a href="<?= BASE_URL ?>Inventory/inventory.php" class="card">
            <div class="icon inventory"><i class='bx bx-box'></i></div>
            <h3>Inventory</h3>
            <p>Track stock</p>
        </a>

        <?php if($isAdmin): ?>
            <a href="<?= BASE_URL ?>Reports/reports.php" class="card">
                <div class="icon analytics"><i class='bx bx-bar-chart-alt-2'></i></div>
                <h3>Analytics</h3>
                <p>Reports & trends</p>
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>Endorsement_tally/endorsement_tally.php" class="card">
                <div class="icon tally"><i class='bx bx-list-check'></i></div>
                <h3>Endorsements</h3>
                <p>Submit & track</p>
            </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>settings/settings.php" class="card">
            <div class="icon settings"><i class='bx bx-cog'></i></div>
            <h3>Settings</h3>
            <p>Account preferences</p>
        </a>
    </div>
</div>


<!-- Floating Menu -->
<div class="floating-menu">
    <button class="menu-btn" id="menuBtn"><i class='bx bx-plus'></i></button>
    <div class="menu-items" id="menuItems">
        <a href="<?= BASE_URL ?>about/about_us.php" class="menu-item" title="About Us"><i class='bx bx-info-circle'></i></a>
        <button class="menu-item" id="chatBtn" title="Chat"><i class='bx bx-chat'></i></button>
    </div>
</div>

<!-- CHAT MODAL -->
<div id="chatModal" class="chat-modal">
    <div class="chat-header">
        <div>
            <button id="minimizeChat" class="minimize-chat">—</button>
            <span>💬 Chat Support - <span id="chatUserName">
                <?= $isAdmin ? 'No user selected' : htmlspecialchars($admin_full_name) ?>
            </span></span>
        </div>
        <button id="closeChat" class="close-chat">&times;</button>
    </div>

    <div class="chat-main">
        <?php if($isAdmin): ?>
        <div class="chat-user-list" id="chatUserList"></div>
        <?php endif; ?>
        <div class="chat-body" id="chatBody"></div>
    </div>
    <div class="chat-footer" id="chatFooter">
        <input type="text" id="chatInput" placeholder="Type a message..." />
        <input type="file" id="chatFile" style="display:none;" />
        <button id="attachFile">+</button>
        <button id="sendChat">Send</button>
    </div>
</div>

<script>
const chatBtn = document.getElementById('chatBtn');
const chatModal = document.getElementById('chatModal');
const closeChat = document.getElementById('closeChat');
const minimizeChat = document.getElementById('minimizeChat');
const chatBody = document.getElementById('chatBody');
const chatFooter = document.getElementById('chatFooter');
const chatInput = document.getElementById('chatInput');
const sendChat = document.getElementById('sendChat');
const chatFile = document.getElementById('chatFile');
const attachFile = document.getElementById('attachFile');
const menuBtn = document.getElementById('menuBtn');
const menuItems = document.getElementById('menuItems');


menuBtn.addEventListener('click', () => {
    menuBtn.classList.toggle('active');
    menuItems.classList.toggle('show');
});

const userId = <?= $user_id ?>;
let selectedUserId = <?= $isAdmin ? 'null' : ($admin_id ?? 'null') ?>;
let isMinimized = false;

chatBtn.addEventListener('click', ()=>chatModal.classList.add('active'));
closeChat.addEventListener('click', ()=>chatModal.classList.remove('active'));
minimizeChat.addEventListener('click', ()=>{
    isMinimized = !isMinimized;
    chatBody.style.display = isMinimized ? 'none' : 'flex';
    chatFooter.style.display = isMinimized ? 'none' : 'flex';
    minimizeChat.textContent = isMinimized ? '+' : '—';
});

// Handle attach file
attachFile.addEventListener('click', () => chatFile.click());

chatFile.addEventListener('change', () => {
    if (!chatFile.files.length) return;
    const file = chatFile.files[0];

    const formData = new FormData();
    formData.append('receiver_id', selectedUserId);
    formData.append('file', file);

    chatInput.disabled = true;
    sendChat.disabled = true;
    attachFile.disabled = true;

    fetch('messages/send_message.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
      .then(data => {
          if(data.status === 'success') {
              chatInput.value = '';
              loadMessages();
          }
          chatInput.disabled = false;
          sendChat.disabled = false;
          attachFile.disabled = false;
          chatFile.value = '';
      });
});

function sendMessage(){
    if(selectedUserId === null) return alert("No user to chat with!");
    const msg = chatInput.value.trim();
    if(!msg) return;
    chatInput.disabled = true;
    sendChat.disabled = true;

    fetch('messages/send_message.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'message='+encodeURIComponent(msg)+'&receiver_id='+selectedUserId
    }).then(res=>res.json())
      .then(data=>{
          if(data.status==='success'){
              chatInput.value='';
              loadMessages();
          }
          chatInput.disabled = false;
          sendChat.disabled = false;
      });
}

sendChat.addEventListener('click', sendMessage);
chatInput.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); sendMessage(); }});
function loadMessages() {
    if (selectedUserId === null) return;

    // Check if user is near the bottom
    const isNearBottom = chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight < 50;

    fetch('messages/fetch_messages.php?user_id=' + selectedUserId)
        .then(res => res.json())
        .then(data => {
            // Save current scroll position from bottom
            const scrollFromBottom = chatBody.scrollHeight - chatBody.scrollTop;

            chatBody.innerHTML = '';
            data.forEach(msg => {
                const div = document.createElement('div');
                div.classList.add('message', msg.sender_id == userId ? 'admin' : 'user');

                const nameSpan = document.createElement('div');
                nameSpan.className = 'sender-name';
                nameSpan.textContent = msg.sender_id == userId ? 'You' : msg.sender_name;
                div.appendChild(nameSpan);

                const msgText = document.createElement('div');
                msgText.className = 'message-text';
                if(msg.message) msgText.textContent = msg.message;
                if(msg.file_name && msg.file_path){
                    const link = document.createElement('a');
                    link.href = msg.file_path;
                    link.target = '_blank';
                    if(msg.file_name.match(/\.(jpeg|jpg|png|gif)$/i)){
                        const img = document.createElement('img');
                        img.src = msg.file_path;
                        img.style.maxWidth='150px';
                        img.style.maxHeight='150px';
                        link.appendChild(img);
                    } else link.textContent = msg.file_name;
                    msgText.appendChild(link);
                }
                div.appendChild(msgText);

                if(msg.sender_id == userId){
                    const check = document.createElement('span');
                    check.className = 'read-check ' + (msg.read_status == 1 ? 'seen':'sent');
                    check.textContent = msg.read_status == 1 ? 'seen':'sent';
                    div.appendChild(check);
                }

                chatBody.appendChild(div);
            });

            // Auto-scroll if user was near the bottom
            if(isNearBottom){
                chatBody.scrollTop = chatBody.scrollHeight;
            } else {
                // If user scrolled up (back-reading), maintain their position
                chatBody.scrollTop = chatBody.scrollHeight - scrollFromBottom;
            }

            // Mark messages as read
            fetch('messages/mark_read.php',{
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'sender_id='+selectedUserId+'&receiver_id='+userId
            });
        });
}


<?php if($isAdmin): ?>
function loadUsers(){
    fetch('messages/fetch_users.php')
    .then(res=>res.json())
    .then(users=>{
        const list = document.getElementById('chatUserList');
        list.innerHTML='';
        users.forEach(u=>{
            const btn = document.createElement('button');
            btn.className='chat-user-btn';
            btn.textContent = u.full_name;

            if(u.unread_count > 0){
                const redDot = document.createElement('span');
                redDot.className='unread-dot';
                redDot.textContent=u.unread_count;
                btn.appendChild(redDot);
            }

            const status = document.createElement('span');
            status.className = 'status-dot ' + ((Date.now()/1000 - new Date(u.last_activity*1000).getTime()/1000 < 120) ? 'status-online':'status-offline');
            btn.appendChild(status);

            btn.onclick = ()=> {
                selectedUserId = u.id;
                document.getElementById('chatUserName').textContent = u.full_name;
                chatModal.classList.add('active');
                isMinimized=false;
                chatBody.style.display='flex';
                chatFooter.style.display='flex';
                minimizeChat.textContent='—';
                loadMessages();
            };

            list.appendChild(btn);
        });
    });
}
loadUsers();
<?php endif; ?>

setInterval(loadMessages, 2000);
<?php if($isAdmin): ?>
setInterval(loadUsers, 5000);
<?php endif; ?>
</script>

</body>
</html>

