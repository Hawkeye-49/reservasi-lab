<?php
require_once '../includes/config.php';
require_once '../includes/layout_admin.php';
requireAdmin();
$cur = basename(__FILE__);
$fs  = $_GET['status']  ?? '';
$ft  = $_GET['tanggal'] ?? '';
$fk  = $_GET['kode']    ?? '';
?>
<?= adminHead('Kelola Reservasi') ?>
<?= adminSidebar($cur) ?>
<div class="main-content">
<?= adminTopbar('Kelola Reservasi') ?>
<div class="content-area">

<!-- filter -->
<div class="card mb-3">
  <div class="card-header">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-sm-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Semua Status</option>
          <?php foreach(['pending','disetujui','ditolak','dibatalkan'] as $s): ?>
          <option value="<?=$s?>" <?=$fs===$s?'selected':''?>><?=ucfirst($s)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control form-control-sm" value="<?=htmlspecialchars($ft)?>" onchange="this.form.submit()">
      </div>
      <div class="col-sm-4">
        <label class="form-label">Cari Kode / Dosen</label>
        <input type="text" name="kode" class="form-control form-control-sm" placeholder="Kode reservasi / nama dosen..." value="<?=htmlspecialchars($fk)?>">
      </div>
      <div class="col-sm-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary-custom flex-fill">Cari</button>
        <a href="reservasi.php" class="btn btn-sm btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-check-fill me-2 text-primary"></i>Daftar Reservasi</h6>
    <span class="badge bg-primary" id="totalBadge">–</span>
  </div>
  <div class="table-responsive">
  <table class="table table-hover mb-0">
    <thead class="table-light">
      <tr><th>Kode</th><th>Dosen</th><th>Ruangan</th><th>Kelas</th><th>Mata Kuliah</th><th>Tanggal</th><th>Jam</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody id="tbody"><tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr></tbody>
  </table>
  </div>
</div>
</div></div>

<!-- modal detail -->
<div class="modal fade" id="mDetail" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Detail Reservasi</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="mDetailBody"><div class="text-center py-4"><div class="spinner-border text-primary"></div></div></div>
    </div>
  </div>
</div>

<?= adminFoot() ?>
<script>
const API='../api/reservasi.php';
const fs='<?=addslashes($fs)?>',ft='<?=addslashes($ft)?>',fk='<?=addslashes($fk)?>';

async function load(){
  let url=`${API}?action=list&limit=100`;
  if(fs) url+=`&status=${fs}`;
  if(ft) url+=`&tanggal=${ft}`;
  const res=await fetch(url).then(r=>r.json());
  document.getElementById('totalBadge').textContent=res.total??0;
  let rows=res.data??[];
  if(fk) rows=rows.filter(r=>r.kode_reservasi.toLowerCase().includes(fk.toLowerCase())||r.dosen.toLowerCase().includes(fk.toLowerCase()));
  const bmap={pending:'bg-warning text-dark',disetujui:'bg-success',ditolak:'bg-danger',dibatalkan:'bg-secondary'};
  document.getElementById('tbody').innerHTML=rows.length?rows.map(r=>`
    <tr>
      <td><span class="badge bg-light text-dark" style="font-size:.7rem;">${r.kode_reservasi}</span></td>
      <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${r.dosen}">${r.dosen}</td>
      <td style="font-size:.82rem;">${r.ruangan}</td>
      <td><span class="badge bg-light text-dark">${r.kelas}</span></td>
      <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem;" title="${r.matakuliah}">${r.matakuliah}</td>
      <td style="font-size:.82rem;white-space:nowrap;">${r.tanggal}</td>
      <td style="font-size:.8rem;white-space:nowrap;">${r.jam}</td>
      <td><span class="badge ${bmap[r.status]||'bg-secondary'}">${r.status}</span></td>
      <td>
        <div class="d-flex gap-1">
          ${r.status==='pending'?`
            <button class="btn btn-success btn-action" onclick="ubahStatus(${r.id},'disetujui')" title="Setujui"><i class="bi bi-check"></i></button>
            <button class="btn btn-danger btn-action" onclick="ubahStatus(${r.id},'ditolak')" title="Tolak"><i class="bi bi-x"></i></button>`:''}
          ${r.status==='disetujui'?`<button class="btn btn-secondary btn-action" onclick="ubahStatus(${r.id},'dibatalkan')" title="Batalkan"><i class="bi bi-slash-circle"></i></button>`:''}
          <button class="btn btn-outline-secondary btn-action" onclick="showDetail(${r.id})" title="Detail"><i class="bi bi-eye"></i></button>
        </div>
      </td>
    </tr>`).join(''):'<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data</td></tr>';
}

async function ubahStatus(id,status){
  let catatan='';
  if(status==='ditolak'){ catatan=prompt('Alasan penolakan (opsional):','')||''; if(catatan===null)return; }
  if(!confirm(`Yakin ingin ${status==='disetujui'?'menyetujui':'mengubah status'} reservasi ini?`)) return;
  const r=await fetch(`${API}?action=update_status`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,status,catatan})}).then(x=>x.json());
  showToast(r.success?'success':'danger',r.message);
  if(r.success) load();
}

async function showDetail(id){
  const modal=new bootstrap.Modal(document.getElementById('mDetail'));
  document.getElementById('mDetailBody').innerHTML='<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
  modal.show();
  const res=await fetch(`${API}?action=detail&id=${id}`).then(r=>r.json());
  if(!res.success){document.getElementById('mDetailBody').innerHTML='<p class="text-danger">Gagal memuat</p>';return;}
  const d=res.data;
  const bmap={pending:'warning',disetujui:'success',ditolak:'danger',dibatalkan:'secondary'};
  document.getElementById('mDetailBody').innerHTML=`
    <div class="row g-3">
      ${[['Kode Reservasi',d.kode_reservasi],['Status',`<span class="badge bg-${bmap[d.status]||'secondary'} fs-6">${d.status}</span>`],
         ['Dosen',d.dosen+' ('+d.nidn+')'],['Kelas',d.kelas],
         ['Mata Kuliah',d.matakuliah+' ('+d.kode_mk+') · '+d.sks+' SKS'],['Ruangan',d.ruangan],
         ['Lokasi',d.lokasi||'-'],['Kapasitas',d.kapasitas+' orang'],
         ['Tanggal',d.tanggal],['Jam',d.jam],
         ['Keterangan',d.keterangan||'-'],['Catatan Admin',d.catatan_admin||'-']]
        .map(([l,v])=>`<div class="col-md-6"><div class="p-3 rounded-3" style="background:#f8fafc;"><small class="text-muted d-block">${l}</small><div class="fw-medium">${v}</div></div></div>`)
        .join('')}
    </div>
    ${d.status==='pending'?`
    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-success flex-fill" onclick="ubahStatus(${d.id},'disetujui');bootstrap.Modal.getInstance(document.getElementById('mDetail')).hide()"><i class="bi bi-check-circle me-1"></i>Setujui</button>
      <button class="btn btn-danger flex-fill" onclick="ubahStatus(${d.id},'ditolak');bootstrap.Modal.getInstance(document.getElementById('mDetail')).hide()"><i class="bi bi-x-circle me-1"></i>Tolak</button>
    </div>`:''}`;
}
load();
</script>
