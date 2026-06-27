<?php
require_once '../includes/config.php';
require_once '../includes/layout_dosen.php';
requireDosen();
$cur = basename(__FILE__);
$did = $_SESSION['dosen_id'];
?>
<?= dosenHead('Buat Reservasi') ?>
<?= dosenSidebar($cur) ?>
<div class="main-content">
<?= dosenTopbar('Buat Reservasi Laboratorium') ?>
<div class="content-area">
<div class="row g-4">

  <!-- form -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <!-- indikator step -->
        <div class="d-flex mb-3" id="stepBar">
          <?php foreach(['1. Data Pengajuan','2. Ruangan & Waktu','3. Konfirmasi'] as $i=>$l): ?>
          <div class="flex-fill text-center py-2 px-1" id="stepEl<?=$i+1?>"
               style="font-size:.75rem;font-weight:600;background:<?=$i===0?'#1a1a2e':'#f0f4f8'?>;
                      color:<?=$i===0?'#fff':'#aaa'?>;<?=$i===0?'':'';?>
                      <?=$i===0?'border-radius:8px 0 0 8px;':($i===2?'border-radius:0 8px 8px 0;':'')?>">
            <?=$l?>
          </div>
          <?php endforeach; ?>
        </div>
        <div id="alertArea"></div>
      </div>

      <div class="card-body p-4">

        <!-- step 1 reservasi -->
        <div id="panel1">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Dosen <span class="text-danger">*</span></label>
              <input type="text" class="form-control" value="<?=htmlspecialchars($_SESSION['dosen_nama'])?>" readonly
                     style="background:#f0f4f8;cursor:not-allowed;">
              <input type="hidden" id="fDosenId" value="<?=$did?>">
            </div>
            <div class="col-12">
              <label class="form-label">Jurusan</label>
              <input type="text" class="form-control" value="Informatika" readonly style="background:#f0f4f8;cursor:not-allowed;">
            </div>
            <div class="col-md-8">
              <label class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
              <select class="form-select" id="fMK" required>
                <option value="">-- Pilih Mata Kuliah --</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Kelas <span class="text-danger">*</span></label>
              <select class="form-select" id="fKelas" required>
                <option value="">-- Pilih Kelas --</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Keterangan (Opsional)</label>
              <textarea class="form-control" id="fKet" rows="2" placeholder="Keperluan penggunaan ruangan..."></textarea>
            </div>
            <div class="col-12 mt-2">
              <button type="button" class="btn btn-primary-custom w-100" onclick="goStep(2)">
                Lanjut <i class="bi bi-arrow-right ms-1"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- step 2 reservasi-->
        <div id="panel2" class="d-none">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Laboratorium <span class="text-danger">*</span></label>
              <select class="form-select" id="fRuangan" required onchange="onRuanganChange()">
                <option value="">-- Pilih Lab --</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tanggal <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="fTgl" required onchange="onTglChange()" min="">
            </div>
            <div class="col-12 d-none" id="ruanganInfo"></div>

            <div class="col-12">
              <label class="form-label">Pilih Sesi Waktu <span class="text-danger">*</span></label>
              <div class="row g-2" id="slotGrid">
                <div class="col-12 text-muted" style="font-size:.85rem;">
                  <i class="bi bi-info-circle me-1"></i>Pilih ruangan dan tanggal terlebih dahulu
                </div>
              </div>
            </div>

            <div class="col-12 d-flex gap-2 mt-2">
              <button type="button" class="btn btn-outline-secondary flex-fill rounded-3" onclick="goStep(1)">
                <i class="bi bi-arrow-left me-1"></i>Kembali
              </button>
              <button type="button" class="btn btn-primary-custom flex-fill" onclick="goStep(3)">
                Lanjut <i class="bi bi-arrow-right ms-1"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- step 3 reservasi -->
        <div id="panel3" class="d-none">
          <h6 class="fw-bold mb-3">Konfirmasi Detail Reservasi</h6>
          <div id="konfBox" class="p-3 rounded-3 mb-3" style="background:#f8fafc;border:1px solid #e8ecf0;font-size:.88rem;"></div>
          <div class="alert" style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;font-size:.82rem;color:#92400e;">
            <i class="bi bi-info-circle-fill me-2"></i>Reservasi akan berstatus <strong>Pending</strong> dan memerlukan persetujuan admin.
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary flex-fill rounded-3" onclick="goStep(2)">
              <i class="bi bi-arrow-left me-1"></i>Kembali
            </button>
            <button type="button" class="btn btn-primary-custom flex-fill" id="btnKirim" onclick="kirim()">
              <i class="bi bi-send-fill me-2"></i>Kirim Reservasi
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- sidebar info -->
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0 fw-bold"><i class="bi bi-clock-fill me-2 text-primary"></i>Jadwal Sesi</h6></div>
      <div class="card-body p-3" id="sesiInfo">
        <div class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-check-fill me-2 text-success"></i>Ketentuan</h6></div>
      <div class="card-body p-3">
        <ul class="list-unstyled mb-0" style="font-size:.82rem;color:#555;">
          <li class="mb-2 d-flex gap-2"><i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>Pembatalan minimal 2 jam sebelum sesi</li>
          <li class="mb-2 d-flex gap-2"><i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>Tidak tersedia hari Sabtu/Minggu</li>
          <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>Simpan kode reservasi untuk tracking</li>
        </ul>
      </div>
    </div>
  </div>

