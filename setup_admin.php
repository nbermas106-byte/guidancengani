<?php
require_once 'config.php';
$count=(int)db()->query("SELECT COUNT(*) FROM users WHERE role IN ('admin','counselor')")->fetchColumn();
if($count>0){exit('An administrator or counselor account already exists. Delete setup_admin.php now.');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$name=trim($_POST['name']??'');$email=strtolower(trim($_POST['email']??''));$role=$_POST['role']??'admin';$pass=$_POST['password']??'';
 if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<10||!in_array($role,['admin','counselor'],true))$error='Enter valid details. Staff password must have at least 10 characters.';
 else{try{$s=db()->prepare("INSERT INTO users(full_name,email,password,role) VALUES(?,?,?,?)");$s->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT),$role]);echo 'Staff account created. Sign in at <a href="admin_login.php">login.php</a>. Delete setup_admin.php from your server now.';exit;}catch(PDOException $e){$error='Email is already in use.';}}
}
$pageTitle='Initial Staff Setup';include 'header.php';
?>
<div class="row justify-content-center"><div class="col-md-6"><div class="form-card"><h2>Initial staff account setup</h2><p class="text-secondary">Create the first administrator or counselor account. This page locks after a staff account is created.</p><?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label class="form-label">Full name</label><input class="form-control mb-3" name="name" required><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Account role</label><select class="form-select mb-3" name="role"><option value="admin">Administrator</option><option value="counselor">Guidance Counselor</option></select><label class="form-label">Password (10+ characters)</label><input class="form-control" type="password" name="password" minlength="10" required><button class="btn btn-green w-100 mt-3">Create staff account</button></form></div></div></div>
<?php include 'footer.php'; ?>
