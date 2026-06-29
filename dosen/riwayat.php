<?php
require_once '../includes/config.php';
require_once '../includes/layout_dosen.php';
requireDosen();
$cur = basename(__FILE__);
$did = (int)$_SESSION['dosen_id'];
$fs  = $_GET['status'] ?? '';
?>
<?= dosenHead('Riwayat Reservasi') ?>
<?= dosenSidebar($cur) ?>
<div class="main-content">
<?= dosenTopbar('Riwayat Reservasi') ?>
<div class="content-area">

<!-- filter -->
<div class="card mb-3">
  <div class="card-header">
    <div class="d-flex flex-wrap gap-2 align-items-end">
      <div>
        <label class="form-label mb-1">Filter Status</label>
        <div class="d-flex gap-1 flex-wrap" id="filterBtns">
          <?php foreach([''=> 'Semua','pending'=>'Pending','disetujui'=>'Disetujui','ditolak'=>'Ditolak','dibatalkan'=>'Dibatalkan'] as $val=>$lbl): ?>
          <button onclick="setFilter('<?=$val?>')"
                  class="btn btn-sm filter-btn <?=$fs===$val?'active':''?>"
                  data-val="<?=$val?>"
                  style="border-radius:8px;">
            <?=$lbl?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="ms-auto">
        <label class="form-label mb-1">Cari</label>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Masukkan keyword..." style="width:220px;" oninput="render()">
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Reservasi Saya</h6>
    <span class="badge bg-primary" id="badge">–</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th><th>Kode</th><th>Laboratorium</th><th>Kelas</th>
          <th>Mata Kuliah</th><th>Tanggal</th><th>Jam</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tbody">
        <tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
      </tbody>
    </table>
  </div>
</div>

</div>
</div>

<!-- modal detail -->
<div class="modal fade" id="mDetail" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Detail Reservasi</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="mDetailBody">
        <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
      </div>
    </div>
  </div>
</div>

