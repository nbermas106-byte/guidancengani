<?php
require_once 'config.php';
if (current_user()) redirect('dashboard.php');
$pageTitle = 'QNHS Guidance Portal';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body class="qnhs-landing">
<header class="landing-topbar"><span>QUEZON NATIONAL HIGH SCHOOL</span><span>GUIDANCE CONSULTATION PORTAL</span></header>
<main class="landing-stage">
  <div class="landing-photo" aria-hidden="true"></div>
  <div class="landing-blue-wash" aria-hidden="true"></div>
  <section class="landing-login-card" aria-label="Choose your portal">
    <div class="landing-school-mark" aria-label="Quezon National High School official seal">
      <img src="assets/qnhs-logo.png" alt="Quezon National High School seal" class="qnhs-logo qnhs-logo-home">
    </div>
    <h1>Quezon National High School</h1>
    <p class="landing-subtitle">Guidance Consultation &amp; Career Progression</p>
    <div class="landing-actions">
      <a class="landing-button" href="admin_login.php">Administrator</a>
      <a class="landing-button" href="admin_login.php">Guidance Counselor</a>
      <a class="landing-button" href="student_login.php">Student</a>
      <a class="landing-button" href="signup.php">Student Registration</a>
    </div>
    <p class="landing-help">A safe space to seek guidance, discover strengths, and plan your future.</p>
  </section>
  <div class="landing-version">QNHS GUIDANCE SYSTEM</div>
</main>
<footer class="landing-footer">© <?= date('Y') ?> Quezon National High School · Guidance Office</footer>
</body>
</html>
