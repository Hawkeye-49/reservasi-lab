<?php
require_once '../includes/config.php';
if(isAdmin()){ header('Location: dashboard.php'); exit; }
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $u=sanitize($_POST['username']??''); $p=$_POST['password']??'';
    if($u&&$p){
        $db=getDB();
        $stmt=$db->prepare("SELECT * FROM admin WHERE username=?");
        $stmt->execute([$u]);
        $row=$stmt->fetch();
        if($row && password_verify($p,$row['password'])){
            $_SESSION['admin_id']=$row['id'];
            $_SESSION['admin_nama']=$row['nama'];
            header('Location: dashboard.php'); exit;
        } else { $err='Username atau password salah!'; }
    } else { $err='Isi username dan password!'; }
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login Admin – SiResLab</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{font-family:'Inter',sans-serif;}
body{min-height:100vh;background:linear-gradient(135deg,#0d1b2a 0%,#1b2838 50%,#0f3460 100%);display:flex;align-items:center;justify-content:center;}
.login-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);backdrop-filter:blur(20px);border-radius:22px;padding:2.5rem 2rem;width:100%;max-width:400px;box-shadow:0 25px 50px rgba(0,0,0,.4);}
.logo-box{width:68px;height:68px;background:linear-gradient(135deg,#00d4ff,#0099cc);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 1.2rem;box-shadow:0 8px 25px rgba(0,212,255,.35);}
h2{color:#fff;font-weight:800;}
.sub{color:rgba(255,255,255,.45);font-size:.8rem;}
.form-control{background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:.7rem 1rem;}
.form-control:focus{background:rgba(255,255,255,.12);border-color:#00d4ff;color:#fff;box-shadow:0 0 0 3px rgba(0,212,255,.2);}
.form-control::placeholder{color:rgba(255,255,255,.3);}
.form-label{color:rgba(255,255,255,.7);font-size:.83rem;font-weight:600;}
.input-group-text{background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.15);border-right:none;color:rgba(255,255,255,.5);border-radius:12px 0 0 12px;}
.input-group .form-control{border-left:none;border-radius:0 12px 12px 0;}
.btn-togpass{background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.15);border-left:none;color:rgba(255,255,255,.5);border-radius:0 12px 12px 0;}
.btn-login{background:linear-gradient(135deg,#00d4ff,#0099cc);border:none;border-radius:12px;padding:.75rem;font-weight:700;color:#fff;width:100%;transition:all .3s;}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,212,255,.4);color:#fff;}
.hint{color:rgba(255,255,255,.3);font-size:.72rem;}
.back-link{color:rgba(255,255,255,.45);font-size:.82rem;text-decoration:none;}
.back-link:hover{color:#00d4ff;}
</style></head>
<body>
<div class="login-card text-center">
  <div class="logo-box"><i class="bi bi-shield-lock-fill text-white"></i></div>
  <h2 class="mb-1">Panel Admin</h2>
  <p class="sub mb-4">Sistem Reservasi Laboratorium Komputer</p>
  <?php if($err): ?><div class="alert alert-danger rounded-3 py-2 px-3 mb-3" style="font-size:.85rem;"><i class="bi bi-exclamation-triangle-fill me-2"></i><?=htmlspecialchars($err)?></div><?php endif; ?>
  <form method="POST" autocomplete="off">
    <div class="mb-3 text-start">
      <label class="form-label">Username</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-person"></i></span>
        <input type="text" name="username" class="form-control" placeholder="Username admin" required value="<?=htmlspecialchars($_POST['username']??'')?>">
      </div>
    </div>
    <div class="mb-4 text-start">
      <label class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-lock"></i></span>
        <input type="password" name="password" class="form-control" placeholder="Password" required id="pp">
        <button type="button" class="btn-togpass" onclick="const i=document.getElementById('pp');i.type=i.type==='password'?'text':'password';this.innerHTML=i.type==='password'?'<i class=\'bi bi-eye\'></i>':'<i class=\'bi bi-eye-slash\'></i>'">
          <i class="bi bi-eye"></i>
        </button>
      </div>
    </div>
    <button type="submit" class="btn-login mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk sebagai Admin</button>
  </form>
  <a href="register.php" class="back-link d-block mb-2"><i class="bi bi-person-plus me-1"></i>Register Admin</a>
  <a href="../dosen/login.php" class="back-link d-block mb-2"><i class="bi bi-person-badge me-1"></i>Login sebagai Dosen</a>
  <a href="../index.php" class="back-link"><i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