</div>
</div>
</div>
<?= dosenFoot() ?>
<script>
const API = '../api/reservasi.php';
const MAPI = '../api/master.php';
let selSlotId = null, selSlotLabel = '';
let step = 1;

// init
(async () => {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('fTgl').min = today;
  document.getElementById('fTgl').value = today;

  const [mk, kl, ru, sl] = await Promise.all([
    fetch(`${MAPI}?action=matakuliah`).then(r=>r.json()),
    fetch(`${MAPI}?action=kelas`).then(r=>r.json()),
    fetch(`${MAPI}?action=ruangan`).then(r=>r.json()),
    fetch(`${MAPI}?action=slot_waktu`).then(r=>r.json()),
  ]);

  fillSelect('fMK', mk.data.filter(d => d.status === 'aktif') || [], d=>`${d.nama} (${d.kode}) – ${d.sks} SKS`, '-- Pilih Mata Kuliah --');
  fillSelect('fKelas', (kl.data || []).filter(d => d.status && d.status.toLowerCase() === 'aktif'), d=>`${d.nama} (Kap. ${d.kapasitas})`, '-- Pilih Kelas --');
  fillSelect('fRuangan', ru.data.filter(d => d.status === 'aktif') || [], d=>`${d.nama} (Kap. ${d.kapasitas})`, '-- Pilih Lab --');

  document.getElementById('sesiInfo').innerHTML = (sl.data||[]).map(s=>`
    <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid #f0f4f8;font-size:.82rem;">
      <span class="text-muted">Sesi ${s.sesi}</span>
      <span class="fw-600">${s.label}</span>
    </div>`).join('');

  onTglChange();
})();

function fillSelect(id, data, labelFn, placeholder) {
  const el = document.getElementById(id);
  el.innerHTML = `<option value="">${placeholder}</option>` +
    data.map(d=>`<option value="${d.id}">${typeof labelFn==='function'?labelFn(d):d[labelFn]}</option>`).join('');
}

function isWeekend(tgl) {
  const day = new Date(tgl + 'T00:00:00').getDay();
  return day === 0 || day === 6;
}

function renderWeekendSlotBlock() {
  document.getElementById('slotGrid').innerHTML = `
    <div class="col-12">
      <div class="d-flex align-items-center justify-content-center gap-2 p-3 rounded-3"
          style="background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;">
        <i class="bi bi-calendar-x fs-5"></i>
        <span>Tidak tersedia hari Sabtu/Minggu</span>
      </div>
    </div>
  `;
}

