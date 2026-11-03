<?php
require_once 'config.php'; require_login();
$u=current_user();
if($u['role']==='student'){
 $q=db()->prepare("SELECT COUNT(*) FROM appointments WHERE student_id=?"); $q->execute([$u['id']]); $total=(int)$q->fetchColumn();
 $q=db()->prepare("SELECT COUNT(*) FROM appointments WHERE student_id=? AND status='Pending'"); $q->execute([$u['id']]); $pending=(int)$q->fetchColumn();
 $q=db()->prepare("SELECT * FROM appointments WHERE student_id=? ORDER BY created_at DESC LIMIT 5"); $q->execute([$u['id']]); $recent=$q->fetchAll();
} else {
 $total=(int)db()->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
 $pending=(int)db()->query("SELECT COUNT(*) FROM appointments WHERE status='Pending'")->fetchColumn();
 $recent=db()->query("SELECT a.*,u.full_name FROM appointments a JOIN users u ON u.id=a.student_id ORDER BY a.created_at DESC LIMIT 8")->fetchAll();
}
$pageTitle='Dashboard'; include 'header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><span class="eyebrow">YOUR SPACE</span><h2 class="mt-2">Hello, <?= e($u['full_name']) ?>!</h2><p class="text-secondary mb-0"><?= $u['role']==='student'?'Here is an overview of your guidance journey.':'Here is the current guidance office overview.' ?></p></div>
<?php if($u['role']==='student'): ?><a class="btn btn-green" href="appointments.php">+ Request consultation</a><?php else: ?><a class="btn btn-green" href="manage_appointments.php">Review requests</a><?php endif; ?></div>
<div class="row g-3 mb-4">
 <div class="col-md-4"><div class="stat-card"><span><?= $u['role']==='student'?'My requests':'Total requests' ?></span><strong><?= $total ?></strong></div></div>
 <div class="col-md-4"><div class="stat-card"><span>Pending requests</span><strong><?= $pending ?></strong></div></div>
 <div class="col-md-4"><div class="stat-card"><span>Account type</span><strong class="role-label"><?= e(ucfirst($u['role'])) ?></strong></div></div>
</div>
<div class="content-card">
 <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0"><?= $u['role']==='student'?'Recent consultation requests':'Latest consultation requests' ?></h5>
 <?php if($u['role']==='student'): ?><a href="appointments.php" class="small">View all</a><?php else: ?><a href="manage_appointments.php" class="small">Manage all</a><?php endif; ?></div>
 <?php if(!$recent): ?><p class="text-secondary mb-0">No consultation requests yet.</p><?php else: ?>
 <div class="table-responsive"><table class="table align-middle"><thead><tr><?php if($u['role']!=='student'): ?><th>Student</th><?php endif; ?><th>Type</th><th>Preferred date</th><th>Status</th></tr></thead><tbody>
 <?php foreach($recent as $a): ?><tr><?php if($u['role']!=='student'): ?><td><?= e($a['full_name']) ?></td><?php endif; ?><td><?= e($a['consultation_type']) ?></td><td><?= e($a['preferred_date']) ?></td><td><span class="status status-<?= strtolower($a['status']) ?>"><?= e($a['status']) ?></span></td></tr><?php endforeach; ?>
 </tbody></table></div><?php endif; ?>
</div>
<?php if($u['role']==='student'): ?><div class="row g-3 mt-1"><div class="col-12"><a class="career-library-banner" href="career_resources.php"><span class="career-library-icon">✦</span><span><strong>Explore 150+ Career Paths & Skills</strong><small>Read career descriptions, discover useful skills, and learn how to prepare for different fields.</small></span><b>Explore careers →</b></a></div><div class="col-md-6"><a class="quick-link" href="career.php"><strong>My career plan</strong><span>Reflect on your interests and save your goals →</span></a></div><div class="col-md-6"><a class="quick-link" href="appointments.php"><strong>Guidance consultation</strong><span>Request a private appointment with the Guidance Office →</span></a></div><div class="col-md-4"><a class="quick-link" href="career_resources.php"><strong>Career paths & skills</strong><span>Explore 150 career examples and transferable skills →</span></a></div><div class="col-md-4"><a class="quick-link" href="curriculum_exits.php"><strong>Transition support</strong><span>Plan your next step after school →</span></a></div><div class="col-md-4"><a class="quick-link" href="resume_guides.php"><strong>Resume guides</strong><span>Prepare for applications and opportunities →</span></a></div></div><?php endif; ?>
<?php include 'footer.php'; ?>
