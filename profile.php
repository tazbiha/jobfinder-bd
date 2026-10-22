<?php require 'config.php'; need('seeker'); $u=user();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $cv=null;
  if(!empty($_FILES['cv']['name']) && $_FILES['cv']['error']===0){
    $ext=strtolower(pathinfo($_FILES['cv']['name'],PATHINFO_EXTENSION));
    if(in_array($ext,['pdf','doc','docx']) && $_FILES['cv']['size']<=2*1024*1024){
      $cv='uploads/cv_'.$u['id'].'_'.time().'.'.$ext; move_uploaded_file($_FILES['cv']['tmp_name'],$cv);
    } else flash('CV must be PDF/DOC/DOCX under 2MB.');
  }
  x("UPDATE users SET name=?, skills=?, education=? WHERE id=?",[trim($_POST['name']),trim($_POST['skills']),trim($_POST['education']),$u['id']]);
  if($cv) x("UPDATE users SET cv_path=? WHERE id=?",[$cv,$u['id']]);
  $_SESSION['user']['name']=trim($_POST['name']); if(!flash()) flash('Profile saved.'); else flash('Saved, but CV rejected: must be PDF/DOC/DOCX under 2MB.');
  header('Location: profile.php'); exit;
}
$me=q("SELECT * FROM users WHERE id=?",[$u['id']])[0];
$res=q("SELECT * FROM mock_results WHERE user_id=? ORDER BY id DESC LIMIT 10",[$u['id']]);
$apps=q("SELECT a.*, j.title, j.company, j.company_logo, j.location, j.salary_details FROM applications a JOIN jobs j ON j.id=a.job_id WHERE a.user_id=? ORDER BY a.id DESC",[$u['id']]);
$appStatusColors=['Pending'=>'secondary','Shortlisted'=>'info','Rejected'=>'danger','Hired'=>'success'];
$title='Profile'; require 'header.php'; ?>
<div class="row g-4"><div class="col-lg-6"><div class="card p-4"><h4>My profile</h4>
<form method="post" enctype="multipart/form-data">
<label class="form-label">Name</label><input class="form-control mb-2" name="name" value="<?= e($me['name']) ?>" required>
<label class="form-label">Skills (comma separated, used for AI recommendations)</label>
<input class="form-control mb-2" name="skills" value="<?= e($me['skills']) ?>" placeholder="php, mysql, javascript, excel">
<label class="form-label">Education / experience</label><textarea class="form-control mb-2" name="education" rows="4"><?= e($me['education']) ?></textarea>
<label class="form-label">CV (PDF/DOC/DOCX, max 2MB)</label><input type="file" class="form-control mb-2" name="cv">
<?php if($me['cv_path']): ?><p><a href="<?= e($me['cv_path']) ?>" target="_blank">Current CV</a></p><?php endif; ?>
<button class="btn btn-primary">Save</button></form></div></div>
<div class="col-lg-6"><div id="application-history" class="card p-4 mb-4"><h5>My application history</h5>
<?php if($apps): ?><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Job</th><th>Location / salary</th><th>Applied</th><th>Status</th></tr></thead><tbody>
<?php foreach($apps as $a): ?><tr><td><div class="d-flex align-items-center gap-2"><?= company_logo($a) ?><div><a href="job.php?id=<?= (int)$a['job_id'] ?>"><?= e($a['title']) ?></a><div class="small text-muted"><?= e($a['company']) ?></div></div></div></td><td><?= e($a['location']) ?><?php if(!empty($a['salary_details'])): ?><div class="small"><?= e($a['salary_details']) ?></div><?php endif; ?></td><td><?= e(substr($a['created_at'],0,10)) ?></td>
<td><span class="badge bg-<?= e($appStatusColors[$a['status']]??'secondary') ?>"><?= e($a['status']) ?></span></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><p class="text-muted mb-0">You have not applied to any jobs yet.</p><?php endif; ?>
</div><div class="card p-4"><h5>Mock test history</h5>
<?php foreach($res as $r): ?><div class="d-flex justify-content-between border-bottom py-1"><span><?= e($r['topic']) ?></span><strong><?= $r['score'] ?>/<?= $r['total'] ?></strong></div><?php endforeach; if(!$res) echo '<p class="text-muted">No tests taken yet.</p>'; ?></div></div></div>
<?php require 'footer.php';
