<?php
require_once 'config.php';
if (current_user()) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'counselor') LIMIT 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($password, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$u['id'],
            'full_name' => $u['full_name'],
            'email' => $u['email'],
            'role' => $u['role'],
            'student_no' => $u['student_no'] ?? null
        ];
        redirect('dashboard.php');
    }
    $error = 'Staff account not found or the email/password is incorrect.';
}
$pageTitle = 'Administrator Sign In | QNHS Guidance Portal';
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
<body class="qnhs-landing qnhs-auth-landing">
<header class="landing-topbar"><span>QUEZON NATIONAL HIGH SCHOOL</span><span>ADMINISTRATOR / COUNSELOR PORTAL</span></header>
<main class="landing-stage">
  <div class="landing-photo" aria-hidden="true"></div>
  <div class="landing-blue-wash" aria-hidden="true"></div>
  <section class="landing-login-card auth-landing-card" aria-label="Administrator and counselor sign in">
    <div class="landing-school-mark"><img src="assets/qnhs-logo.png" alt="Quezon National High School seal" class="qnhs-logo qnhs-logo-home"></div>
    <h1>Quezon National High School</h1>
    <p class="landing-subtitle">Administrator / Counselor Sign In</p>
    <?php if ($error): ?><div class="alert alert-danger auth-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="landing-auth-form">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label for="staff-email">Email Address</label>
      <input id="staff-email" class="form-control" type="email" name="email" placeholder="Enter your official email" autocomplete="username" required>
      <label for="staff-password">Password</label>
      <input id="staff-password" class="form-control" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
      <button type="submit" class="landing-button landing-submit">Staff Sign In</button>
    </form>
    <a class="landing-home-link" href="index.php">← Back to homepage</a>
  </section>
  <div class="landing-version">QNHS GUIDANCE SYSTEM</div>
</main>
<footer class="landing-footer">© <?= date('Y') ?> Quezon National High School · Guidance Office</footer>
</body>
</html>
