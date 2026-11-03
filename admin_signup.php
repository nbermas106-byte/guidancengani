<?php
require_once 'config.php';
require_role(['admin']);
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $name=trim($_POST['full_name']??''); $email=strtolower(trim($_POST['email']??'')); $role=$_POST['role']??'counselor'; $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
 if($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || !in_array($role,['admin','counselor'],true) || strlen($password)<10) $error='Complete all fields. Staff passwords must contain at least 10 characters.';
 elseif($password!==$confirm) $error='Passwords do not match.';
 else { try{$q=db()->prepare('INSERT INTO users(full_name,email,password,role) VALUES(?,?,?,?)');$q->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$role]);flash('success','Staff account created successfully.');redirect('students.php');}catch(PDOException $ex){$error='That email address is already registered.';} }
}
$pageTitle='Create Staff Account';include 'header.php';
?>
<div class="row justify-content-center py-3"><div class="col-lg-7"><div class="form-card"><span class="eyebrow">ADMINISTRATION</span><h2 class="mt-2">Create staff account</h2><p class="text-secondary">Only signed-in administrators can create administrator or counselor accounts. There is no public staff registration.</p><?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="mb-3"><label class="form-label">Full name</label><input class="form-control" name="full_name" required></div><div class="mb-3"><label class="form-label">Official email</label><input class="form-control" type="email" name="email" required></div><div class="mb-3"><label class="form-label">Staff role</label><select class="form-select" name="role"><option value="counselor">Guidance Counselor</option><option value="admin">Administrator</option></select></div><div class="mb-3"><label class="form-label">Password (at least 10 characters)</label><input class="form-control" type="password" name="password" minlength="10" required></div><div class="mb-3"><label class="form-label">Confirm password</label><input class="form-control" type="password" name="confirm_password" minlength="10" required></div><button class="btn btn-green w-100">Create staff account</button></form></div></div></div>
<?php include 'footer.php'; ?>
