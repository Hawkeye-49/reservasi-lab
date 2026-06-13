<?php
require_once '../includes/config.php';
require_once '../includes/layout_admin.php';
requireAdmin(); $cur=basename(__FILE__);
$dari = $_GET['dari'] ?? date('Y-m-01');
$ke   = $_GET['ke']   ?? date('Y-m-t');
?>
<?= adminHead('Laporan Penggunaan') ?><?= adminSidebar($cur) ?>
<div class="main-content"><?= adminTopbar('Laporan Penggunaan Lab') ?>
<div class="content-area">

<!-- filter -->
<div class="card mb-3">
  <div class="card-header">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-sm-4">
        <label class="form-label">Dari Tanggal</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?=htmlspecialchars($dari)?>">
      </div>
      <div class="col-sm-4">
        <label class="form-label">Sampai Tanggal</label>
        <input type="date" name="ke" class="form-control form-control-sm" value="<?=htmlspecialchars($ke)?>">
      </div>
      <div class="col-sm-4 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary-custom flex-fill"><i class="bi bi-filter me-1"></i>Filter</button>
        <button type="button" class="btn btn-sm btn-outline-success" onclick="cetakLaporan()"><i class="bi bi-printer me-1"></i>Cetak</button>
      </div>
    </form>
  </div>
</div>

<!-- ringkasan -->
<div class="row g-3 mb-3" id="statCards"></div>

<!-- chart ruangan -->
<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="card p-3">
      <h6 class="fw-bold mb-3"><i class="bi bi-building me-2 text-primary"></i>Penggunaan per Ruangan</h6>
      <div id="chartRuangan"></div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card p-3">
      <h6 class="fw-bold mb-3"><i class="bi bi-person-badge me-2 text-primary"></i>Top 10 Dosen Pengguna</h6>
      <div id="chartDosen"></div>
    </div>
  </div>
</div>

<!-- tabel detail -->
<div class="card" id="printArea">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Detail Reservasi: <?=formatTgl($dari)?> – <?=formatTgl($ke)?></h6>
    <span class="badge bg-primary" id="totalBadge">–</span>
  </div>
  <div class="table-responsive">
  <table class="table table-hover mb-0" id="tblLaporan">
    <thead class="table-light">
      <tr><th>#</th><th>Kode</th><th>Dosen</th><th>Ruangan</th><th>Kelas</th><th>Mata Kuliah</th><th>Tanggal</th><th>Jam</th><th>Status</th></tr>
    </thead>
    <tbody id="tbody"><tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr></tbody>
  </table>
  </div>
</div>
</div></div>
<?= adminFoot() ?>
<script>
const API='../api/reservasi.php';
const dari='<?=addslashes($dari)?>', ke='<?=addslashes($ke)?>';

async function load(){
  const res=await fetch(`${API}?action=laporan&dari=${dari}&ke=${ke}`).then(r=>r.json());
  const data=res.data||[];
  const pr=res.per_ruangan||[];
  const pd=res.per_dosen||[];

  // stat cards
  const setuju=data.filter(d=>d.status==='disetujui').length;
  const pending=data.filter(d=>d.status==='pending').length;
  const ditolak=data.filter(d=>d.status==='ditolak').length;
  const stats=[['Total Reservasi',data.length,'bi-calendar3','#e8f4fd','#0099cc'],
               ['Disetujui',setuju,'bi-check-circle-fill','#e8f8f5','#10b981'],
               ['Pending',pending,'bi-clock-fill','#fff8e1','#f59e0b'],
               ['Ditolak',ditolak,'bi-x-circle-fill','#fee2e2','#ef4444']];
  document.getElementById('statCards').innerHTML=stats.map(s=>`
    <div class="col-6 col-lg-3"><div class="stat-card">
      <div class="d-flex align-items-start justify-content-between">
        <div><div class="stat-num" style="color:${s[4]}">${s[1]}</div><div class="stat-label">${s[0]}</div></div>
        <div class="stat-icon" style="background:${s[3]};color:${s[4]}"><i class="bi ${s[2]}"></i></div>
      </div></div></div>`).join('');

  // chart ruangan (bar manual)
  const maxR=pr.reduce((m,x)=>Math.max(m,+x.n),0)||1;
  document.getElementById('chartRuangan').innerHTML=pr.map(x=>`
    <div class="mb-2">
      <div class="d-flex justify-content-between mb-1" style="font-size:.82rem;"><span class="fw-medium">${x.nama}</span><span>${x.n}</span></div>
      <div style="background:#f0f4f8;border-radius:6px;height:10px;"><div style="height:10px;border-radius:6px;background:linear-gradient(135deg,#00d4ff,#0099cc);width:${(x.n/maxR*100).toFixed(1)}%;"></div></div>
    </div>`).join('')||'<div class="text-muted text-center py-3">Tidak ada data</div>';

  const maxD=pd.reduce((m,x)=>Math.max(m,+x.n),0)||1;
  document.getElementById('chartDosen').innerHTML=pd.map(x=>`
    <div class="mb-2">
      <div class="d-flex justify-content-between mb-1" style="font-size:.82rem;"><span class="fw-medium" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${x.nama}</span><span>${x.n}</span></div>
      <div style="background:#f0f4f8;border-radius:6px;height:10px;"><div style="height:10px;border-radius:6px;background:linear-gradient(135deg,#6366f1,#4f46e5);width:${(x.n/maxD*100).toFixed(1)}%;"></div></div>
    </div>`).join('')||'<div class="text-muted text-center py-3">Tidak ada data</div>';

  // table
  document.getElementById('totalBadge').textContent=data.length;
  const bmap={pending:'bg-warning text-dark',disetujui:'bg-success',ditolak:'bg-danger',dibatalkan:'bg-secondary'};
  document.getElementById('tbody').innerHTML=data.length?data.map((d,i)=>`
    <tr>
      <td class="text-muted">${i+1}</td>
      <td><span class="badge bg-light text-dark" style="font-size:.7rem;">${d.kode_reservasi}</span></td>
      <td style="font-size:.83rem;">${d.dosen}</td>
      <td style="font-size:.83rem;">${d.ruangan}</td>
      <td><span class="badge bg-light text-dark">${d.kelas}</span></td>
      <td style="font-size:.82rem;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${d.matakuliah}">${d.matakuliah}</td>
      <td style="font-size:.82rem;white-space:nowrap;">${d.tanggal}</td>
      <td style="font-size:.8rem;white-space:nowrap;">${d.jam}</td>
      <td><span class="badge ${bmap[d.status]||'bg-secondary'}">${d.status}</span></td>
    </tr>`).join(''):'<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data untuk periode ini</td></tr>';
}

function cetakLaporan(){
  const tbl=document.getElementById('tblLaporan').outerHTML;
  const w=window.open('','_blank');
  w.document.write(`<!DOCTYPE html><html><head><title>Laporan Reservasi Lab Komputer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{padding:20px;}@media print{.no-print{display:none}}</style></head>
    <body><h4>Laporan Penggunaan Lab Komputer</h4><p>${dari} s/d ${ke}</p>${tbl}
    <script>window.onload=()=>window.print()<\/script></body></html>`);
}
load();
</script>
