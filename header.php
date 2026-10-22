<?php $u = user(); ?>
<!doctype html><html lang="en" data-bs-theme="dark"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? 'Job Finder BD') ?> - WORKIVA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/style.css?v=20261022-opportunities-animation-v6" rel="stylesheet"></head><body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary"><div class="container">
<a class="navbar-brand fw-bold" href="index.php"><span class="brand-mark">WORKIVA BD</span></a>
<button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nv"><span class="navbar-toggler-icon"></span></button>
<div id="nv" class="collapse navbar-collapse"><ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link" href="jobs.php">Browse Jobs</a></li>
<?php if($u && $u['role']==='seeker'): ?>
<li class="nav-item"><a class="nav-link" href="recommend.php">Recommended</a></li>
<li class="nav-item"><a class="nav-link" href="mocktest.php">AI Mock Test</a></li>
<li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
<?php elseif($u && $u['role']==='employer'): ?>
<li class="nav-item"><a class="nav-link" href="employer.php">Dashboard</a></li>
<li class="nav-item"><a class="nav-link" href="post_job.php">Post a Job</a></li>
<?php elseif($u && $u['role']==='admin'): ?>
<li class="nav-item"><a class="nav-link" href="admin.php">Admin Panel</a></li>
<?php endif; ?></ul>
<ul class="navbar-nav align-items-lg-center gap-lg-2">
<?php if($u): ?><li class="nav-item"><span class="nav-link">Hi, <?= e($u['name']) ?></span></li>
<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
<?php else: ?><li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
<li class="nav-item"><a class="nav-link" href="register.php">Register</a></li><?php endif; ?>
</ul></div>
<?php if(!empty($showQuizTimer)): ?><span id="quizTimer" class="quiz-navbar-timer" data-ends-at="<?= $quizEndsAt ?>"><?= gmdate('i:s',$quizSecondsLeft) ?></span><?php endif; ?>
</div></nav>
<main class="container py-4">
<?php if($f=flash()): ?><div class="alert alert-info"><?= e($f) ?></div><?php endif; ?>
