<?php require 'config.php';
// Run ONCE in browser: creates admin account. Then DELETE this file.
if(!q("SELECT id FROM users WHERE role='admin'")){
  x("INSERT INTO users(name,email,password,role) VALUES('Admin','admin@jobfinder.com',?, 'admin')",[password_hash('admin123',PASSWORD_DEFAULT)]);
  echo "Admin created: admin@jobfinder.com / admin123 . Now delete setup.php";
} else echo "Admin already exists. Delete setup.php";
