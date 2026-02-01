<?php

require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'Login/index.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);

$conn = new mysqli("localhost", "root", "", "Mobilecare_monitoring");
if ($conn->connect_error) die('DB Connection failed: ' . $conn->connect_error);

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0a1128;
            --bg-secondary: #1e293b;
            --bg-card: rgba(51, 65, 85, 0.4);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: rgba(148, 163, 184, 0.2);
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --accent-light: #60a5fa;
            --glass-bg: rgba(30, 41, 59, 0.6);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0a1128 0%, #1e3a5f 50%, #0f172a 100%);
            background-attachment: fixed;
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Header */
        .header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-info h1 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .header-info p {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .logout-btn {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: var(--accent);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        /* Main Content */
        .main {
            padding: 40px 0;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 32px;
        }

        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 24px;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card:hover {
            border-color: var(--accent);
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }

        .card:hover .card-icon {
            transform: scale(1.05);
        }

        .card-icon svg {
            width: 22px;
            height: 22px;
            stroke: white;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .card h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
            position: relative;
            z-index: 1;
        }

        .card p {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        /* Floating Menu */
        .floating-menu {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
        }

        .menu-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            color: white;
            border: none;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .menu-icon {
            transition: transform 0.3s ease;
            display: inline-block;
        }

        .menu-btn.active .menu-icon {
            transform: rotate(45deg);
        }

        .menu-btn:hover {
            transform: scale(1.05);
        }

        .chat-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--bg-primary);
        }

        .menu-items {
            position: absolute;
            bottom: 70px;
            right: 0;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 8px;
            min-width: 180px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        }

        .menu-items.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            border-radius: 4px;
            transition: background 0.15s ease;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .menu-item svg {
            width: 16px;
            height: 16px;
            stroke: var(--text-secondary);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .menu-item:hover {
            background: rgba(59, 130, 246, 0.2);
        }

        .menu-item:hover svg {
            stroke: var(--accent-light);
        }

        /* Chat Modal */
        .chat-modal {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 380px;
            height: 520px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            display: none;
            flex-direction: column;
            z-index: 999;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }

        .chat-modal.active {
            display: flex;
        }

        .chat-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-header span {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .minimize-chat,
        .close-chat {
            background: none;
            border: none;
            font-size: 18px;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px 8px;
            line-height: 1;
        }

        .minimize-chat:hover,
        .close-chat:hover {
            color: var(--text-primary);
        }

        .chat-main {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .chat-user-list {
            width: 140px;
            border-right: 1px solid var(--glass-border);
            overflow-y: auto;
            padding: 8px;
        }

        .chat-user-btn {
            width: 100%;
            padding: 10px 8px;
            background: none;
            border: none;
            border-radius: 4px;
            text-align: left;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-primary);
            cursor: pointer;
            margin-bottom: 4px;
            position: relative;
            transition: background 0.15s ease;
        }

        .chat-user-btn:hover {
            background: rgba(59, 130, 246, 0.2);
        }

        .unread-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            background: var(--accent);
            color: white;
            font-size: 9px;
            padding: 2px 5px;
            border-radius: 10px;
        }

        .status-dot {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .status-online {
            background: #22c55e;
        }

        .status-offline {
            background: var(--text-secondary);
        }

        .chat-body {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .message {
            display: flex;
            flex-direction: column;
            max-width: 75%;
        }

        .message.admin {
            align-self: flex-end;
            align-items: flex-end;
        }

        .message.user {
            align-self: flex-start;
            align-items: flex-start;
        }

        .sender-name {
            font-size: 11px;
            color: var(--text-secondary);
            margin-bottom: 4px;
            font-weight: 500;
        }

        .message-text {
            background: rgba(51, 65, 85, 0.5);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.5;
            border: 1px solid var(--glass-border);
        }

        .message.admin .message-text {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            color: white;
            border: none;
        }

        .message-text img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 4px;
            margin-top: 6px;
            display: block;
        }

        .message-text a {
            color: inherit;
            text-decoration: underline;
        }

        .read-check {
            font-size: 10px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .read-check.seen {
            color: var(--accent);
        }

        .chat-footer {
            padding: 16px;
            border-top: 1px solid var(--glass-border);
            display: flex;
            gap: 8px;
        }

        .chat-footer input[type="text"] {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            background: rgba(51, 65, 85, 0.4);
            color: var(--text-primary);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .chat-footer input[type="text"]:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .chat-footer button {
            padding: 10px 16px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-footer button:hover {
            transform: translateY(-2px);
        }

        .chat-footer button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        #attachFile {
            background: rgba(51, 65, 85, 0.5);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
            box-shadow: none;
        }

        #attachFile:hover {
            background: rgba(59, 130, 246, 0.3);
            border-color: var(--accent);
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .chat-modal {
                width: calc(100vw - 48px);
                max-width: 380px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-info">
                    <h1>Welcome 👋</h1>
                    <p><?= htmlspecialchars($email) ?> • Site: <?= htmlspecialchars($site) ?></p>
                </div>
                <form action="<?= BASE_URL ?>Login/logout.php" method="post">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="container">
            <h2 class="page-title">Dashboard</h2>
            <div class="grid">
                <a href="<?= BASE_URL ?>Chubb/chubb.php" class="card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3>Chubb</h3>
                    <p>Manage Chubb claims</p>
                </a>
                <a href="<?= BASE_URL ?>Escalations/escalations.php" class="card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <h3>Escalations</h3>
                    <p>View escalated cases</p>
                </a>
                <a href="<?= BASE_URL ?>Inventory/inventory.php" class="card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <h3>Inventory</h3>
                    <p>Track stock</p>
                </a>
                <?php if($isAdmin): ?>
                <a href="<?= BASE_URL ?>Reports/reports.php" class="card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </div>
                    <h3>Analytics</h3>
                    <p>Reports & trends</p>
                </a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>Endorsement_tally/endorsement_tally.php" class="card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                    </div>
                    <h3>Endorsements</h3>
                    <p>Submit & track</p>
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>settings/settings.php" class="card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6m8.66-15.66l-4.24 4.24m-4.24 4.24l-4.24 4.24M23 12h-6m-6 0H1m20.66 8.66l-4.24-4.24m-4.24-4.24l-4.24-4.24"></path>
                        </svg>
                    </div>
                    <h3>Settings</h3>
                    <p>Account preferences</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Floating Menu -->
    <div class="floating-menu">
        <button class="menu-btn" id="menuBtn">
            <span class="menu-icon">+</span>
            <span class="chat-badge" id="chatBadge" style="display: none;">0</span>
        </button>
        <div class="menu-items" id="menuItems">
            <?php if(!$isAdmin): ?>
            <a href="<?= BASE_URL ?>Endorsement_tally/endorsement_tally.php" class="menu-item">
                <svg viewBox="0 0 24 24">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                </svg>
                📋 Endorsement
            </a>
            <?php endif; ?>
            <?php if($isAdmin): ?>
            <a href="<?= BASE_URL ?>Reports/reports.php" class="menu-item">
                <svg viewBox="0 0 24 24">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                📊 Analytics
            </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>Inventory/inventory.php" class="menu-item">
                <svg viewBox="0 0 24 24">
                    <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
                📦 Inventory
            </a>
            <a href="<?= BASE_URL ?>about/about_us.php" class="menu-item">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                ℹ️ About us!
            </a>
            <button class="menu-item" id="chatBtn">
                <svg viewBox="0 0 24 24">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                💬 Chat
            </button>
        </div>
    </div>

    <!-- Chat Modal -->
    <div id="chatModal" class="chat-modal">
        <div class="chat-header">
            <div class="chat-header-left">
                <button id="minimizeChat" class="minimize-chat">−</button>
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
        const chatBadge = document.getElementById('chatBadge');

        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            menuBtn.classList.toggle('active');
            menuItems.classList.toggle('show');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menuBtn.contains(e.target) && !menuItems.contains(e.target)) {
                menuBtn.classList.remove('active');
                menuItems.classList.remove('show');
            }
        });

        const userId = <?= $user_id ?>;
        let selectedUserId = <?= $isAdmin ? 'null' : ($admin_id ?? 'null') ?>;
        let isMinimized = false;

        chatBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            chatModal.classList.add('active');
            menuBtn.classList.remove('active');
            menuItems.classList.remove('show');
            isMinimized = false;
            chatBody.style.display = 'flex';
            chatFooter.style.display = 'flex';
            minimizeChat.textContent = '−';
        });

        closeChat.addEventListener('click', () => {
            chatModal.classList.remove('active');
        });

        minimizeChat.addEventListener('click', () => {
            isMinimized = !isMinimized;
            chatBody.style.display = isMinimized ? 'none' : 'flex';
            chatFooter.style.display = isMinimized ? 'none' : 'flex';
            minimizeChat.textContent = isMinimized ? '+' : '−';
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
                let totalUnread = 0;
                
                users.forEach(u=>{
                    const btn = document.createElement('button');
                    btn.className='chat-user-btn';
                    btn.textContent = u.full_name;

                    if(u.unread_count > 0){
                        const redDot = document.createElement('span');
                        redDot.className='unread-dot';
                        redDot.textContent=u.unread_count;
                        btn.appendChild(redDot);
                        totalUnread += parseInt(u.unread_count);
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
                        minimizeChat.textContent='−';
                        loadMessages();
                    };

                    list.appendChild(btn);
                });

                // Update badge
                if(totalUnread > 0) {
                    chatBadge.textContent = totalUnread;
                    chatBadge.style.display = 'flex';
                } else {
                    chatBadge.style.display = 'none';
                }
            });
        }
        loadUsers();
        setInterval(loadUsers, 5000);
        <?php else: ?>
        // For non-admin users, check for unread messages
        function checkUnreadMessages() {
            fetch('messages/fetch_messages.php?user_id=' + selectedUserId)
                .then(res => res.json())
                .then(data => {
                    const unreadCount = data.filter(msg => msg.sender_id != userId && msg.read_status == 0).length;
                    if(unreadCount > 0) {
                        chatBadge.textContent = unreadCount;
                        chatBadge.style.display = 'flex';
                    } else {
                        chatBadge.style.display = 'none';
                    }
                });
        }
        if(selectedUserId) {
            checkUnreadMessages();
            setInterval(checkUnreadMessages, 5000);
        }
        <?php endif; ?>

        setInterval(loadMessages, 2000);
    </script>
</body>
</html>