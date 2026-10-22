<?php require 'config.php'; $id=(int)($_GET['id']??0); $j=q("SELECT * FROM jobs WHERE id=?",[$id]);
if(!$j){ header('Location: jobs.php'); exit; } $j=$j[0]; $u=user();
if($_SERVER['REQUEST_METHOD']==='POST' && $u && $u['role']==='seeker'){
  if(q("SELECT id FROM applications WHERE job_id=? AND user_id=?",[$id,$u['id']])) flash('You already applied.');
  else { x("INSERT INTO applications(job_id,user_id,cover) VALUES(?,?,?)",[$id,$u['id'],trim($_POST['cover'])]); flash('Application submitted!'); }
  header("Location: job.php?id=$id"); exit;
}
$applied = $u ? q("SELECT status FROM applications WHERE job_id=? AND user_id=?",[$id,$u['id']]) : [];
$title=$j['title']; require 'header.php'; ?>
<div class="card p-4"><div class="d-flex align-items-center gap-3 mb-2"><?= company_logo($j) ?><div><h3 class="mb-1"><?= e($j['title']) ?> <span class="badge bg-secondary fs-6"><?= e($j['type']) ?></span></h3>
<div class="text-muted"><?= e($j['company']) ?></div></div></div>
<div class="text-muted mb-3"><?= e($j['location']) ?> &middot; <?= e($j['category']) ?> &middot; Deadline: <?= e($j['deadline']) ?></div>
<p><strong>Salary:</strong> <?= e($j['salary_details']?:'Not disclosed') ?></p>
<p><?= nl2br(e($j['description'])) ?></p><p><strong>Required skills:</strong> <?= e($j['skills']) ?></p><hr>
<?php if(!$u): ?><a href="login.php" class="btn btn-primary">Login to apply</a>
<?php elseif($u['role']!=='seeker'): ?><p class="text-muted">Only job seekers can apply.</p>
<?php elseif($applied): ?><div class="alert alert-success mb-0">Applied. Status: <strong><?= e($applied[0]['status']) ?></strong></div>
<?php else: ?><form method="post"><label class="form-label">Cover letter (optional)</label>
<textarea name="cover" class="form-control mb-3" rows="4"></textarea><button class="btn btn-success">Apply now</button></form><?php endif; ?></div>
<?php require 'footer.php';
