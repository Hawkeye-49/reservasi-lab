<?php
require_once '../includes/config.php';
require_once '../includes/layout_admin.php';
requireAdmin(); $cur=basename(__FILE__);
?>
<?= adminHead('Data Kelas') ?><?= adminSidebar($cur) ?>
<div class="main-content"><?= adminTopbar('Data Kelas') ?>
<div class="content-area">
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>Data Kelas – Informatika</h6>
    <button class="btn btn-sm btn-primary-custom" onclick="openModal()"><i class="bi bi-plus-lg me-1"></i>Tambah Kelas</button>
  </div>
  <div class="table-responsive">
  <table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>Nama Kelas</th><th>Jurusan</th><th>Semester</th><th>Tahun Ajaran</th><th>Kapasitas</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody id="tbody"><tr><td colspan="8" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr></tbody>
  </table></div>
</div></div></div>
<div class="modal fade" id="mForm" tabindex="-1"><div class="modal-dialog"><div class="modal-content rounded-4">
  <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="mTitle">Tambah Kelas</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" id="fId">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Nama Kelas *</label><input type="text" id="fNama" class="form-control" placeholder="INF-A-24"></div>
      <div class="col-md-6"><label class="form-label">Semester *</label><input type="number" id="fSem" class="form-control" min="1" max="8" value="1"></div>
      <div class="col-md-6"><label class="form-label">Tahun Ajaran*</label><input type="text" id="fTA" class="form-control" placeholder="2024/2025"></div>
      <div class="col-md-6"><label class="form-label">Kapasitas*</label><input type="number" id="fKap" class="form-control" value="30" min="1"></div>
      <div class="col-12"><label class="form-label">Status</label>
        <select id="fStatus" class="form-select"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></div>
    </div>
  </div>
  <div class="modal-footer border-0 pt-0">
    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
    <button class="btn btn-primary-custom" onclick="simpan()"><i class="bi bi-save me-1"></i>Simpan</button>
  </div>
</div></div></div>
<?= adminFoot() ?>
<script>
const API='../api/master.php';
async function loadData(){
  const res=await fetch(`${API}?action=kelas`).then(r=>r.json());
  document.getElementById('tbody').innerHTML=(res.data||[]).length?(res.data||[]).map((d,i)=>`<tr>
    <td class="text-muted">${i+1}</td>
    <td><span class="badge bg-primary fs-8">${d.nama}</span></td>
    <td>${d.jurusan}</td>
    <td>Semester ${d.semester}</td>
    <td>${d.tahun_ajaran||'-'}</td>
    <td>${d.kapasitas} orang</td>
    <td><span class="badge ${d.status==='aktif'?'bg-success':'bg-secondary'}">${d.status}</span></td>
    <td>
      <button class="btn btn-warning btn-action me-1" onclick='edit(${JSON.stringify(d)})'><i class="bi bi-pencil"></i></button>
      <button class="btn btn-danger btn-action" onclick="hapus(${d.id},'${d.nama}')"><i class="bi bi-trash"></i></button>
    </td></tr>`).join(''):'<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data</td></tr>';
}
function openModal(){
  document.getElementById('fId').value='';
  document.getElementById('mTitle').textContent='Tambah Kelas';
  ['fNama','fTA'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('fSem').value=1;
  document.getElementById('fKap').value=30;
  document.getElementById('fStatus').value='aktif';
  new bootstrap.Modal(document.getElementById('mForm')).show();
}
function edit(d){
  document.getElementById('fId').value=d.id;
  document.getElementById('mTitle').textContent='Edit Kelas';
  document.getElementById('fNama').value=d.nama;
  document.getElementById('fSem').value=d.semester;
  document.getElementById('fTA').value=d.tahun_ajaran||'';
  document.getElementById('fKap').value=d.kapasitas;
  document.getElementById('fStatus').value=d.status;
  new bootstrap.Modal(document.getElementById('mForm')).show();
}
async function simpan(){
  const fd=new FormData();
  fd.append('id',document.getElementById('fId').value);
  fd.append('nama',document.getElementById('fNama').value);
  fd.append('semester',document.getElementById('fSem').value);
  fd.append('tahun_ajaran',document.getElementById('fTA').value);
  fd.append('kapasitas',document.getElementById('fKap').value);
  fd.append('status',document.getElementById('fStatus').value);
  const res=await fetch(`${API}?action=save_kelas`,{method:'POST',body:fd}).then(r=>r.json());
  showToast(res.success?'success':'danger',res.message);
  if(res.success){bootstrap.Modal.getInstance(document.getElementById('mForm')).hide();
  loadData();}
}
async function hapus(id,nama){
  if(!confirm(`Hapus kelas "${nama}"?`))return;
  const fd=new FormData();
  fd.append('id',id);
  const res=await fetch(`${API}?action=del_kelas`,{method:'POST',body:fd}).then(r=>r.json());
  showToast(res.success?'success':'danger',res.message);
  if(res.success)loadData();
}
loadData();
</script>
