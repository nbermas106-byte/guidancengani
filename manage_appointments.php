<?php
require_once 'config.php'; require_role(['admin','counselor']); verify_csrf();
if($_SERVER['REQUEST_METHOD']==='POST'){
 $id=(int)($_POST['id']??0); $status=$_POST['status']??'Pending'; $note=trim($_POST['counselor_note']??'');
 if($id>0 && in_array($status,['Pending','Approved','Completed','Declined'],true)){
  $s=db()->prepare('UPDATE appointments SET status=?, counselor_note=?, reviewed_by=? WHERE id=?');
  $s->execute([$status,$note,current_user()['id'],$id]); flash('success','Consultation request updated.');
 }
 redirect('manage_appointments.php');
}
$rows=db()->query("SELECT a.*,u.full_name,u.student_no,u.grade_level,u.section FROM appointments a JOIN users u ON u.id=a.student_id ORDER BY FIELD(a.status,'Pending','Approved','Completed','Declined'),a.preferred_date ASC")->fetchAll();
$pageTitle='Manage Requests'; include 'header.php';
?>
<h2>Consultation requests</h2><p class="text-secondary">Review student requests and update their appointment status.</p>
<div class="content-card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Student / topic</th><th>Preferred schedule</th><th>Details</th><th>Update</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><strong><?= e($r['full_name']) ?></strong><div class="small text-secondary"><?= e($r['student_no']) ?> · <?= e($r['grade_level']) ?> <?= e($r['section']) ?></div><div class="small"><?= e($r['consultation_type']) ?></div></td>
<td><?= e($r['preferred_date']) ?><br><?= e(substr($r['preferred_time'],0,5)) ?><div class="small text-secondary"><?= e($r['mode']) ?></div><span class="status status-<?= strtolower($r['status']) ?>"><?= e($r['status']) ?></span></td>
<td class="small"><?= e($r['notes']?:'No additional note') ?></td><td style="min-width:220px"><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><select class="form-select form-select-sm mb-2" name="status"><?php foreach(['Pending','Approved','Completed','Declined'] as $st): ?><option <?= $r['status']===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select><textarea class="form-control form-control-sm mb-2" name="counselor_note" rows="2" placeholder="Optional office note"><?= e($r['counselor_note']) ?></textarea><button class="btn btn-sm btn-green">Save update</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php if(!$rows): ?><p class="text-secondary">No requests found.</p><?php endif; ?></div>
<?php include 'footer.php'; ?>
