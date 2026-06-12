<?php
require_once '../includes/config.php';
require_once '../includes/layout_dosen.php';
requireDosen();
$cur = basename(__FILE__);
?>
<?= dosenHead('Jadwal Ruangan') ?>
<?= dosenSidebar($cur) ?>
<div class="main-content">
<?= dosenTopbar('Jadwal Ketersediaan Ruangan') ?>
<div class="content-area">

<div class="card mb-3">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Grid Ketersediaan Lab</h6>
    <div class="d-flex gap-2 align-items-center">
      <input type="date" id="tgl" class="form-control form-control-sm" style="width:auto;" onchange="load()">
      <button class="btn btn-sm btn-outline-primary rounded-3" onclick="load()"><i class="bi bi-arrow-clockwise"></i></button>
      <a href="reservasi.php" class="btn btn-sm btn-primary-custom rounded-3"><i class="bi bi-plus-lg me-1"></i>Reservasi</a>
    </div>
  </div>
  <div class="p-3 pb-1 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="fw-bold" id="tglInfo" style="font-size:.9rem;color:#1a1a2e;"></div>
    <div class="d-flex gap-3 flex-wrap" style="font-size:.78rem;">
      <span><span style="display:inline-block;width:11px;height:11px;border-radius:50%;background:#10b981;vertical-align:middle;"></span> Tersedia</span>
      <span><span style="display:inline-block;width:11px;height:11px;border-radius:3px;background:linear-gradient(135deg,#0f3460,#1a4080);vertical-align:middle;"></span> Disetujui</span>
      <span><span style="display:inline-block;width:11px;height:11px;border-radius:3px;background:linear-gradient(135deg,#92400e,#b45309);vertical-align:middle;"></span> Pending</span>
    </div>
  </div>
  <div class="table-responsive px-3 pb-3" id="gridWrap">
    <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
  </div>
</div>

<!-- quick nav seminggu -->
<div class="card">
  <div class="card-header"><h6 class="mb-0 fw-bold"><i class="bi bi-calendar-week me-2 text-primary"></i>Navigasi Cepat – 7 Hari ke Depan</h6></div>
  <div class="card-body">
    <div class="d-flex gap-2 flex-wrap" id="quickNav"></div>
  </div>
</div>

</div>
</div>
<?= dosenFoot() ?>
<script>
const API = '../api/reservasi.php';
const HARI=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
const BULAN=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

document.addEventListener('DOMContentLoaded', () => {
  const today = new Date().toISOString().split('T')[0];
  const tglInput = document.getElementById('tgl');
  
  tglInput.min = today;
  if (!tglInput.value || tglInput.value < today) {
    tglInput.value = today;
  }
  buildQuickNav();
  load();
});

function buildQuickNav() {
  const wrap = document.getElementById('quickNav');
  const base = new Date();
  wrap.innerHTML = '';
  for (let i=0; i<7; i++) {
    const d = new Date(base); d.setDate(base.getDate()+i);
    const iso = d.toISOString().split('T')[0];
    const isSatSun = d.getDay()===0 || d.getDay()===6;
    const btn = document.createElement('button');
    btn.className = 'btn btn-sm rounded-3' + (isSatSun ? ' btn-outline-secondary disabled' : ' btn-outline-primary');
    btn.innerHTML = `<div style="font-size:.7rem;font-weight:700;">${HARI[d.getDay()]}</div><div style="font-size:.85rem;">${d.getDate()} ${BULAN[d.getMonth()].slice(0,3)}</div>`;
    if (!isSatSun) btn.onclick = () => { document.getElementById('tgl').value = iso; load(); };
    wrap.appendChild(btn);
  }
}

async function load() {
  const tgl = document.getElementById('tgl').value;
  if (!tgl) return;
  const d = new Date(tgl+'T00:00:00');
  document.getElementById('tglInfo').textContent = `${HARI[d.getDay()]}, ${d.getDate()} ${BULAN[d.getMonth()]} ${d.getFullYear()}`;

  const gw = document.getElementById('gridWrap');
  gw.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

  if (d.getDay()===0 || d.getDay()===6) {
    gw.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-calendar-x display-5 d-block mb-2"></i>Tidak ada jadwal pada hari Sabtu/Minggu</div>';
    return;
  }

  const res = await fetch(`${API}?action=jadwal&tanggal=${tgl}`).then(r=>r.json());
  const {data=[], ruangan=[], slots=[]} = res;
  const lk = {};
  data.forEach(b => { lk[`${b.ruangan_id}_${b.sesi}`] = b; });

  gw.innerHTML = `<table class="table table-bordered" style="min-width:680px;font-size:.83rem;">
    <thead>
      <tr>
        <th style="min-width:110px;background:#f8fafc;">Sesi / Jam</th>
        ${ruangan.map(r=>`<th style="min-width:155px;background:#f8fafc;">${r.nama}</th>`).join('')}
      </tr>
    </thead>
    <tbody>
      ${slots.map(s=>`
        <tr>
          <td style="white-space:nowrap;background:#fafbff;">
            <span class="fw-bold">Sesi ${s.sesi}</span><br>
            <span style="color:#888;font-size:.75rem;">${s.label}</span>
          </td>
          ${ruangan.map(r=>{
            const b = lk[`${r.id}_${s.sesi}`];
            if (!b) return `<td><div style="background:rgba(16,185,129,.08);color:#059669;text-align:center;border-radius:8px;padding:.4rem;">Tersedia</div></td>`;
            const cl = b.status==='pending'
              ? 'background:linear-gradient(135deg,#92400e,#b45309)'
              : 'background:linear-gradient(135deg,#0f3460,#1a4080)';
            return `<td><div style="${cl};color:#fff;border-radius:8px;padding:.45rem .6rem;">
              <div style="font-weight:700;font-size:.78rem;">${b.dosen}</div>
              <div style="opacity:.8;font-size:.7rem;">${b.kelas} · ${b.matakuliah}</div>
            </div></td>`;
          }).join('')}
        </tr>`).join('')}
    </tbody>
  </table>`;
}
</script>
