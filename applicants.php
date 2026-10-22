<?php require 'config.php'; need('employer'); $u=user(); $jid=(int)($_GET['job']??0);
$job=q("SELECT * FROM jobs WHERE id=? AND employer_id=?",[$jid,$u['id']]); if(!$job){ header('Location: employer.php'); exit; }
if($_SERVER['REQUEST_METHOD']==='POST' && in_array($_POST['status'],['Pending','Shortlisted','Rejected','Hired'])){
  x("UPDATE applications SET status=? WHERE id=? AND job_id=?",[$_POST['status'],(int)$_POST['aid'],$jid]); flash('Status updated.'); header("Location: applicants.php?job=$jid"); exit; }
$apps=q("SELECT a.*, u.name, u.email, u.skills, u.education, u.cv_path,
 (SELECT ROUND(AVG(score/total*100)) FROM mock_results m WHERE m.user_id=u.id) avg_score,
 (SELECT COUNT(*) FROM mock_results m WHERE m.user_id=u.id) tests
 FROM applications a JOIN users u ON u.id=a.user_id WHERE a.job_id=? ORDER BY a.id DESC",[$jid]);
$title='Applicants'; require 'header.php'; ?>
<a href="employer.php">&larr; Back</a><div class="d-flex align-items-center gap-3 my-3"><?= company_logo($job[0]) ?><div><h4 class="mb-1">Applicants for: <?= e($job[0]['title']) ?></h4><div class="text-muted"><?= e($job[0]['company']) ?> &middot; <?= e($job[0]['location']) ?><?php if(!empty($job[0]['salary_details'])): ?> &middot; <?= e($job[0]['salary_details']) ?><?php endif; ?></div></div></div>
<?php foreach($apps as $a): ?><div class="card p-3 mb-3"><div class="row"><div class="col-md-8">
<h5><?= e($a['name']) ?> <small class="text-muted"><?= e($a['email']) ?></small></h5>
<div><strong>Skills:</strong> <?= e($a['skills']) ?></div><div><strong>Education:</strong> <?= e($a['education']) ?></div>
<?php if($a['cover']): ?><div><strong>Cover letter:</strong> <?= nl2br(e($a['cover'])) ?></div><?php endif; ?>
<div><strong>AI mock test average:</strong> <?= $a['tests']?e($a['avg_score']).'% ('.$a['tests'].' tests)':'<em>none taken</em>' ?></div>
<?php if($a['cv_path']): ?><a href="<?= e($a['cv_path']) ?>" target="_blank">Download CV</a><?php endif; ?></div>
<div class="col-md-4"><form method="post"><input type="hidden" name="aid" value="<?= $a['id'] ?>">
<select name="status" class="form-select mb-2"><?php foreach(['Pending','Shortlisted','Rejected','Hired'] as $s) echo '<option'.($a['status']==$s?' selected':'').'>'.$s.'</option>'; ?></select>
<button class="btn btn-primary btn-sm">Update status</button></form></div></div></div><?php endforeach; ?>
<?php if(!$apps) echo '<p class="text-muted">No applicants yet.</p>'; require 'footer.php';
