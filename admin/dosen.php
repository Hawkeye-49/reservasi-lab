<?php
require_once '../includes/config.php';
require_once '../includes/layout_admin.php';
requireAdmin();
$cur=basename(__FILE__);
?>
<?= adminHead('Data Dosen') ?>
<?= adminSidebar($cur) ?>
<div class="main-content">
<?= adminTopbar('Data Dosen') ?>
<div class="content-area">
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge-fill me-2 text-primary"></i>Data Dosen</h6>
    <button class="btn btn-sm btn-primary-custom" onclick="openModal()"><i class="bi bi-plus-lg me-1"></i>Tambah Dosen</button>
  </div>
  <div class="p-3 border-bottom">
    <input type="text" id="search" class="form-control form-control-sm" placeholder="Cari nama / NIDN..." style="max-width:300px;" oninput="loadData()">
  </div>
  <div class="table-responsive">
  <table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>NIDN</th><th>Nama</th><th>Email</th><th>No. HP</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody id="tbody"><tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr></tbody>
  </table>
  </div>
</div>
</div></div>

<!-- modal -->
<div class="modal fade" id="mForm" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="mTitle">Tambah Dosen</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="fId">
        <div class="row g-3">
          <div class="col-12"><label class="form-label">NIDN *</label><input type="text" id="fNidn" class="form-control" placeholder="0101019001"></div>
          <div class="col-12"><label class="form-label">Nama Lengkap *</label><input type="text" id="fNama" class="form-control" placeholder="Dr. Nama, M.Kom"></div>
          <div class="col-12"><label class="form-label">Email *</label><input type="email" id="fEmail" class="form-control" placeholder="nama@univ.ac.id"></div>
          <div class="col-12"><label class="form-label">No. HP</label><input type="text" id="fHp" class="form-control" placeholder="08xxxxxxxxxx"></div>
          <div class="col-md-6"><label class="form-label">Status</label>
            <select id="fStatus" class="form-select"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select>
          </div>
          <div class="col-md-6"><label class="form-label">Password <span id="passHint" class="text-muted" style="font-size:.75rem;">(kosongkan jika tidak diubah)</span></label>
            <input type="password" id="fPass" class="form-control" placeholder="Password baru">
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary-custom" onclick="simpan()"><i class="bi bi-save me-1"></i>Simpan</button>
      </div>
    </div>
  </div>
</div>
<?= adminFoot() ?>
<script>
const API='../api/master.php';
let allData=[];
async function loadData(){
  const res=await fetch(`${API}?action=dosen`).then(r=>r.json());
  allData=res.data||[];
  render();
}
function render(){
  const q=document.getElementById('search').value.toLowerCase();
  const rows=allData.filter(d=>!q||d.nama.toLowerCase().includes(q)||d.nidn.includes(q));
  document.getElementById('tbody').innerHTML=rows.length?rows.map((d,i)=>`
    <tr>
      <td class="text-muted">${i+1}</td>
      <td><span class="badge bg-light text-dark fw-bold">${d.nidn}</span></td>
      <td class="fw-medium">${d.nama}</td>
      <td style="font-size:.82rem;">${d.email}</td>
      <td style="font-size:.82rem;">${d.no_hp||'-'}</td>
      <td><span class="badge ${d.status==='aktif'?'bg-success':'bg-secondary'}">${d.status}</span></td>
      <td>
        <button class="btn btn-warning btn-action me-1" onclick='edit(${JSON.stringify(d)})'><i class="bi bi-pencil"></i></button>
        <button class="btn btn-danger btn-action" onclick="hapus(${d.id},'${d.nama.replace(/'/g,"\\'")}')"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join(''):'<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data</td></tr>';
}
function openModal(reset=true){
  if(reset){document.getElementById('fId').value='';document.getElementById('mTitle').textContent='Tambah Dosen';
    ['fNidn','fNama','fEmail','fHp','fPass'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('fStatus').value='aktif';
    document.getElementById('passHint').textContent='(wajib diisi untuk dosen baru)';}
  new bootstrap.Modal(document.getElementById('mForm')).show();
}
function edit(d){
  document.getElementById('fId').value=d.id;document.getElementById('mTitle').textContent='Edit Dosen';
  document.getElementById('fNidn').value=d.nidn;document.getElementById('fNama').value=d.nama;
  document.getElementById('fEmail').value=d.email;document.getElementById('fHp').value=d.no_hp||'';
  document.getElementById('fStatus').value=d.status;document.getElementById('fPass').value='';
  document.getElementById('passHint').textContent='(kosongkan jika tidak diubah)';
  new bootstrap.Modal(document.getElementById('mForm')).show();
}
async function simpan(){
  const fd=new FormData();
  fd.append('id',document.getElementById('fId').value);
  fd.append('nidn',document.getElementById('fNidn').value);
  fd.append('nama',document.getElementById('fNama').value);
  fd.append('email',document.getElementById('fEmail').value);
  fd.append('no_hp',document.getElementById('fHp').value);
  fd.append('status',document.getElementById('fStatus').value);
  fd.append('password',document.getElementById('fPass').value);
  const res=await fetch(`${API}?action=save_dosen`,{method:'POST',body:fd}).then(r=>r.json());
  showToast(res.success?'success':'danger',res.message);
  if(res.success){bootstrap.Modal.getInstance(document.getElementById('mForm')).hide();loadData();}
}
async function hapus(id,nama){
  if(!confirm(`Hapus dosen "${nama}"?`)) return;
  const fd=new FormData();fd.append('id',id);
  const res=await fetch(`${API}?action=del_dosen`,{method:'POST',body:fd}).then(r=>r.json());
  showToast(res.success?'success':'danger',res.message);
  if(res.success) loadData();
}
loadData();
</script>