function onRuanganChange() {
  const rid = document.getElementById('fRuangan').value;
  if (!rid) return;

  const sel = document.getElementById('fRuangan');
  const txt = sel.options[sel.selectedIndex].text;
  document.getElementById('ruanganInfo').classList.remove('d-none');
  document.getElementById('ruanganInfo').innerHTML =
    `<div class="p-2 rounded-3" style="background:#ede9fe;font-size:.82rem;color:#4f46e5;">
       <i class="bi bi-building me-2"></i>${txt}
     </div>`;

  const tgl = document.getElementById('fTgl').value;
  if (tgl && isWeekend(tgl)) {
    showAlert('warning', 'Reservasi tidak tersedia pada hari Sabtu/Minggu.');
    renderWeekendSlotBlock();
    return;
  }

  document.getElementById('alertArea').innerHTML = '';
  loadSlots();
}

function onTglChange() {
  const tgl = document.getElementById('fTgl').value;
  if (!tgl) return;

  if (isWeekend(tgl)) {
    showAlert('warning', 'Reservasi tidak tersedia pada hari Sabtu/Minggu.');
    renderWeekendSlotBlock();
    return;
  }

  document.getElementById('alertArea').innerHTML = '';
  if (document.getElementById('fRuangan').value) loadSlots();
}


async function loadSlots() {
  const rid = document.getElementById('fRuangan').value;
  const tgl = document.getElementById('fTgl').value;
  if (!rid || !tgl) return;
  selSlotId = null;

  const grid = document.getElementById('slotGrid');
  grid.innerHTML = '<div class="col-12 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

  const res = await fetch(`${API}?action=slot_status&ruangan_id=${rid}&tanggal=${tgl}`).then(r=>r.json());
  const slots = res.data || [];

  grid.innerHTML = slots.map(s => {
    const booked = parseInt(s.booked) > 0;
    const expired = parseInt(s.expired) === 1;
    return `<div class="col-sm-6 col-lg-4">
      <div class="slot-btn ${booked?'booked':''}" id="slot_${s.id}"
           onclick="${booked?'':  `pickSlot(${s.id},'${s.label}')`}">
        <div class="fw-bold" style="font-size:.85rem;">Sesi ${s.sesi}</div>
        <div style="font-size:.78rem;">${s.label}</div>
        <div style="font-size:.72rem;margin-top:3px;">
          ${expired?'<span style="color:#6b7280;">Non-aktif (sudah lewat)</span>'
                   :(booked?'<span style="color:#dc2626;">Sudah dipesan</span>':'<span style="color:#059669;">Tersedia</span>')}
        </div>
      </div>
    </div>`;
  }).join('');
}

function pickSlot(id, label) {
  document.querySelectorAll('.slot-btn:not(.booked)').forEach(el => el.classList.remove('selected'));
  document.getElementById(`slot_${id}`)?.classList.add('selected');
  selSlotId = id;
  selSlotLabel = label;
}

// step navigasi
function goStep(next) {
  if (next > 1) {
    const mk = document.getElementById('fMK').value;
    const kl = document.getElementById('fKelas').value;
    if (!mk || !kl) { showAlert('danger','Pilih mata kuliah dan kelas terlebih dahulu!'); return; }
  }
  if (next > 2) {
    const rid = document.getElementById('fRuangan').value;
    const tgl = document.getElementById('fTgl').value;
    if (!rid || !tgl || !selSlotId) { showAlert('danger','Pilih ruangan, tanggal, dan sesi waktu!'); return; }

    if (isWeekend(tgl)) {
      showAlert('warning', 'Reservasi tidak tersedia pada hari Sabtu/Minggu.');
      renderWeekendSlotBlock();
      return;
    }

    const ruTxt = document.getElementById('fRuangan').selectedOptions[0]?.textContent || '';
    const ruCap = parseInt((ruTxt.match(/Kap\.\s*(\d+)/i) || [,''])[1], 10);
    const klTxt = document.getElementById('fKelas').selectedOptions[0]?.textContent || '';
    const klCap = parseInt((klTxt.match(/Kap\.\s*(\d+)/i) || [,''])[1], 10);

    if (!Number.isNaN(ruCap) && !Number.isNaN(klCap) && ruCap < klCap) {
      showAlert('danger', 'Kapasitas ruangan tidak mencukupi untuk kapasitas kelas');
      return;
    }

    buildKonfirmasi();
  }
  step = next;
  document.getElementById('alertArea').innerHTML = '';
  [1,2,3].forEach(i => {
    document.getElementById(`panel${i}`).classList.toggle('d-none', i !== next);
    const el = document.getElementById(`stepEl${i}`);
    el.style.background = i < next ? '#10b981' : i === next ? '#1a1a2e' : '#f0f4f8';
    el.style.color = i <= next ? '#fff' : '#aaa';
  });
}


