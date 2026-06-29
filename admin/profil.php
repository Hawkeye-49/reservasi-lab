<?php
require_once '../includes/config.php';
require_once '../includes/layout_admin.php';
requireAdmin();
$cur = basename(__FILE__);
$did = (int)$_SESSION['admin_id'];
$db  = getDB();

$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profil') {
        $nama = sanitize($_POST['nama'] ?? '');
        $user = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        if (!$nama) { $err = 'Nama tidak boleh kosong.'; }
        if (!$user) { $err = 'Username tidak boleh kosong.'; }
        if (!$email) { $err = 'Email tidak boleh kosong.'; }
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $err = 'Format email tidak valid.'; }
        if (!$err) {
            $stmt = $db->prepare("SELECT id FROM admin WHERE username=? AND id<>?");
            $stmt->execute([$user, $did]);
            if ($stmt->fetch()) {
                $err = 'Username sudah digunakan oleh admin lain.';
            }
        }
        if (!$err) {
            $stmt = $db->prepare("SELECT id FROM admin WHERE email=? AND id<>?");
            $stmt->execute([$email, $did]);
            if ($stmt->fetch()) {
                $err = 'Email sudah digunakan oleh admin lain.';
            }
        }
        if (!$err) {
            $db->prepare("UPDATE admin SET nama=?, username=?, email=? WHERE id=?")
               ->execute([$nama, $user, $email, $did]);
            $_SESSION['admin_nama'] = $nama;
            $msg = 'Profil berhasil diperbarui!';
        }
    } elseif ($action === 'ganti_password') {
        $lama = $_POST['password_lama'] ?? '';
        $baru = $_POST['password_baru'] ?? '';
        $konfirm = $_POST['password_konfirm'] ?? '';
        $row = $db->prepare("SELECT password FROM admin WHERE id=?");
        $row->execute([$did]);
        $hash = $row->fetchColumn();
        if (!$hash || !password_verify($lama, $hash)) {
            $err = 'Password lama salah.';
        } elseif (strlen($baru) < 8) {
            $err = 'Password baru minimal 8 karakter.';
        } elseif ($baru !== $konfirm) {
            $err = 'Konfirmasi password tidak cocok.';
        } else {
            $db->prepare("UPDATE admin SET password=? WHERE id=?")
               ->execute([password_hash($baru, PASSWORD_BCRYPT), $did]);
            $msg = 'Password berhasil diubah!';
        }
    }
}

$admin = $db->prepare("SELECT * FROM admin WHERE id=?");
$admin->execute([$did]);
$admin = $admin->fetch();
?>
<?= adminHead('Profil Saya') ?>
<?= adminSidebar($cur) ?>
<div class="main-content">
<?= adminTopbar('Profil Saya') ?>
<div class="content-area">

<?php if($msg): ?><div class="alert alert-success rounded-3 mb-3"><i class="bi bi-check-circle-fill me-2"></i><?=htmlspecialchars($msg)?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-danger rounded-3 mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i><?=htmlspecialchars($err)?></div><?php endif; ?>

<div class="row g-4">

  <!-- profil card -->
  <div class="col-lg-4">
    <div class="card text-center p-4">
      <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--accent),#0099cc);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:2.2rem;color:#fff;">
        <i class="bi bi-person-fill"></i>
      </div>
      <h5 class="fw-bold mb-1"><?=htmlspecialchars($admin['nama'])?></h5>
      <div class="text-muted mb-3" style="font-size:.85rem;">
        <div><i class="bi bi-card-text me-1"></i>Username: <?=htmlspecialchars($admin['username'])?></div>
        <div><i class="bi bi-envelope me-1"></i><?=htmlspecialchars($admin['email'])?></div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <!-- edit profil (admin) -->
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Profil</h6></div>
      <div class="card-body p-4">
        <form method="POST">
          <input type="hidden" name="action" value="update_profil">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="nama" class="form-control" value="<?=htmlspecialchars($admin['nama'])?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" value="<?=htmlspecialchars($admin['email'])?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Username <span class="text-danger">*</span></label>
              <input type="text" name="username" class="form-control" value="<?=htmlspecialchars($admin['username'])?>" required>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary-custom"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- ganti password (admin) -->
    <div class="card">
      <div class="card-header"><h6 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-warning"></i>Ganti Password</h6></div>
      <div class="card-body p-4">
        <form method="POST">
          <input type="hidden" name="action" value="ganti_password">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Password Lama <span class="text-danger">*</span></label>
              <input type="password" name="password_lama" class="form-control" placeholder="Masukkan password saat ini" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Password Baru <span class="text-danger">*</span></label>
              <input type="password" name="password_baru" class="form-control" placeholder="Minimal 8 karakter" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
              <input type="password" name="password_konfirm" class="form-control" placeholder="Ulangi password baru" required>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-warning text-white rounded-3 fw-600"><i class="bi bi-key-fill me-2"></i>Ganti Password</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>
</div>
</div>
<?= adminFoot() ?>