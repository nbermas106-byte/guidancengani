<?php
require_once 'config.php'; require_role(['admin','counselor']);
$rows=db()->query("SELECT id,full_name,email,student_no,grade_level,section,created_at FROM users WHERE role='student' ORDER BY full_name")->fetchAll();
$pageTitle='Students';include 'header.php';
?>
<h2>Registered students</h2><p class="text-secondary">Student account directory. Access is limited to authorized guidance staff.</p>
<div class="content-card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Name</th><th>Student number</th><th>Grade & section</th><th>Email</th><th>Registered</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= e($r['full_name']) ?></td><td><?= e($r['student_no']) ?></td><td><?= e($r['grade_level']) ?> <?= e($r['section']) ?></td><td><?= e($r['email']) ?></td><td><?= e(date('M d, Y',strtotime($r['created_at']))) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php if(!$rows): ?><p class="text-secondary">No student accounts yet.</p><?php endif; ?></div>
<?php include 'footer.php'; ?>
