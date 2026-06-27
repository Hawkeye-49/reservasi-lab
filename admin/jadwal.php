<?php
require_once '../includes/config.php';
require_once '../includes/layout_admin.php';
requireAdmin(); $cur=basename(__FILE__);
?>
<?= adminHead('Jadwal Ruangan') ?><?= adminSidebar($cur) ?>
<div class="main-content"><?= adminTopbar('Jadwal Ruangan') ?>
<div class="content-area">
<div class="card">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Ketersediaan Laboratorium</h6>
    <div class="d-flex gap-2 align-items-center">
      <input type="date" id="tgl" class="form-control form-control-sm" style="width:auto;" onchange="load()">
      <button class="btn btn-sm btn-outline-primary rounded-3" onclick="load()"><i class="bi bi-arrow-clockwise"></i></button>
    </div>
  </div>
  <div class="p-3 pb-1" id="tglInfo" style="font-weight:700;font-size:.9rem;color:#1a1a2e;"></div>
  <div class="table-responsive p-3 pt-1" id="gridWrap"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
  <div class="px-3 pb-3 d-flex gap-3 flex-wrap" style="font-size:.78rem;">
    <span><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#10b981;"></span> Tersedia</span>
    <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:linear-gradient(135deg,#0f3460,#1a4080);"></span> Disetujui</span>
    <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:linear-gradient(135deg,#92400e,#b45309);"></span> Pending</span>
  </div>
</div></div></div>
<?= adminFoot() ?>
<script>
const API='../api/reservasi.php';
const HARI=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
const BULAN=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
document.addEventListener('DOMContentLoaded',()=>{
  const t=new Date().toISOString().split('T')[0];
  document.getElementById('tgl').value=t; load();
});

async function load(){
  const tgl=document.getElementById('tgl').value;
  const d=new Date(tgl+'T00:00:00');
  document.getElementById('tglInfo').textContent=`${HARI[d.getDay()]}, ${d.getDate()} ${BULAN[d.getMonth()]} ${d.getFullYear()}`;
  const gw=document.getElementById('gridWrap');
  gw.innerHTML='<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
  if(d.getDay() === 0 || d.getDay() === 6){
    gw.innerHTML='<div class="text-center py-5 text-muted"><i class="bi bi-calendar-x display-5 d-block mb-2"></i>Tidak ada jadwal pada hari Sabtu/Minggu</div>';
    return;
  }
  const res=await fetch(`${API}?action=jadwal&tanggal=${tgl}`).then(r=>r.json());
  const {data=[],ruangan=[],slots=[]}=res;
  const lk={};data.forEach(b=>{lk[`${b.ruangan_id}_${b.sesi}`]=b;});
  gw.innerHTML=`<table class="table table-bordered" style="min-width:700px;">
    <thead><tr><th style="min-width:120px;">Sesi / Jam</th>${ruangan.map(r=>`<th style="min-width:160px;">${r.nama}</th>`).join('')}</tr></thead>
    <tbody>${slots.map(s=>`<tr>
      <td style="font-size:.78rem;white-space:nowrap;"><strong>Sesi ${s.sesi}</strong><br><span style="color:#888;">${s.label}</span></td>
      ${ruangan.map(r=>{const b=lk[`${r.id}_${s.sesi}`];
        if(!b) return '<td><div style="background:rgba(16,185,129,.08);color:#059669;text-align:center;border-radius:6px;padding:.35rem;font-size:.75rem;">Tersedia</div></td>';
        const cl=b.status==='pending'?'background:linear-gradient(135deg,#92400e,#b45309)':'background:linear-gradient(135deg,#0f3460,#1a4080)';
        return `<td><div style="${cl};color:#fff;border-radius:8px;padding:.45rem .6rem;font-size:.75rem;"><div style="font-weight:600;">${b.dosen}</div><div style="opacity:.8;font-size:.7rem;">${b.kelas} · ${b.matakuliah}</div></div></td>`;
      }).join('')}</tr>`).join('')}
    </tbody></table>`;
}
</script>
