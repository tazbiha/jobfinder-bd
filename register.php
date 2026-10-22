<?php require 'config.php'; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $n=trim($_POST['name']); $em=trim($_POST['email']); $p=$_POST['password']; $r=($_POST['role']??'')==='employer'?'employer':'seeker';
  if(!$n || !filter_var($em,FILTER_VALIDATE_EMAIL) || strlen($p)<6) $err='Fill all fields correctly (password min 6 characters).';
  elseif(q("SELECT id FROM users WHERE email=?",[$em])) $err='Email already registered.';
  else { x("INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)",[$n,$em,password_hash($p,PASSWORD_DEFAULT),$r]); flash('Registration successful. Please login.'); header('Location: login.php'); exit; }
}
$title='Register'; require 'header.php'; ?>
<div class="row justify-content-center"><div class="col-md-5"><div class="card p-4"><h4>Create account</h4>
<?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
<form method="post">
<input class="form-control mb-2" name="name" placeholder="Full name / Company name" required>
<input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
<input class="form-control mb-2" type="password" name="password" placeholder="Password (min 6)" required>
<select class="form-select mb-3" name="role"><option value="seeker">I am a Job Seeker / Student</option><option value="employer">I am an Employer</option></select>
<button class="btn btn-primary w-100">Register</button></form></div></div></div>
<?php require 'footer.php';
