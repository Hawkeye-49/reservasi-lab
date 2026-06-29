<?php
require_once '../includes/config.php';
require_once '../includes/layout_dosen.php';
requireDosen();
$cur=basename(__FILE__);
$db=getDB();
$did=$_SESSION['dosen_id'];

$stat=$db->prepare("SELECT
  (SELECT COUNT(*) FROM reservasi WHERE dosen_id=?) total,
  (SELECT COUNT(*) FROM reservasi WHERE dosen_id=? AND status='pending') pending,
  (SELECT COUNT(*) FROM reservasi WHERE dosen_id=? AND status='disetujui') disetujui,
  (SELECT COUNT(*) FROM reservasi WHERE dosen_id=? AND tanggal>=CURDATE() AND status='disetujui') mendatang");
$stat->execute([$did,$did,$did,$did]);
$s=$stat->fetch();

$riwayat=$db->prepare("SELECT r.id,r.kode_reservasi,r.tanggal,r.status,r.catatan_admin,
    ru.nama ruangan,mk.nama matakuliah,sw.label jam,kl.nama kelas
    FROM reservasi r
    JOIN ruangan ru ON r.ruangan_id=ru.id JOIN matakuliah mk ON r.matakuliah_id=mk.id
    JOIN slot_waktu sw ON r.slot_waktu_id=sw.id JOIN kelas kl ON r.kelas_id=kl.id
    WHERE r.dosen_id=? ORDER BY r.created_at DESC LIMIT 6");
$riwayat->execute([$did]);
$riwayat=$riwayat->fetchAll();

$mendatang=$db->prepare("SELECT r.id,r.kode_reservasi,r.tanggal,r.status,
    ru.nama ruangan,mk.nama matakuliah,sw.label jam,kl.nama kelas
    FROM reservasi r
    JOIN ruangan ru ON r.ruangan_id=ru.id JOIN matakuliah mk ON r.matakuliah_id=mk.id
    JOIN slot_waktu sw ON r.slot_waktu_id=sw.id JOIN kelas kl ON r.kelas_id=kl.id
    WHERE r.dosen_id=? AND r.tanggal>=CURDATE() AND r.status='disetujui'
    ORDER BY r.tanggal,sw.sesi LIMIT 3");
$mendatang->execute([$did]);
$mendatang=$mendatang->fetchAll();
?>
<?= dosenHead('Dashboard') ?><?= dosenSidebar($cur) ?>
<div class="main-content"><?= dosenTopbar('Dashboard') ?>
<div class="content-area">

<!-- selamat datang -->
<div class="p-4 mb-4 rounded-4" style="background:linear-gradient(135deg,#0d1b2a,#2d1b69);color:#fff;">
  <div class="d-flex align-items-center gap-3">
    <div style="width:56px;height:56px;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;"><i class="bi bi-person-circle"></i></div>
    <div>
      <div style="font-size:1.1rem;font-weight:800;">Selamat datang, <?=htmlspecialchars($_SESSION['dosen_nama'])?>!</div>
      <div style="color:rgba(255,255,255,.55);font-size:.82rem;">NIDN: <?=htmlspecialchars($_SESSION['dosen_nidn']??'-')?> · Jurusan Informatika</div>
    </div>
    <a href="reservasi.php" class="btn ms-auto" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border-radius:10px;font-weight:700;white-space:nowrap;">
      <i class="bi bi-plus-lg me-1"></i>Buat Reservasi
    </a>
  </div>
</div>

<!-- status -->
<div class="row g-3 mb-4">
<?php
$cards=[['Total Reservasi',$s['total'],'bi-calendar3','#e8f4fd','#0099cc'],
        ['Pending',$s['pending'],'bi-clock-fill','#fff8e1','#f59e0b'],
        ['Disetujui',$s['disetujui'],'bi-check-circle-fill','#e8f8f5','#10b981'],
        ['Jadwal Mendatang',$s['mendatang'],'bi-calendar-event-fill','#ede9fe','#6366f1']];
foreach($cards as $c):?>
<div class="col-6 col-lg-3"><div class="stat-card">
  <div class="d-flex align-items-start justify-content-between">
    <div><div class="stat-num" style="color:<?=$c[4]?>"><?=$c[1]?></div><div class="stat-label"><?=$c[0]?></div></div>
    <div class="stat-icon" style="background:<?=$c[3]?>;color:<?=$c[4]?>"><i class="bi <?=$c[2]?>"></i></div>
  </div>
</div></div>
<?php endforeach; ?>
</div>

<div class="row g-3">
<!-- jadwal mendatang -->
<div class="col-lg-5">
<div class="card h-100">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-event-fill me-2" style="color:#6366f1;"></i>Jadwal Mendatang</h6>
  </div>
  <div class="card-body p-0">
  <?php if(!$mendatang): ?>
    <div class="text-center py-4 text-muted"><i class="bi bi-calendar-x d-block fs-2 mb-2"></i>Tidak ada jadwal mendatang</div>
  <?php else: ?>
  <?php foreach($mendatang as $r): ?>
  <div class="p-3 border-bottom" style="font-size:.84rem;">
    <div class="fw-bold"><?=htmlspecialchars($r['ruangan'])?></div>
    <div class="text-muted"><?=hariID($r['tanggal'])?>, <?=formatTgl($r['tanggal'])?> · <?=htmlspecialchars($r['jam'])?></div>
    <div style="font-size:.78rem;"><?=htmlspecialchars($r['matakuliah'])?> – Kelas <?=htmlspecialchars($r['kelas'])?></div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
  </div>
  <div class="card-footer bg-white pb-3 text-center border-top-0 pt-0">
    <a href="riwayat.php?status=disetujui" class="btn btn-sm btn-outline-success rounded-3">Lihat Semua</a>
  </div>
</div>
</div>

<!-- riwayat -->
<div class="col-lg-7">
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Terbaru</h6>
    <a href="riwayat.php" class="btn btn-sm btn-outline-primary rounded-3">Semua</a>
  </div>
  <div class="table-responsive">
  <table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Kode</th><th>Ruangan</th><th>Tanggal</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach($riwayat as $r): ?>
    <tr>
      <td><span class="badge bg-light text-dark" style="font-size:.7rem;"><?=htmlspecialchars($r['kode_reservasi'])?></span></td>
      <td style="font-size:.83rem;"><?=htmlspecialchars($r['ruangan'])?></td>
      <td style="font-size:.82rem;white-space:nowrap;"><?=formatTgl($r['tanggal'])?></td>
      <td><?=badgeStatus($r['status'])?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$riwayat): ?><tr><td colspan="5" class="text-center py-3 text-muted">Belum ada reservasi</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
</div>
</div>
</div></div>
<?= dosenFoot() ?>
