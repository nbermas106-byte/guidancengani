<?php
require_once 'config.php';
if (current_user()) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 verify_csrf();
 $name = trim($_POST['full_name'] ?? '');
 $email = strtolower(trim($_POST['email'] ?? ''));
 $studentNo = trim($_POST['student_no'] ?? '');
 $grade = trim($_POST['grade_level'] ?? '');
 $section = trim($_POST['section'] ?? '');
 $password = $_POST['password'] ?? '';
 $confirm = $_POST['confirm_password'] ?? '';
 if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $studentNo === '' || $grade === '' || strlen($password) < 8) {
  $error = 'Complete all required fields. Use a valid email and a password with at least 8 characters.';
 } elseif ($password !== $confirm) {
  $error = 'Passwords do not match.';
 } else {
  try {
   $stmt = db()->prepare("INSERT INTO users (full_name,email,student_no,grade_level,section,password,role) VALUES (?,?,?,?,?,?, 'student')");
   $stmt->execute([$name,$email,$studentNo,$grade,$section,password_hash($password,PASSWORD_DEFAULT)]);
   flash('success','Your student account has been created. You may now sign in.');
   redirect('student_login.php');
  } catch (PDOException $ex) { $error = 'That email address or student number is already registered.'; }
 }
}
$pageTitle='Student Sign Up'; include 'header.php';
?>
<div class="row justify-content-center"><div class="col-lg-7"><div class="form-card">
<h2>Create student account</h2><p class="text-secondary">Register using your school information.</p>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<div class="row g-3">
 <div class="col-md-6"><label class="form-label">Full name *</label><input class="form-control" name="full_name" required value="<?= e($_POST['full_name']??'') ?>"></div>
 <div class="col-md-6"><label class="form-label">Student number *</label><input class="form-control" name="student_no" required value="<?= e($_POST['student_no']??'') ?>"></div>
 <div class="col-md-6"><label class="form-label">School email *</label><input class="form-control" type="email" name="email" required value="<?= e($_POST['email']??'') ?>"></div>
 <div class="col-md-6"><label class="form-label">Grade level *</label><select class="form-select" name="grade_level" required><option value="">Select grade</option><?php foreach(['Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12'] as $g): ?><option <?= (($_POST['grade_level']??'')===$g?'selected':'') ?>><?= e($g) ?></option><?php endforeach; ?></select></div>
 <div class="col-md-6"><label class="form-label">Section</label><input class="form-control" name="section" value="<?= e($_POST['section']??'') ?>"></div>
 <div class="col-md-6"><label class="form-label">Password *</label><input class="form-control" type="password" name="password" minlength="8" required></div>
 <div class="col-12"><label class="form-label">Confirm password *</label><input class="form-control" type="password" name="confirm_password" minlength="8" required></div>
</div>
<button class="btn btn-green w-100 mt-4">Create account</button>
</form><p class="small text-center mt-3 mb-0">Already registered? <a href="student_login.php">Sign in</a></p>
</div></div></div>
<?php include 'footer.php'; ?>
