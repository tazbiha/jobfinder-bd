<?php require 'config.php'; need('seeker'); $u=user();
$me=q("SELECT skills FROM users WHERE id=?",[$u['id']])[0];
function toks($s){ return array_values(array_unique(array_filter(array_map('trim',preg_split('/[\s,;\/|]+/',strtolower((string)$s)))))); }
$mine=toks($me['skills']); $list=[];
if($mine) foreach(q("SELECT * FROM jobs") as $j){
  $need=toks($j['skills'].' '.$j['category']); if(!$need) continue;
  $match=array_intersect($need,$mine); $score=round(count($match)/count($need)*100);
  if($score>0){ $j['score']=$score; $j['match']=$match; $list[]=$j; }
}
usort($list,fn($a,$b)=>$b['score']<=>$a['score']);
$title='Recommended'; require 'header.php'; ?>
<h4>Jobs recommended for your skills</h4>
<?php if(!$mine): ?><div class="alert alert-warning">Add your skills in <a href="profile.php">Profile</a> to get recommendations.</div><?php endif; ?>
<?php foreach($list as $j): ?><div class="card p-3 mb-3"><div class="d-flex justify-content-between align-items-center gap-3">
<div class="d-flex align-items-center gap-3"><?= company_logo($j) ?><div><h5><?= e($j['title']) ?> <small class="text-muted">- <?= e($j['company']) ?></small></h5>
<div class="text-muted"><?= e($j['location']) ?><?php if(!empty($j['salary_details'])): ?> &middot; <?= e($j['salary_details']) ?><?php endif; ?></div><small>Matched skills: <?= e(implode(', ',$j['match'])) ?></small></div></div>
<div class="text-end"><div class="fs-4 fw-bold text-primary"><?= $j['score'] ?>%</div><a class="btn btn-sm btn-outline-primary" href="job.php?id=<?= $j['id'] ?>">View</a></div></div></div><?php endforeach; ?>
<?php if($mine && !$list) echo '<p class="text-muted">No matching jobs yet.</p>'; require 'footer.php';
