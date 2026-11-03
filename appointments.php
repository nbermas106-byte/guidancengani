<?php
require_once 'config.php'; require_role(['student']); if (current_user()['role'] === 'student') verify_csrf();
$u=current_user(); $error='';
if($_SERVER['REQUEST_METHOD']==='POST' && $u['role']==='student'){
 $type=trim($_POST['consultation_type']??''); $date=$_POST['preferred_date']??''; $time=$_POST['preferred_time']??''; $mode=$_POST['mode']??'In-person'; $notes=trim($_POST['notes']??'');
 $allowed=['Academic Concern','Personal / Social Concern','Family Concern','Career Guidance','Other'];
 if(!in_array($type,$allowed,true)||!$date||!$time||strtotime($date)<strtotime(date('Y-m-d'))){$error='Please provide a valid consultation type, date, and time.';}
 else { $s=db()->prepare("INSERT INTO appointments(student_id,consultation_type,preferred_date,preferred_time,mode,notes,status) VALUES(?,?,?,?,?,?,'Pending')");
  $s->execute([$u['id'],$type,$date,$time,$mode,$notes]); flash('success','Your consultation request has been submitted. Please check its status here.'); redirect('appointments.php'); }
}
$s=db()->prepare('SELECT * FROM appointments WHERE student_id=? ORDER BY created_at DESC'); $s->execute([$u['id']]); $rows=$s->fetchAll();
$pageTitle='Consultations'; include 'header.php';
?>
<div class="row g-4"><?php if($u['role']==='student'): ?><div class="col-lg-5"><div class="form-card"><h3>Request a consultation</h3><p class="text-secondary">Choose a topic and your preferred schedule. The Guidance Office will review your request.</p>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label class="form-label">Consultation type</label><select class="form-select mb-3" name="consultation_type" required><option value="">Select a topic</option><?php foreach(['Academic Concern','Personal / Social Concern','Family Concern','Career Guidance','Other'] as $t): ?><option><?= e($t) ?></option><?php endforeach; ?></select>
<label class="form-label">Preferred date</label><input class="form-control mb-3" type="date" name="preferred_date" min="<?= date('Y-m-d') ?>" required>
<label class="form-label">Preferred time</label><input class="form-control mb-3" type="time" name="preferred_time" required>
<label class="form-label">Preferred mode</label><select class="form-select mb-3" name="mode"><option>In-person</option><option>Online</option></select>
<label class="form-label">Additional note <span class="text-secondary">(optional)</span></label><textarea class="form-control" rows="4" name="notes" maxlength="1500" placeholder="Share only the information needed to help schedule your consultation."></textarea>
<button class="btn btn-green w-100 mt-3">Submit request</button></form></div></div><?php else: ?><div class="col-12"><div class="alert alert-info">Administrator view: this page is accessible for review. Student consultation requests are submitted through student accounts; use <a href="manage_appointments.php">Manage Requests</a> to review all submissions.</div></div><?php endif; ?>
<div class="col-lg-7"><div class="content-card"><h4>My consultation history</h4><?php if(!$rows): ?><p class="text-secondary">Your requests will appear here.</p><?php else: ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Topic</th><th>Schedule</th><th>Status</th><th>Office note</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= e($r['consultation_type']) ?><div class="small text-secondary"><?= e($r['mode']) ?></div></td><td><?= e($r['preferred_date']) ?><br><?= e(substr($r['preferred_time'],0,5)) ?></td><td><span class="status status-<?= strtolower($r['status']) ?>"><?= e($r['status']) ?></span></td><td class="small"><?= e($r['counselor_note']?:'—') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div></div>
<?php include 'footer.php'; ?>
