<?php require 'config.php'; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $r=q("SELECT * FROM users WHERE email=?",[trim($_POST['email'])]);
  if($r && password_verify($_POST['password'],$r[0]['password'])){
    $_SESSION['user']=['id'=>(int)$r[0]['id'],'name'=>$r[0]['name'],'role'=>$r[0]['role']];
    header('Location: '.['seeker'=>'recommend.php','employer'=>'employer.php','admin'=>'admin.php'][$r[0]['role']]); exit;
  } $err='Wrong email or password.';
}
$title='Login'; require 'header.php'; ?>
<div class="row justify-content-center"><div class="col-md-4"><div class="card p-4"><h4>Login</h4>
<?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
<form method="post"><input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
<input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
<button class="btn btn-primary w-100">Login</button></form></div></div></div>
<?php require 'footer.php';
