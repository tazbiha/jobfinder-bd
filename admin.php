<?php require 'config.php'; need('admin');
if(isset($_GET['deluser'])){ x("DELETE FROM users WHERE id=? AND role<>'admin'",[(int)$_GET['deluser']]); flash('User removed.'); header('Location: admin.php'); exit; }
if(isset($_GET['deljob'])){ x("DELETE FROM jobs WHERE id=?",[(int)$_GET['deljob']]); flash('Job removed.'); header('Location: admin.php'); exit; }
$st=['Job seekers'=>q("SELECT COUNT(*) c FROM users WHERE role='seeker'")[0]['c'],'Employers'=>q("SELECT COUNT(*) c FROM users WHERE role='employer'")[0]['c'],
 'Jobs/Internships'=>q("SELECT COUNT(*) c FROM jobs")[0]['c'],'Applications'=>q("SELECT COUNT(*) c FROM applications")[0]['c'],'Mock tests taken'=>q("SELECT COUNT(*) c FROM mock_results")[0]['c']];
$users=q("SELECT * FROM users ORDER BY id DESC LIMIT 50"); $jobs=q("SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) apps FROM jobs j ORDER BY id DESC LIMIT 50");
$title='Admin'; require 'header.php'; ?>
<h4>Admin panel &amp; analytics</h4><div class="row g-3 mb-4">
<?php foreach($st as $k=>$v): ?><div class="col"><div class="card p-3 text-center"><div class="fs-2 fw-bold text-primary"><?= $v ?></div><small><?= e($k) ?></small></div></div><?php endforeach; ?></div>
<h5>Users</h5><table class="table bg-white"><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr>
<?php foreach($users as $x_): ?><tr><td><?= e($x_['name']) ?></td><td><?= e($x_['email']) ?></td><td><?= e($x_['role']) ?></td><td><?= e(substr($x_['created_at'],0,10)) ?></td>
<td><?php if($x_['role']!='admin'): ?><a class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove user?')" href="?deluser=<?= $x_['id'] ?>">Remove</a><?php endif; ?></td></tr><?php endforeach; ?></table>
<h5>Jobs</h5><div class="table-responsive"><table class="table bg-white"><tr><th>Title</th><th>Company</th><th>Location</th><th>Salary</th><th>Applications</th><th></th></tr>
<?php foreach($jobs as $j): ?><tr><td><?= e($j['title']) ?></td><td><div class="d-flex align-items-center gap-2"><?= company_logo($j) ?><?= e($j['company']) ?></div></td><td><?= e($j['location']) ?></td><td><?= e($j['salary_details']?:'Not specified') ?></td><td><?= $j['apps'] ?></td>
<td><a class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove job?')" href="?deljob=<?= $j['id'] ?>">Remove</a></td></tr><?php endforeach; ?></table></div>
<?php require 'footer.php';
