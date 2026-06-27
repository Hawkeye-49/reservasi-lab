<?php
require_once '../includes/config.php';
require_once '../includes/layout_admin.php';
requireAdmin(); $cur=basename(__FILE__);
?>
<?= adminHead('Mata Kuliah') ?><?= adminSidebar($cur) ?>
<div class="main-content"><?= adminTopbar('Mata Kuliah') ?>
<div class="content-area">
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-book-fill me-2 text-primary"></i>Data Mata Kuliah</h6>
    <button class="btn btn-sm btn-primary-custom" onclick="openModal()"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
  </div>
  <div class="p-3 border-bottom"><input type="text" id="search" class="form-control form-control-sm" placeholder="Cari nama / kode..." style="max-width:300px;" oninput="render()"></div>
  <div class="table-responsive">
  <table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>Kode</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Jurusan</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody id="tbody"><tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr></tbody>
  </table></div>
</div></div></div>
<div class="modal fade" id="mForm" tabindex="-1"><div class="modal-dialog"><div class="modal-content rounded-4">
  <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="mTitle">Tambah Mata Kuliah</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" id="fId">
    <div class="row g-3">
      <div class="col-md-5"><label class="form-label">Kode *</label><input type="text" id="fKode" class="form-control" placeholder="IF101"></div>
      <div class="col-md-7"><label class="form-label">Nama *</label><input type="text" id="fNama" class="form-control" placeholder="Algoritma dan Pemrograman"></div>
      <div class="col-md-4"><label class="form-label">SKS *</label><input type="number" id="fSks" class="form-control" value="3" min="1" max="6"></div>
      <div class="col-md-8"><label class="form-label">Status</label>
        <select id="fStatus" class="form-select"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select>
      </div>
    </div>
  </div>
  <div class="modal-footer border-0 pt-0">
    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
    <button class="btn btn-primary-custom" onclick="simpan()"><i class="bi bi-save me-1"></i>Simpan</button>
  </div>
</div></div></div>
<?= adminFoot() ?>
<script>
const API='../api/master.php'; let allData=[];
async function loadData(){ 
  const res=await fetch(`${API}?action=matakuliah`).then(r=>r.json()); 
  allData=res.data||[]; 
  render(); 
}

function render(){
  const q=document.getElementById('search').value.toLowerCase();
  const rows=allData.filter(d=>!q||d.nama.toLowerCase().includes(q)||d.kode.toLowerCase().includes(q));
  document.getElementById('tbody').innerHTML=rows.length?rows.map((d,i)=>`<tr>
    <td class="text-muted">${i+1}</td>
    <td><span class="badge bg-primary bg-opacity-10 text-primary fw-bold">${d.kode}</span></td>
    <td class="fw-medium">${d.nama}</td>
    <td><span class="badge bg-light text-dark">${d.sks} SKS</span></td>
    <td>${d.jurusan}</td>
    <td><span class="badge ${d.status==='aktif'?'bg-success':'bg-secondary'}">${d.status}</span></td>
    <td>
      <button class="btn btn-warning btn-action me-1" onclick='edit(${JSON.stringify(d)})'><i class="bi bi-pencil"></i></button>
      <button class="btn btn-danger btn-action" onclick="hapus(${d.id},'${d.nama.replace(/'/g,"\\'")}')"><i class="bi bi-trash"></i></button>
    </td></tr>`).join(''):'<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data</td></tr>';
}

function openModal(){
  document.getElementById('fId').value='';
  document.getElementById('mTitle').textContent='Tambah Mata Kuliah';
  ['fKode','fNama'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('fSks').value=3;
  document.getElementById('fStatus').value='aktif';
  new bootstrap.Modal(document.getElementById('mForm')).show();
}

function edit(d){
  document.getElementById('fId').value=d.id;
  document.getElementById('mTitle').textContent='Edit Mata Kuliah';
  document.getElementById('fKode').value=d.kode;
  document.getElementById('fNama').value=d.nama;
  document.getElementById('fSks').value=d.sks;
  document.getElementById('fStatus').value=d.status;
  new bootstrap.Modal(document.getElementById('mForm')).show();
}
async function simpan(){
  const fd=new FormData();
  fd.append('id',document.getElementById('fId').value);
  fd.append('kode',document.getElementById('fKode').value);
  fd.append('nama',document.getElementById('fNama').value);
  fd.append('sks',document.getElementById('fSks').value);
  fd.append('status',document.getElementById('fStatus').value);
  const res=await fetch(`${API}?action=save_mk`,{method:'POST',body:fd}).then(r=>r.json());
  showToast(res.success?'success':'danger',res.message);if(res.success){bootstrap.Modal.getInstance(document.getElementById('mForm')).hide();
  loadData();}
}
async function hapus(id,nama){
  if(!confirm(`Hapus "${nama}"?`))return;
  const fd=new FormData();fd.append('id',id);
  const res=await fetch(`${API}?action=del_mk`,{method:'POST',body:fd}).then(r=>r.json());
  showToast(res.success?'success':'danger',res.message);
  if(res.success)loadData();
}
loadData();
</script>
