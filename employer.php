<?php require 'config.php'; need('employer'); $u=user();
if(isset($_GET['del'])){ x("DELETE FROM jobs WHERE id=? AND employer_id=?",[(int)$_GET['del'],$u['id']]); flash('Job deleted.'); header('Location: employer.php'); exit; }
$jobs=q("SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) apps FROM jobs j WHERE employer_id=? ORDER BY id DESC",[$u['id']]);
$title='Employer Dashboard'; require 'header.php'; ?>
<div class="d-flex justify-content-between"><h4>My postings</h4><a href="post_job.php" class="btn btn-primary">+ Post new</a></div>
<div class="table-responsive"><table class="table bg-white mt-3"><tr><th>Posting</th><th>Location</th><th>Salary</th><th>Deadline</th><th>Applicants</th><th></th></tr>
<?php foreach($jobs as $j): ?><tr><td><div class="d-flex align-items-center gap-2"><?= company_logo($j) ?><div><?= e($j['title']) ?><div class="small text-muted"><?= e($j['type']) ?></div></div></div></td><td><?= e($j['location']) ?></td><td><?= e($j['salary_details']?:'Not specified') ?></td><td><?= e($j['deadline']) ?></td><td><?= $j['apps'] ?></td>
<td><a class="btn btn-sm btn-outline-primary" href="applicants.php?job=<?= $j['id'] ?>">Applicants</a>
<a class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')" href="?del=<?= $j['id'] ?>">Delete</a></td></tr><?php endforeach; ?></table></div>
<?php require 'footer.php';
