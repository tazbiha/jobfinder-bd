<?php require 'config.php'; $title='Browse Jobs';
$qs=trim($_GET['q']??''); $type=$_GET['type']??''; $cat=trim($_GET['category']??''); $loc=trim($_GET['location']??''); $salary=trim($_GET['salary']??'');
$sql="SELECT * FROM jobs WHERE 1"; $p=[];
if($qs!==''){ $sql.=" AND (title LIKE ? OR company LIKE ? OR skills LIKE ?)"; $l="%$qs%"; array_push($p,$l,$l,$l); }
if(in_array($type,['Job','Internship'])){ $sql.=" AND type=?"; $p[]=$type; }
if($cat!==''){ $sql.=" AND category LIKE ?"; $p[]="%$cat%"; }
if($loc!==''){ $sql.=" AND location LIKE ?"; $p[]="%$loc%"; }
if($salary!==''){ $sql.=" AND salary_details LIKE ?"; $p[]="%$salary%"; }
$jobs=q($sql." ORDER BY created_at DESC",$p); require 'header.php'; ?>
<form class="row g-2 mb-4">
<div class="col-md-3"><input name="q" value="<?= e($qs) ?>" class="form-control" placeholder="Keyword / skill / company"></div>
<div class="col-md-2"><select name="type" class="form-select"><option value="">All types</option><option <?= $type=='Job'?'selected':'' ?>>Job</option><option <?= $type=='Internship'?'selected':'' ?>>Internship</option></select></div>
<div class="col-md-2"><select name="category" class="form-select"><option value="">All categories</option>
<?php foreach(array_unique(array_merge(job_categories(),['IT','Admin','Accounting','Marketing'])) as $category): ?><option value="<?= e($category) ?>" <?= $cat===$category?'selected':'' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><input name="location" value="<?= e($loc) ?>" class="form-control" placeholder="Location"></div>
<div class="col-md-2"><input name="salary" value="<?= e($salary) ?>" class="form-control" placeholder="Salary (e.g. BDT)"></div>
<div class="col-md-1"><button class="btn btn-primary w-100">Filter</button></div></form>
<p class="text-muted"><?= count($jobs) ?> result(s)</p>
<?php foreach($jobs as $j): ?><div class="card p-3 mb-3"><div class="d-flex justify-content-between flex-wrap gap-3">
<div class="d-flex gap-3"><?= company_logo($j) ?><div><h5 class="mb-1"><?= e($j['title']) ?> <span class="badge bg-<?= $j['type']=='Job'?'primary':'success' ?> badge-type"><?= e($j['type']) ?></span></h5>
<div class="text-muted"><?= e($j['company']) ?> &middot; <?= e($j['location']) ?> &middot; <?= e($j['category']) ?></div>
<div><strong>Salary:</strong> <?= e($j['salary_details']?:'Not disclosed') ?></div>
<small>Skills: <?= e($j['skills']) ?> &middot; Deadline: <?= e($j['deadline']) ?></small></div>
<a class="btn btn-outline-primary align-self-center" href="job.php?id=<?= $j['id'] ?>">Details</a></div></div></div><?php endforeach; ?>
<?php require 'footer.php';
