<?php
require_once __DIR__ . '/config.php';
$user = current_user();
$pageTitle = $pageTitle ?? 'Guidance Portal';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
function qnhs_nav_active(string $page, string $currentPage): string { return $page === $currentPage ? ' active' : ''; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#063bc5">
<title><?= e($pageTitle) ?> | QNHS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body class="qnhs-inner-page">
<nav class="navbar navbar-expand-xl navbar-dark site-nav qnhs-main-nav">
 <div class="container">
  <a class="navbar-brand qnhs-brand d-flex align-items-center gap-2" href="<?= $user ? 'dashboard.php' : 'index.php' ?>">
   <img src="assets/qnhs-logo.png" alt="QNHS seal" class="qnhs-logo-nav">
   <span class="qnhs-brand-text"><strong>QUEZON NATIONAL HIGH SCHOOL</strong><small>GUIDANCE SERVICES</small></span>
  </a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#qnhsNavigation" aria-controls="qnhsNavigation" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
  <div class="collapse navbar-collapse" id="qnhsNavigation"><ul class="navbar-nav ms-auto align-items-xl-center qnhs-nav-links">
  <?php if ($user): ?>
   <li class="nav-item"><a class="nav-link<?= qnhs_nav_active('dashboard.php',$currentPage) ?>" href="dashboard.php">Dashboard</a></li>
   <?php if ($user['role']==='student'): ?>
    <li class="nav-item"><a class="nav-link<?= qnhs_nav_active('appointments.php',$currentPage) ?>" href="appointments.php">Consultations</a></li>
    <li class="nav-item"><a class="nav-link<?= qnhs_nav_active('career.php',$currentPage) ?>" href="career.php">My Career Plan</a></li>
    <li class="nav-item dropdown"><a class="nav-link dropdown-toggle<?= in_array($currentPage,['career_resources.php','curriculum_exits.php','resume_guides.php'],true)?' active':'' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Student Resources</a>
     <ul class="dropdown-menu dropdown-menu-end qnhs-resource-menu"><li><a class="dropdown-item" href="career_resources.php">100+ Career Paths & Skills</a></li><li><a class="dropdown-item" href="curriculum_exits.php">Curriculum Exits & Transition Support</a></li><li><a class="dropdown-item" href="resume_guides.php">Resume & Application Guides</a></li></ul>
    </li>
   <?php else: ?>
    <li class="nav-item"><a class="nav-link<?= qnhs_nav_active('manage_appointments.php',$currentPage) ?>" href="manage_appointments.php">Manage Requests</a></li>
    <li class="nav-item"><a class="nav-link<?= qnhs_nav_active('students.php',$currentPage) ?>" href="students.php">Students</a></li>
    <?php if($user['role']==='admin'): ?>
     <li class="nav-item"><a class="nav-link<?= qnhs_nav_active('admin_signup.php',$currentPage) ?>" href="admin_signup.php">Add Staff</a></li>
    <?php endif; ?>
   <?php endif; ?>
   <li class="nav-item qnhs-user-item"><a class="qnhs-profile-nav" href="profile.php" title="Update profile picture"><?php if(!empty($user['profile_picture'])): ?><img src="<?= e($user['profile_picture']) ?>" alt="Profile picture"><?php else: ?><span class="qnhs-avatar-initial"><?= e(strtoupper(substr($user['full_name'],0,1))) ?></span><?php endif; ?><span class="qnhs-user-label"><?= e($user['full_name']) ?><small><?= e(strtoupper($user['role'])) ?> · PROFILE</small></span></a></li>
   <li class="nav-item"><a class="btn qnhs-signout-btn" href="logout.php">Sign Out</a></li>
  <?php else: ?>
   <li class="nav-item"><a class="nav-link" href="student_login.php">Student Login</a></li><li class="nav-item"><a class="nav-link" href="admin_login.php">Staff Login</a></li><li class="nav-item"><a class="btn qnhs-signout-btn" href="signup.php">Register</a></li>
  <?php endif; ?>
  </ul></div>
 </div>
</nav>
<main class="container py-4 qnhs-page-content">
<?php if ($m=flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<?php if ($m=flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
