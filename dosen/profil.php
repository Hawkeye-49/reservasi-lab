<?php
require_once '../includes/config.php';
require_once '../includes/layout_dosen.php';
requireDosen();
$cur = basename(__FILE__);
$did = (int)$_SESSION['dosen_id'];
$db  = getDB();

$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profil') {
        $nama = sanitize($_POST['nama'] ?? '');
        $hp   = sanitize($_POST['no_hp'] ?? '');
        if (!$nama) { $err = 'Nama tidak boleh kosong.'; }
        else {
            $db->prepare("UPDATE dosen SET nama=?, no_hp=? WHERE id=?")
               ->execute([$nama, $hp, $did]);
            $_SESSION['dosen_nama'] = $nama;
            $msg = 'Profil berhasil diperbarui!';
        }
    } elseif ($action === 'ganti_password') {
        $lama = $_POST['password_lama'] ?? '';
        $baru = $_POST['password_baru'] ?? '';
        $konfirm = $_POST['password_konfirm'] ?? '';
        $row = $db->prepare("SELECT password FROM dosen WHERE id=?");
        $row->execute([$did]);
        $hash = $row->fetchColumn();
        if (!password_verify($lama, $hash)) {
            $err = 'Password lama salah.';
        } elseif (strlen($baru) < 6) {
            $err = 'Password baru minimal 6 karakter.';
        } elseif ($baru !== $konfirm) {
            $err = 'Konfirmasi password tidak cocok.';
        } else {
            $db->prepare("UPDATE dosen SET password=? WHERE id=?")
               ->execute([password_hash($baru, PASSWORD_BCRYPT), $did]);
            $msg = 'Password berhasil diubah!';
        }
    }
}

$dosen = $db->prepare("SELECT * FROM dosen WHERE id=?");
$dosen->execute([$did]);
$dosen = $dosen->fetch();

$stat = $db->prepare("SELECT
    (SELECT COUNT(*) FROM reservasi WHERE dosen_id=?) total,
    (SELECT COUNT(*) FROM reservasi WHERE dosen_id=? AND status='disetujui') disetujui,
    (SELECT COUNT(*) FROM reservasi WHERE dosen_id=? AND status='pending') pending");
$stat->execute([$did,$did,$did]);
$stat = $stat->fetch();
?>
<?= dosenHead('Profil Saya') ?>
<?= dosenSidebar($cur) ?>
<div class="main-content">
<?= dosenTopbar('Profil Saya') ?>
<div class="content-area">

<?php if($msg): ?><div class="alert alert-success rounded-3 mb-3"><i class="bi bi-check-circle-fill me-2"></i><?=htmlspecialchars($msg)?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-danger rounded-3 mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i><?=htmlspecialchars($err)?></div><?php endif; ?>

<div class="row g-4">

  <!-- profil card -->
  <div class="col-lg-4">
    <div class="card text-center p-4">
      <div style="width:80px;height:80px;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:2.2rem;color:#fff;">
        <i class="bi bi-person-fill"></i>
      </div>
      <h5 class="fw-bold mb-1"><?=htmlspecialchars($dosen['nama'])?></h5>
      <div class="text-muted mb-3" style="font-size:.85rem;">
        <div><i class="bi bi-card-text me-1"></i>NIDN: <?=htmlspecialchars($dosen['nidn'])?></div>
        <div><i class="bi bi-envelope me-1"></i><?=htmlspecialchars($dosen['email'])?></div>
        <?php if($dosen['no_hp']): ?>
        <div><i class="bi bi-phone me-1"></i><?=htmlspecialchars($dosen['no_hp'])?></div>
        <?php endif; ?>
      </div>
      <div class="d-flex justify-content-around pt-3 border-top">
        <div class="text-center">
          <div class="fw-bold fs-4" style="color:#6366f1;"><?=$stat['total']?></div>
          <div class="text-muted" style="font-size:.75rem;">Total</div>
        </div>
        <div class="text-center">
          <div class="fw-bold fs-4 text-success"><?=$stat['disetujui']?></div>
          <div class="text-muted" style="font-size:.75rem;">Disetujui</div>
        </div>
        <div class="text-center">
          <div class="fw-bold fs-4 text-warning"><?=$stat['pending']?></div>
          <div class="text-muted" style="font-size:.75rem;">Pending</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <!-- edit profil (dosen) -->
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Profil</h6></div>
      <div class="card-body p-4">
        <form method="POST">
          <input type="hidden" name="action" value="update_profil">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">NIDN</label>
              <input type="text" class="form-control" value="<?=htmlspecialchars($dosen['nidn'])?>" readonly style="background:#f0f4f8;cursor:not-allowed;">
            </div>
            <div class="col-12">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="nama" class="form-control" value="<?=htmlspecialchars($dosen['nama'])?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" value="<?=htmlspecialchars($dosen['email'])?>" readonly style="background:#f0f4f8;cursor:not-allowed;">
            </div>
            <div class="col-md-6">
              <label class="form-label">No. HP</label>
              <input type="text" name="no_hp" class="form-control" value="<?=htmlspecialchars($dosen['no_hp']??'')?>">
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary-custom"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- ganti password (dosen) -->
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
              <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter" required>
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
<?= dosenFoot() ?>
