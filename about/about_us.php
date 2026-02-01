<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>About Us | MobileCare</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box;font-family:Poppins}
body{
    margin:0;
    background:#f1f5f9;
    color:#0f172a;
    position:relative;
}

/* BACK BUTTON */
.back-btn {
    position: fixed;
    top: 25px;
    right: 25px;
    background:#4f46e5;
    color:#fff;
    border:none;
    padding:12px 18px;
    border-radius:999px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    box-shadow:0 8px 20px rgba(0,0,0,.2);
    transition:all 0.25s ease;
    z-index:1000;
    display:flex;
    align-items:center;
    gap:6px;
    text-decoration:none;
}
.back-btn:hover{
    transform:translateY(-2px) scale(1.05);
    box-shadow:0 12px 25px rgba(0,0,0,.25);
}

/* CONTAINER */
.container{
    max-width:1100px;
    margin:60px auto;
    padding:0 20px;
}

/* HEADER */
.header{
    text-align:center;
    margin-bottom:60px;
}
.header h1{
    font-size:36px;
    margin-bottom:12px;
}
.header p{
    font-size:16px;
    color:#64748b;
    max-width:650px;
    margin:auto;
}

/* TEAM GRID */
.team{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:30px;
}

/* MEMBER CARD */
.member{
    background:#fff;
    border-radius:24px;
    padding:35px;
    text-align:center;
    box-shadow:0 25px 60px rgba(0,0,0,.12);
    transition:.3s;
}
.member:hover{
    transform:translateY(-8px);
    box-shadow:0 35px 80px rgba(0,0,0,.18);
}
.member img{
    width:140px;
    height:140px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #4f46e5;
    margin-bottom:20px;
}
.member h3{
    margin:10px 0 4px;
    font-size:20px;
}
.member .role{
    color:#4f46e5;
    font-weight:600;
    font-size:14px;
    margin-bottom:15px;
}
.member .desc{
    font-size:15px;
    color:#475569;
    line-height:1.6;
}

/* STACK */
.stack{
    margin-top:20px;
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:10px;
}
.stack span{
    background:#e0e7ff;
    color:#3730a3;
    padding:6px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:500;
}

/* MISSION */
.mission{
    margin-top:80px;
    background:linear-gradient(135deg,#4f46e5,#0ea5e9);
    color:#fff;
    padding:50px;
    border-radius:30px;
    text-align:center;
}
.mission h2{margin-bottom:12px}
.mission p{
    max-width:700px;
    margin:auto;
    opacity:.95;
    line-height:1.7;
}
</style>
</head>

<body>

<!-- BACK BUTTON -->
<a href="<?= BASE_URL ?>dashboard.php" class="back-btn">
    &#8592; Back
</a>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h1>About Us</h1>
        <p>
            We are a small but dedicated team behind the
            <strong>MobileCare Monitoring System</strong>,
            focused on building efficient, clean, and user-friendly internal tools.
        </p>
    </div>

    <!-- TEAM -->
    <div class="team">

        <!-- MEMBER 1 -->
        <div class="member">
            <img src="<?= BASE_URL ?>assets/dev1.jpg" alt="Team Member">
            <h3>Alexander B. Losaynon</h3>
            <div class="role">Full Stack Developer</div>
            <div class="desc">
                Responsible for system architecture, backend logic,
                and ensuring the platform is scalable, secure, and reliable. Also, focused on user experience, interface design,
                and making sure everything feels intuitive and modern.
            </div>
            <div class="stack">
                <span>PHP</span>
                <span>MySQL</span>
                <span>JavaScript</span>
                <span>CSS</span>
            </div>
        </div>

        <!-- MEMBER 2 -->
        <div class="member">
            <img src="<?= BASE_URL ?>assets/dev2.jpg" alt="Team Member">
            <h3>Karl David Garcia</h3>
            <div class="role">Full Stack Developer</div>
            <div class="desc">
                Handles system architecture and backend logic while maintaining scalability, security, and reliability. Also contributes to UI/UX design to ensure a clean, intuitive, and modern user experience.
            </div>
            <div class="stack">
                <span>PHP</span>
                <span>MySQL</span>
                <span>JavaScript</span>
                <span>CSS</span>
            </div>
        </div>

    </div>

    <!-- MISSION -->
    <div class="mission">
        <h2>Our Mission</h2>
        <p>
            To simplify monitoring workflows and deliver a clean,
            reliable system that helps teams work faster and smarter
            with minimal friction.
        </p>
    </div>

</div>

</body>
</html>