<?= dosenFoot() ?>
<style>
.filter-btn { background: #f0f4f8; border: 1px solid #e8ecf0; color: #555; }
.filter-btn.active { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
.filter-btn:hover:not(.active) { background: #e8ecf0; }
</style>
<script>
const API  = '../api/reservasi.php';
const DID  = <?= $did ?>;
let allData = [];
let activeFilter = '<?= addslashes($fs) ?>';

async function loadData() {
  let url = `${API}?action=list&dosen_id=${DID}&limit=200`;
  const res = await fetch(url).then(r=>r.json());
  allData = res.data || [];
  render();
}

function setFilter(val) {
  activeFilter = val;
  document.querySelectorAll('.filter-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.val === val);
  });
  render();
}

function render() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  let rows = allData;
  if (activeFilter) rows = rows.filter(r => r.status === activeFilter);
  if (q) rows = rows.filter(r =>
    r.kode_reservasi.toLowerCase().includes(q) ||
    r.kelas.toLowerCase().includes(q) ||
    r.ruangan.toLowerCase().includes(q) ||
    r.matakuliah.toLowerCase().includes(q)
  );

  document.getElementById('badge').textContent = rows.length;
  const bmap = {
    pending: 'bg-warning text-dark',
    disetujui: 'bg-success',
    ditolak: 'bg-danger',
    dibatalkan: 'bg-secondary'
  };

  document.getElementById('tbody').innerHTML = rows.length ? rows.map((r, i) => `
    <tr>
      <td class="text-muted">${i+1}</td>
      <td><span class="badge bg-light text-dark" style="font-size:.7rem;">${r.kode_reservasi}</span></td>
      <td style="font-size:.83rem;">${r.ruangan}</td>
      <td><span class="badge bg-light text-dark">${r.kelas}</span></td>
      <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem;" title="${r.matakuliah}">${r.matakuliah}</td>
      <td style="font-size:.82rem;white-space:nowrap;">${r.tanggal}</td>
      <td style="font-size:.8rem;white-space:nowrap;">${r.jam}</td>
      <td><span class="badge ${bmap[r.status]||'bg-secondary'}">${r.status}</span></td>
      <td>
        <div class="d-flex gap-1">
          <button class="btn btn-outline-secondary btn-action" onclick="lihatDetail(${r.id})" title="Detail"><i class="bi bi-eye"></i></button>
          ${['pending','disetujui'].includes(r.status) && r.bisa_batalkan === true
            ? `<button class="btn btn-outline-danger btn-action" onclick="batalkan(${r.id},'${r.kode_reservasi}')" title="Batalkan"><i class="bi bi-x-circle"></i></button>`
            : ''}
        </div>
      </td>
    </tr>
  `).join('') : '<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data</td></tr>';
}

async function lihatDetail(id) {
  const modal = new bootstrap.Modal(document.getElementById('mDetail'));
  document.getElementById('mDetailBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
  modal.show();

  const res = await fetch(`${API}?action=detail&id=${id}`).then(r=>r.json());
  if (!res.success) { document.getElementById('mDetailBody').innerHTML = '<p class="text-danger">Gagal memuat data</p>'; return; }
  const d = res.data;
  const sc = {pending:'warning',disetujui:'success',ditolak:'danger',dibatalkan:'secondary'};
  const HARI=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const BULAN=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  const dt = new Date(d.tanggal+'T00:00:00');

  document.getElementById('mDetailBody').innerHTML = `
    <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-3" style="background:#f8fafc;">
      <div style="font-size:2rem;color:#6366f1;"><i class="bi bi-calendar-check-fill"></i></div>
      <div>
        <div class="fw-bold" style="font-size:1rem;">${d.kode_reservasi}</div>
        <span class="badge bg-${sc[d.status]||'secondary'} mt-1">${d.status.toUpperCase()}</span>
        ${d.catatan_admin ? `<div class="text-muted mt-1" style="font-size:.78rem;"><i class="bi bi-chat-left-text me-1"></i>${d.catatan_admin}</div>` : ''}
      </div>
    </div>
    <div class="row g-2">
      ${[
        ['Dosen', d.dosen],
        ['Jurusan', 'Informatika'],
        ['Mata Kuliah', `${d.matakuliah} (${d.kode_mk}) · ${d.sks} SKS`],
        ['Kelas', d.kelas],
        ['Laboratorium', d.ruangan],
        ['Lokasi', d.lokasi||'-'],
        ['Tanggal', `${HARI[dt.getDay()]}, ${dt.getDate()} ${BULAN[dt.getMonth()]} ${dt.getFullYear()}`],
        ['Sesi Waktu', d.jam],
        ...(d.keterangan ? [['Keterangan', d.keterangan]] : []),
        ['Diajukan', new Date(d.created_at).toLocaleString('id-ID')],
      ].map(([l,v]) => `
        <div class="col-md-6">
          <div class="p-2 rounded-3" style="background:#f8fafc;">
            <small class="text-muted d-block">${l}</small>
            <div class="fw-medium" style="font-size:.88rem;">${v}</div>
          </div>
        </div>
      `).join('')}
    </div>
    ${['pending','disetujui'].includes(d.status) && d.bisa_batalkan === true ? `
    <button class="btn btn-outline-danger w-100 mt-3 rounded-3"
            onclick="batalkan(${d.id},'${d.kode_reservasi}');bootstrap.Modal.getInstance(document.getElementById('mDetail')).hide();">
      <i class="bi bi-x-circle me-2"></i>Batalkan Reservasi Ini
    </button>` : ''}`;
}

async function batalkan(id, kode) {
  if (!confirm(`Batalkan reservasi ${kode}?`)) return;
  const res = await fetch(`${API}?action=batal`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({id, dosen_id: DID})
  }).then(r=>r.json());
  showToast(res.success?'success':'danger', res.message);
  if (res.success) loadData();
}

loadData();
</script>
