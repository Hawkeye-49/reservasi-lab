<?php
require_once '../includes/config.php';
require_once '../includes/layout_admin.php';
requireAdmin(); $cur=basename(__FILE__);
?>
<?= adminHead('Laboratorium') ?><?= adminSidebar($cur) ?>
<div class="main-content"><?= adminTopbar('Data Laboratorium') ?>
<div class="content-area">
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-building-fill me-2 text-primary"></i>Data Laboratorium Komputer</h6>
    <button class="btn btn-sm btn-primary-custom" onclick="openModal()"><i class="bi bi-plus-lg me-1"></i>Tambah Lab</button>
  </div>
  <div class="row g-3 p-3" id="labCards"><div class="col-12 text-center py-4"><div class="spinner-border text-primary"></div></div></div>
</div></div></div>
<div class="modal fade" id="mForm" tabindex="-1"><div class="modal-dialog"><div class="modal-content rounded-4">
  <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="mTitle">Tambah Laboratorium</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" id="fId">
    <div class="row g-3">
      <div class="col-md-5"><label class="form-label">Kode *</label><input type="text" id="fKode" class="form-control" placeholder="LAB-303"></div>
      <div class="col-md-7"><label class="form-label">Nama *</label><input type="text" id="fNama" class="form-control" placeholder="Lab. Komputer - 303"></div>
      <div class="col-md-6"><label class="form-label">Kapasitas</label><input type="number" id="fKap" class="form-control" value="30" min="1"></div>
      <div class="col-md-6"><label class="form-label">Status</label>
        <select id="fStatus" class="form-select"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option><option value="maintenance">Maintenance</option></select></div>
      <div class="col-12"><label class="form-label">Lokasi</label><input type="text" id="fLok" class="form-control" placeholder="Gedung Laboratorium Lantai 3"></div>
      <div class="col-12"><label class="form-label">Fasilitas</label><textarea id="fFas" class="form-control" rows="2" placeholder="PC 30 unit, Proyektor, AC, WiFi"></textarea></div>
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
const icons=['bi-laptop','bi-pc-display','bi-display','bi-cpu'];
const colors=[['#e8f4fd','#0099cc'],['#e8f8f5','#059669'],['#ede9fe','#6366f1'],['#fff3e8','#f59e0b']];
async function loadData(){
  const res=await fetch(`${API}?action=ruangan`).then(r=>r.json());
  const data=res.data||[];
  const smap={aktif:'<span class="badge bg-success">Aktif</span>',nonaktif:'<span class="badge bg-secondary">Nonaktif</span>',maintenance:'<span class="badge bg-warning text-dark">Maintenance</span>'};
  document.getElementById('labCards').innerHTML=data.length?data.map((d,i)=>`
    <div class="col-md-6">
      <div class="p-3 rounded-3 border" style="background:#fafbff;">
        <div class="d-flex align-items-start gap-3">
          <div style="width:48px;height:48px;border-radius:12px;background:${colors[i%4][0]};color:${colors[i%4][1]};display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;"><i class="bi ${icons[i%4]}"></i></div>
          <div class="flex-fill">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <div><span class="fw-bold">${d.nama}</span> <span class="badge bg-light text-dark ms-1" style="font-size:.7rem;">${d.kode}</span></div>
              ${smap[d.status]||''}
            </div>
            <div style="font-size:.8rem;color:#6c757d;">${d.lokasi||''}</div>
            <div style="font-size:.78rem;color:#888;margin-top:2px;"><i class="bi bi-people me-1"></i>${d.kapasitas} orang · ${d.fasilitas||''}</div>
          </div>
        </div>
        <div class="d-flex gap-1 mt-2">
          <button class="btn btn-warning btn-sm btn-action flex-fill" onclick='edit(${JSON.stringify(d)})'><i class="bi bi-pencil me-1"></i>Edit</button>
          <button class="btn btn-danger btn-sm btn-action" onclick="hapus(${d.id},'${d.nama.replace(/'/g,"\\'")}')"><i class="bi bi-trash me-1"></i>Hapus</button>
        </div>
      </div>
    </div>`).join(''):'<div class="col-12 text-center py-4 text-muted">Tidak ada data</div>';
}
function openModal(){document.getElementById('fId').value='';document.getElementById('mTitle').textContent='Tambah Laboratorium';['fKode','fNama','fLok','fFas'].forEach(id=>document.getElementById(id).value='');document.getElementById('fKap').value=30;document.getElementById('fStatus').value='aktif';new bootstrap.Modal(document.getElementById('mForm')).show();}
function edit(d){document.getElementById('fId').value=d.id;document.getElementById('mTitle').textContent='Edit Laboratorium';document.getElementById('fKode').value=d.kode;document.getElementById('fNama').value=d.nama;document.getElementById('fKap').value=d.kapasitas;document.getElementById('fLok').value=d.lokasi||'';document.getElementById('fFas').value=d.fasilitas||'';document.getElementById('fStatus').value=d.status;new bootstrap.Modal(document.getElementById('mForm')).show();}
async function simpan(){const fd=new FormData();['fId','fKode','fNama','fKap','fLok','fFas','fStatus'].forEach(id=>fd.append(id.substring(1).toLowerCase()==='id'?'id':id.substring(1).toLowerCase(),document.getElementById(id).value));const res=await fetch(`${API}?action=save_ruangan`,{method:'POST',body:fd}).then(r=>r.json());showToast(res.success?'success':'danger',res.message);if(res.success){bootstrap.Modal.getInstance(document.getElementById('mForm')).hide();loadData();}}
async function hapus(id,nama){if(!confirm(`Hapus laboratorium "${nama}"?`))return;const fd=new FormData();fd.append('id',id);const res=await fetch(`${API}?action=del_ruangan`,{method:'POST',body:fd}).then(r=>r.json());showToast(res.success?'success':'danger',res.message);if(res.success)loadData();}
loadData();
</script>