function buildKonfirmasi() {
  const mkSel = document.getElementById('fMK');
  const klSel = document.getElementById('fKelas');
  const ruSel = document.getElementById('fRuangan');
  const tgl   = document.getElementById('fTgl').value;
  const ket   = document.getElementById('fKet').value;
  const HARI  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  const d = new Date(tgl+'T00:00:00');
  const tglFmt = `${HARI[d.getDay()]}, ${d.getDate()} ${BULAN[d.getMonth()]} ${d.getFullYear()}`;

  const rows = [
    ['Dosen', '<?= addslashes(htmlspecialchars($_SESSION["dosen_nama"])) ?>'],
    ['Jurusan', 'Informatika'],
    ['Mata Kuliah', mkSel.options[mkSel.selectedIndex]?.text || '-'],
    ['Kelas', klSel.options[klSel.selectedIndex]?.text || '-'],
    ['Laboratorium', ruSel.options[ruSel.selectedIndex]?.text || '-'],
    ['Tanggal', tglFmt],
    ['Sesi Waktu', selSlotLabel],
    ...(ket ? [['Keterangan', ket]] : []),
  ];

  document.getElementById('konfBox').innerHTML = rows.map(([l,v]) =>
    `<div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #e8ecf0;">
       <span class="text-muted">${l}</span><span class="fw-600 text-end">${v}</span>
     </div>`).join('');
}

// submit
async function kirim() {
  const btn = document.getElementById('btnKirim');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

  const payload = {
    dosen_id: document.getElementById('fDosenId').value,
    matakuliah_id: document.getElementById('fMK').value,
    kelas_id: document.getElementById('fKelas').value,
    ruangan_id: document.getElementById('fRuangan').value,
    slot_waktu_id: selSlotId,
    tanggal: document.getElementById('fTgl').value,
    keterangan: document.getElementById('fKet').value,
  };

  try {
    const res = await fetch(`${API}?action=buat`, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    }).then(r=>r.json());

    if (res.success) {
      document.getElementById('panel3').innerHTML = `
        <div class="text-center py-3">
          <div style="width:70px;height:70px;background:#e8f8f5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:2rem;color:#10b981;">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <h5 class="fw-bold text-success mb-2">Reservasi Berhasil Diajukan!</h5>
          <p class="text-muted mb-3" style="font-size:.875rem;">Menunggu persetujuan admin. Simpan kode ini:</p>
          <div class="p-3 mb-3 rounded-3" style="background:#f0f4f8;font-size:1.2rem;font-weight:800;letter-spacing:2px;color:#1a1a2e;">${res.kode}</div>
          <div class="d-flex gap-2 justify-content-center">
            <a href="riwayat.php" class="btn btn-outline-primary rounded-3">Lihat Riwayat</a>
            <a href="reservasi.php" class="btn btn-primary-custom rounded-3">Buat Lagi</a>
          </div>
        </div>`;
    } else {
      showAlert('danger', res.message);
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Kirim Reservasi';
    }
  } catch(e) {
    showAlert('danger','Terjadi kesalahan. Coba lagi.');
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Kirim Reservasi';
  }
}

function showAlert(type, msg) {
  const el = document.getElementById('alertArea');
  el.innerHTML = `<div class="alert alert-${type} d-flex gap-2 align-items-center py-2 mb-2 rounded-3" style="font-size:.84rem;">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i><div>${msg}</div></div>`;
  setTimeout(()=>{ if(el) el.innerHTML=''; }, 5000);
}
</script>
