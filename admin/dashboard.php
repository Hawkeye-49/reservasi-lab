<?php
require_once '../includes/config.php';
require_once '../includes/layout_admin.php';
requireAdmin();
$cur = basename(__FILE__);
$db  = getDB();
$stat = $db->query("SELECT
  (SELECT COUNT(*) FROM reservasi) total,
  (SELECT COUNT(*) FROM reservasi WHERE status='pending') pending,
  (SELECT COUNT(*) FROM reservasi WHERE status='disetujui') disetujui,
  (SELECT COUNT(*) FROM reservasi WHERE tanggal=CURDATE() AND status='disetujui') hari_ini,
  (SELECT COUNT(*) FROM dosen WHERE status='aktif') dosen_aktif,
  (SELECT COUNT(*) FROM reservasi WHERE status='ditolak') ditolak")->fetch();

$terbaru = $db->query("SELECT r.id,r.kode_reservasi,r.tanggal,r.status,r.created_at,
    d.nama dosen,ru.nama ruangan,mk.nama matakuliah,sw.label jam,kl.nama kelas
    FROM reservasi r
    JOIN dosen d ON r.dosen_id=d.id JOIN ruangan ru ON r.ruangan_id=ru.id
    JOIN matakuliah mk ON r.matakuliah_id=mk.id JOIN slot_waktu sw ON r.slot_waktu_id=sw.id
    JOIN kelas kl ON r.kelas_id=kl.id
    ORDER BY r.created_at DESC LIMIT 8")->fetchAll();

$pending_list = $db->query("SELECT r.id,r.kode_reservasi,r.tanggal,
    d.nama dosen,ru.nama ruangan,mk.nama matakuliah,sw.label jam,kl.nama kelas
    FROM reservasi r
    JOIN dosen d ON r.dosen_id=d.id JOIN ruangan ru ON r.ruangan_id=ru.id
    JOIN matakuliah mk ON r.matakuliah_id=mk.id JOIN slot_waktu sw ON r.slot_waktu_id=sw.id
    JOIN kelas kl ON r.kelas_id=kl.id
    WHERE r.status='pending' ORDER BY r.created_at ASC LIMIT 5")->fetchAll();
?>
<?= adminHead('Dashboard') ?>
<?= adminSidebar($cur) ?>
<div class="main-content">
<?= adminTopbar('Dashboard') ?>
<div class="content-area">

<!-- stat cards -->
<div class="row g-3 mb-4">
<?php
$stats=[
  ['Total Reservasi',$stat['total'],'bi-calendar3','#e8f4fd','#0099cc'],
  ['Menunggu Persetujuan',$stat['pending'],'bi-clock-fill','#fff8e1','#f59e0b'],
  ['Disetujui',$stat['disetujui'],'bi-check-circle-fill','#e8f8f5','#10b981'],
  ['Aktif Hari Ini',$stat['hari_ini'],'bi-activity','#ede9fe','#6366f1'],
  ['Ditolak',$stat['ditolak'],'bi-x-circle-fill','#fee2e2','#ef4444'],
  ['Dosen Aktif',$stat['dosen_aktif'],'bi-person-badge-fill','#f0fdf4','#22c55e'],
];
foreach($stats as $s):?>
<div class="col-6 col-lg-2">
<div class="stat-card">
  <div class="d-flex align-items-start justify-content-between">
    <div><div class="stat-num" style="color:<?=$s[4]?>"><?=$s[1]?></div><div class="stat-label"><?=$s[0]?></div></div>
    <div class="stat-icon" style="background:<?=$s[3]?>;color:<?=$s[4]?>"><i class="bi <?=$s[2]?>"></i></div>
  </div>
</div></div>
<?php endforeach; ?>
</div>

<div class="row g-3">
<!-- pending persetujuan -->
<div class="col-lg-5">
<div class="card h-100">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-fill me-2 text-warning"></i>Perlu Persetujuan</h6>
    <span class="badge bg-warning text-dark"><?= count($pending_list) ?></span>
  </div>
  <div class="card-body p-0">
  <?php if(!$pending_list): ?>
    <div class="text-center py-4 text-muted"><i class="bi bi-check-all d-block fs-2 mb-2"></i>Semua sudah diproses</div>
  <?php else: ?>
  <?php foreach($pending_list as $r): ?>
  <div class="p-3 border-bottom" style="font-size:.83rem;">
    <div class="d-flex justify-content-between align-items-start mb-1">
      <span class="fw-bold"><?=htmlspecialchars($r['dosen'])?></span>
      <span class="badge bg-light text-dark" style="font-size:.7rem;"><?=htmlspecialchars($r['kode_reservasi'])?></span>
    </div>
    <div class="text-muted mb-2"><?=htmlspecialchars($r['ruangan'])?> · <?=htmlspecialchars($r['jam'])?> · <?=formatTgl($r['tanggal'])?></div>
    <div class="d-flex gap-1">
      <button class="btn btn-success btn-sm btn-action flex-fill" onclick="setuju(<?=$r['id']?>)"><i class="bi bi-check me-1"></i>Setujui</button>
      <button class="btn btn-danger btn-sm btn-action flex-fill" onclick="tolak(<?=$r['id']?>)"><i class="bi bi-x me-1"></i>Tolak</button>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
  </div>
  <div class="card-footer bg-white border-top-0 pt-0 pb-3 text-center">
    <a href="reservasi.php?status=pending" class="btn btn-sm btn-outline-warning rounded-3">Lihat Semua Pending</a>
  </div>
</div>
</div>

<!-- reservasi terbaru -->
<div class="col-lg-7">
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>Reservasi Terbaru</h6>
    <a href="reservasi.php" class="btn btn-sm btn-outline-primary rounded-3">Semua</a>
  </div>
  <div class="table-responsive">
  <table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Kode</th><th>Dosen</th><th>Ruangan</th><th>Tanggal</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($terbaru as $r): ?>
    <tr>
      <td><span class="badge bg-light text-dark" style="font-size:.7rem;"><?=htmlspecialchars($r['kode_reservasi'])?></span></td>
      <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($r['dosen'])?></td>
      <td style="font-size:.8rem;"><?=htmlspecialchars($r['ruangan'])?></td>
      <td style="font-size:.8rem;white-space:nowrap;"><?=formatTgl($r['tanggal'])?></td>
      <td><?=badgeStatus($r['status'])?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
</div>
</div>
</div>
</div>
<?= adminFoot() ?>
<script>
const API='../api/reservasi.php';
async function setuju(id){
  if(!confirm('Setujui reservasi ini?')) return;
  const r=await fetch(`${API}?action=update_status`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,status:'disetujui'})}).then(x=>x.json());
  showToast(r.success?'success':'danger',r.message);
  if(r.success) setTimeout(()=>location.reload(),900);
}
async function tolak(id){
  const cat=prompt('Alasan penolakan (opsional):','');
  if(cat===null) return;
  const r=await fetch(`${API}?action=update_status`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,status:'ditolak',catatan:cat})}).then(x=>x.json());
  showToast(r.success?'success':'danger',r.message);
  if(r.success) setTimeout(()=>location.reload(),900);
}
</script>
