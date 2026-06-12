<?php
require_once 'includes/config.php';
$db = getDB();

// Statistik publik
$total = $db->query("SELECT COUNT(*) FROM reservasi WHERE status='disetujui'")->fetchColumn();
$hariIni = $db->query("SELECT COUNT(*) FROM reservasi WHERE tanggal=CURDATE() AND status='disetujui'")->fetchColumn();
$ruanganList = $db->query("SELECT id,kode,nama,kapasitas,lokasi,fasilitas FROM ruangan WHERE status='aktif' ORDER BY nama")->fetchAll();
$slots = $db->query("SELECT id,sesi,label FROM slot_waktu ORDER BY sesi")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Sistem Reservasi Laboratorium Komputer – Universitas Cihuy">
<title>SiResLab – Sistem Reservasi Laboratorium Komputer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
<style>
:root{--navy:#0d1b2a;--navy2:#1b2838;--accent:#00d4ff;--violet:#6366f1;--green:#10b981;--border:#e8ecf0;}
*{font-family:'Inter',sans-serif;box-sizing:border-box;}
body{background:#f0f4f8;color:#1a1a2e;}
a{text-decoration:none;}

/* NAVBAR */
.navbar-main{background:var(--navy);padding:.85rem 0;position:sticky;top:0;z-index:1000;box-shadow:0 2px 20px rgba(0,0,0,.3);}
.brand-logo{width:38px;height:38px;background:linear-gradient(135deg,var(--accent),#0099cc);border-radius:10px;display:flex;align-items:center;justify-content:center;}
.nav-link-custom{color:rgba(255,255,255,.7)!important;font-weight:500;font-size:.85rem;padding:.4rem .85rem!important;border-radius:8px;transition:all .2s;}
.nav-link-custom:hover,.nav-link-custom.active{color:#fff!important;background:rgba(255,255,255,.1);}

/* HERO */
.hero{background:linear-gradient(135deg,var(--navy) 0%,#0f3460 60%,#1a1a4e 100%);padding:5rem 0 4rem;position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 70% 50%,rgba(0,212,255,.07) 0%,transparent 60%);}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(0,212,255,.1);border:1px solid rgba(0,212,255,.25);color:var(--accent);border-radius:50px;padding:.3rem .9rem;font-size:.78rem;font-weight:700;margin-bottom:1.25rem;}
.hero h1{font-size:clamp(2rem,5vw,3rem);font-weight:800;color:#fff;line-height:1.15;letter-spacing:-.5px;}
.hero h1 span{color:var(--accent);}
.hero-desc{color:rgba(255,255,255,.6);font-size:1rem;line-height:1.7;max-width:520px;}
.btn-hero{background:linear-gradient(135deg,var(--accent),#0099cc);color:#fff;border:none;border-radius:12px;padding:.75rem 1.75rem;font-weight:700;font-size:.95rem;transition:all .3s;}
.btn-hero:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,212,255,.35);color:#fff;}
.btn-hero-outline{border:2px solid rgba(255,255,255,.25);color:rgba(255,255,255,.85);background:transparent;border-radius:12px;padding:.75rem 1.75rem;font-weight:600;font-size:.95rem;transition:all .3s;}
.btn-hero-outline:hover{background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.5);}
.btn-hero-violet{background:linear-gradient(135deg,var(--violet),#4f46e5);color:#fff;border:none;border-radius:12px;padding:.75rem 1.75rem;font-weight:700;font-size:.95rem;transition:all .3s;}
.btn-hero-violet:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(99,102,241,.4);color:#fff;}
.hero-stats{display:flex;gap:2rem;margin-top:2rem;}
.hstat-num{font-size:1.6rem;font-weight:800;color:var(--accent);}
.hstat-lbl{font-size:.75rem;color:rgba(255,255,255,.5);}

/* SECTIONS */
.section-eyebrow{font-size:.72rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;color:var(--accent);}
.section-title{font-size:1.75rem;font-weight:800;color:var(--navy);letter-spacing:-.3px;}

/* LAB CARDS */
.lab-card{background:#fff;border-radius:18px;border:1px solid var(--border);padding:1.4rem;transition:all .3s;cursor:pointer;height:100%;}
.lab-card:hover{transform:translateY(-5px);box-shadow:0 12px 40px rgba(0,0,0,.1);border-color:var(--accent);}
.lab-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:.9rem;}

/* JADWAL GRID */
.cell-free{background:rgba(16,185,129,.08);color:#059669;text-align:center;border-radius:8px;padding:.4rem;font-size:.75rem;}
.cell-booked{color:#fff;border-radius:8px;padding:.45rem .6rem;font-size:.75rem;line-height:1.4;}

/* CEK STATUS */
.cek-card{background:#fff;border-radius:20px;border:1px solid var(--border);padding:2rem;}
.res-row{display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);}
.res-row:last-child{border-bottom:none;}
.res-lbl{font-size:.8rem;color:#6c757d;}
.res-val{font-size:.85rem;font-weight:600;color:var(--navy);text-align:right;max-width:55%;}

/* FOOTER */
.footer{background:var(--navy);color:rgba(255,255,255,.5);padding:2.5rem 0 1.5rem;margin-top:4rem;}

/* MISC */
@media(max-width:768px){.hero{padding:3rem 0 2.5rem;}.hero-stats{gap:1.25rem;}}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-main navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
      <div class="brand-logo"><i class="bi bi-pc-display text-white"></i></div>
      <div><div style="color:#fff;font-weight:800;font-size:.95rem;line-height:1;">SiResLab</div>
           <div style="color:rgba(255,255,255,.4);font-size:.62rem;">Sistem Reservasi Lab</div></div>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span style="color:#fff;font-size:1.5rem;"><i class="bi bi-list"></i></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-center gap-1 mt-2 mt-lg-0">
        <li><a class="nav-link-custom nav-link" href="#beranda">Beranda</a></li>
        <li><a class="nav-link-custom nav-link" href="#ruangan">Ruangan</a></li>
        <li><a class="nav-link-custom nav-link" href="#jadwal">Jadwal</a></li>
        <li><a class="nav-link-custom nav-link" href="#cek">Cek Status</a></li>
        <li class="ms-lg-2"><a class="nav-link" href="dosen/login.php">
          <span style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;font-weight:700;font-size:.83rem;padding:.4rem 1rem;border-radius:8px;">
            <i class="bi bi-person-badge me-1"></i>Dosen
          </span></a></li>
        <li><a class="nav-link" href="admin/login.php">
          <span style="background:linear-gradient(135deg,var(--accent),#0099cc);color:#fff;font-weight:700;font-size:.83rem;padding:.4rem 1rem;border-radius:8px;">
            <i class="bi bi-shield-lock me-1"></i>Admin
          </span></a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero" id="beranda">
  <div class="container position-relative">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-badge"><span class="spinner-grow spinner-grow-sm text-info" role="status"></span>Sistem Aktif · Universitas Cihuy</div>
        <h1>Reservasi <span>Lab Komputer</span> Lebih Mudah & Cepat</h1>
        <p class="hero-desc mt-3">Platform digital untuk pemesanan ruang laboratorium komputer Gedung Laboratorium. Cek ketersediaan real-time, booking instan, kelola jadwal perkuliahan.</p>
        <div class="d-flex gap-3 flex-wrap mt-4">
          <a href="dosen/login.php" class="btn btn-hero-violet"><i class="bi bi-person-badge me-2"></i>Portal Dosen</a>
          <a href="#jadwal" class="btn btn-hero-outline"><i class="bi bi-table me-2"></i>Lihat Jadwal</a>
        </div>
        <div class="hero-stats">
          <div><div class="hstat-num"><?= count($ruanganList) ?></div><div class="hstat-lbl">Lab Tersedia</div></div>
          <div><div class="hstat-num"><?= count($slots) ?></div><div class="hstat-lbl">Sesi / Hari</div></div>
          <div><div class="hstat-num"><?= $total ?></div><div class="hstat-lbl">Total Reservasi</div></div>
          <div><div class="hstat-num"><?= $hariIni ?></div><div class="hstat-lbl">Aktif Hari Ini</div></div>
        </div>
      </div>
      <div class="col-lg-6 d-none d-lg-block">
        <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:1.5rem;backdrop-filter:blur(10px);">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span style="width:10px;height:10px;border-radius:50%;background:#10b981;display:inline-block;"></span>
            <span style="color:rgba(255,255,255,.6);font-size:.8rem;font-weight:600;">Jadwal Hari Ini</span>
            <span class="ms-auto" style="color:rgba(255,255,255,.35);font-size:.75rem;" id="heroDate"></span>
          </div>
          <div id="heroSchedule"><div class="text-center py-3" style="color:rgba(255,255,255,.4);font-size:.85rem;"><div class="spinner-border spinner-border-sm me-2"></div>Memuat jadwal...</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- RUANGAN -->
<section class="py-5" id="ruangan">
  <div class="container">
    <div class="text-center mb-4">
      <div class="section-eyebrow">Fasilitas</div>
      <h2 class="section-title">Ruang Laboratorium</h2>
      <p class="text-muted mt-2" style="font-size:.9rem;">Gedung Laboratorium – Universitas Cihuy</p>
    </div>
    <div class="row g-3">
      <?php
      $colors=[['#e8f4fd','#0099cc','bi-laptop'],['#e8f8f5','#059669','bi-pc-display'],
               ['#ede9fe','#6366f1','bi-display'],['#fff3e8','#f59e0b','bi-cpu']];
      foreach($ruanganList as $i=>$r):
        $c=$colors[$i%4];
      ?>
      <div class="col-sm-6 col-lg-3">
        <div class="lab-card">
          <div class="lab-icon" style="background:<?=$c[0]?>;color:<?=$c[1]?>;"><i class="bi <?=$c[2]?>"></i></div>
          <div style="font-weight:700;font-size:.95rem;margin-bottom:3px;"><?=htmlspecialchars($r['nama'])?></div>
          <div style="font-size:.78rem;color:#6c757d;"><i class="bi bi-geo-alt me-1"></i><?=htmlspecialchars($r['lokasi']??'')?></div>
          <div style="font-size:.78rem;color:#888;margin-top:4px;"><i class="bi bi-people me-1"></i>Kapasitas <?=$r['kapasitas']?> orang</div>
          <div style="font-size:.75rem;color:#aaa;margin-top:4px;"><?=htmlspecialchars($r['fasilitas']??'')?></div>
          <span style="display:inline-flex;align-items:center;gap:4px;background:rgba(16,185,129,.1);color:#059669;border-radius:6px;padding:.2rem .6rem;font-size:.72rem;font-weight:700;margin-top:.75rem;">
            <i class="bi bi-circle-fill" style="font-size:.4rem;"></i> Aktif
          </span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- JADWAL -->
<section class="py-5 bg-white" id="jadwal" style="border-top:1px solid var(--border);">
  <div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <div class="section-eyebrow">Ketersediaan</div>
        <h2 class="section-title">Jadwal Ruangan</h2>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <input type="date" id="jadwalTgl" class="form-control form-control-sm" style="border-radius:10px;width:auto;" onchange="loadJadwal()">
        <button class="btn btn-sm btn-outline-primary rounded-3" onclick="loadJadwal()"><i class="bi bi-arrow-clockwise"></i></button>
      </div>
    </div>
    <div class="table-responsive" id="jadwalGrid">
      <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>
    <div class="d-flex gap-3 mt-3 flex-wrap" style="font-size:.78rem;">
      <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#10b981;vertical-align:middle;"></span> Tersedia</span>
      <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:linear-gradient(135deg,#0f3460,#1a4080);vertical-align:middle;"></span> Disetujui</span>
      <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:linear-gradient(135deg,#92400e,#b45309);vertical-align:middle;"></span> Pending</span>
    </div>
  </div>
</section>

<!-- CEK STATUS -->
<section class="py-5" id="cek">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="text-center mb-4">
          <div class="section-eyebrow">Tracking</div>
          <h2 class="section-title">Cek Status Reservasi</h2>
          <p class="text-muted mt-2" style="font-size:.9rem;">Masukkan kode reservasi untuk melihat detail dan status</p>
        </div>
        <div class="cek-card">
          <div class="input-group mb-3">
            <span class="input-group-text" style="border-radius:12px 0 0 12px;background:#f8fafc;border:1.5px solid var(--border);border-right:none;"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="cekInput" class="form-control" placeholder="Contoh: RSV-20240610-ABC123"
                   style="border:1.5px solid var(--border);border-left:none;font-size:.9rem;"
                   onkeydown="if(event.key==='Enter')cekStatus()">
            <button class="btn ms-2 fw-700" onclick="cekStatus()" id="btnCek"
                    style="background:var(--navy);color:#fff;border-radius:12px;padding:0 1.25rem;font-weight:600;white-space:nowrap;">
              Cek Status
            </button>
          </div>
          <div id="cekResult"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- INFO PORTAL -->
<section class="py-5 bg-white" style="border-top:1px solid var(--border);">
  <div class="container">
    <div class="row g-4 justify-content-center">
      <div class="col-md-5">
        <div class="p-4 rounded-4 text-center" style="background:linear-gradient(135deg,#0d1b2a,#2d1b69);">
          <div style="width:64px;height:64px;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#fff;margin:0 auto 1rem;"><i class="bi bi-person-badge-fill"></i></div>
          <h5 style="color:#fff;font-weight:800;">Portal Dosen</h5>
          <p style="color:rgba(255,255,255,.55);font-size:.85rem;margin-bottom:1.25rem;">Login untuk mengajukan reservasi, memantau jadwal, dan melihat riwayat penggunaan lab.</p>
          <a href="dosen/login.php" class="btn w-100 fw-700" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border-radius:12px;padding:.7rem;">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk sebagai Dosen
          </a>
        </div>
      </div>
      <div class="col-md-5">
        <div class="p-4 rounded-4 text-center" style="background:linear-gradient(135deg,#0d1b2a,#0f3460);">
          <div style="width:64px;height:64px;background:linear-gradient(135deg,var(--accent),#0099cc);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#fff;margin:0 auto 1rem;"><i class="bi bi-shield-lock-fill"></i></div>
          <h5 style="color:#fff;font-weight:800;">Panel Admin</h5>
          <p style="color:rgba(255,255,255,.55);font-size:.85rem;margin-bottom:1.25rem;">Kelola reservasi, dosen, ruangan, jadwal, dan lihat laporan penggunaan laboratorium.</p>
          <a href="admin/login.php" class="btn w-100 fw-700" style="background:linear-gradient(135deg,var(--accent),#0099cc);color:#fff;border-radius:12px;padding:.7rem;">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk sebagai Admin
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--accent),#0099cc);border-radius:9px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-pc-display text-white"></i></div>
          <span style="color:#fff;font-weight:800;">SiResLab</span>
        </div>
        <p style="font-size:.82rem;line-height:1.7;max-width:280px;">Sistem Reservasi Laboratorium Komputer untuk Universitas Cihuy – Gedung Laboratorium.</p>
      </div>
      <div class="col-md-3">
        <div style="color:#fff;font-weight:700;margin-bottom:.8rem;font-size:.88rem;">Laboratorium</div>
        <?php foreach($ruanganList as $r): ?>
        <div style="font-size:.8rem;margin-bottom:3px;"><?=htmlspecialchars($r['nama'])?></div>
        <?php endforeach; ?>
      </div>
      <div class="col-md-3">
        <div style="color:#fff;font-weight:700;margin-bottom:.8rem;font-size:.88rem;">Jam Operasional</div>
        <div style="font-size:.8rem;color:var(--accent);font-weight:600;">Senin – Jum'at</div>
        <div style="font-size:.8rem;">07.30 – 18.45 WIB</div>
        <?php foreach($slots as $s): ?>
        <div style="font-size:.75rem;margin-top:3px;">Sesi <?=$s['sesi']?>: <?=$s['label']?></div>
        <?php endforeach; ?>
      </div>
      <div class="col-md-2">
        <div style="color:#fff;font-weight:700;margin-bottom:.8rem;font-size:.88rem;">Akses</div>
        <a href="dosen/login.php" style="font-size:.8rem;color:rgba(255,255,255,.5);display:block;margin-bottom:4px;">Portal Dosen</a>
        <a href="admin/login.php" style="font-size:.8rem;color:rgba(255,255,255,.5);display:block;">Panel Admin</a>
      </div>
    </div>
    <hr style="border-color:rgba(255,255,255,.1);">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <span style="font-size:.78rem;">&copy; <?=date('Y')?> SiResLab · Universitas Cihuy</span>
      
    </div>
  </div>
</footer>

<div class="position-fixed top-0 end-0 p-3" style="z-index:9999" id="toastWrap"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API_R = 'api/reservasi.php';
const HARI  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
const BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

document.addEventListener('DOMContentLoaded', () => {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('jadwalTgl').value = today;
  document.getElementById('heroDate').textContent = fmtTgl(today);
  loadJadwal();
  loadHeroSchedule();
});

async function loadJadwal() {
  const tgl = document.getElementById('jadwalTgl').value;
  if (!tgl) return;
  const d = new Date(tgl+'T00:00:00');
  const wrap = document.getElementById('jadwalGrid');
  wrap.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
  if (d.getDay()===0) {
    wrap.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-calendar-x display-4 d-block mb-2"></i>Tidak ada jadwal pada hari Minggu</div>';
    return;
  }
  const res = await fetch(`${API_R}?action=jadwal&tanggal=${tgl}`).then(r=>r.json());
  const {data=[], ruangan=[], slots=[]} = res;
  const lk = {};
  data.forEach(b=>{ lk[`${b.ruangan_id}_${b.sesi}`]=b; });

  wrap.innerHTML = `
    <div class="fw-bold mb-2" style="font-size:.88rem;">${HARI[d.getDay()]}, ${d.getDate()} ${BULAN[d.getMonth()]} ${d.getFullYear()}</div>
    <table class="table table-bordered" style="min-width:680px;">
      <thead>
        <tr>
          <th style="background:#f8fafc;min-width:110px;font-size:.78rem;">Sesi / Jam</th>
          ${ruangan.map(r=>`<th style="background:#f8fafc;min-width:155px;font-size:.78rem;">${r.nama}</th>`).join('')}
        </tr>
      </thead>
      <tbody>
        ${slots.map(s=>`<tr>
          <td style="white-space:nowrap;font-size:.78rem;"><strong>Sesi ${s.sesi}</strong><br><span style="color:#888;">${s.label}</span></td>
          ${ruangan.map(r=>{
            const b=lk[`${r.id}_${s.sesi}`];
            if(!b) return '<td><div class="cell-free">Tersedia</div></td>';
            const cl=b.status==='pending'?'background:linear-gradient(135deg,#92400e,#b45309)':'background:linear-gradient(135deg,#0f3460,#1a4080)';
            return `<td><div class="cell-booked" style="${cl}">
              <div style="font-weight:700;">${b.dosen}</div>
              <div style="opacity:.8;font-size:.7rem;">${b.kelas} · ${b.matakuliah}</div>
            </div></td>`;
          }).join('')}
        </tr>`).join('')}
      </tbody>
    </table>`;
}

async function loadHeroSchedule() {
  const today = new Date().toISOString().split('T')[0];
  const res = await fetch(`${API_R}?action=jadwal&tanggal=${today}`).then(r=>r.json());
  const el = document.getElementById('heroSchedule');
  if (!res.data || res.data.length===0) {
    el.innerHTML = '<div style="color:rgba(255,255,255,.4);font-size:.8rem;text-align:center;padding:1rem;">Belum ada reservasi hari ini</div>';
    return;
  }
  el.innerHTML = res.data.slice(0,4).map(b=>`
    <div style="background:rgba(255,255,255,.06);border-radius:10px;padding:.6rem .85rem;margin-bottom:.5rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="color:var(--accent);font-size:.73rem;font-weight:700;">${b.jam}</span>
        <span style="background:${b.status==='disetujui'?'rgba(16,185,129,.2)':'rgba(245,158,11,.2)'};color:${b.status==='disetujui'?'#6ee7b7':'#fbbf24'};font-size:.65rem;padding:2px 7px;border-radius:5px;font-weight:700;">${b.status}</span>
      </div>
      <div style="color:rgba(255,255,255,.85);font-size:.82rem;font-weight:700;margin-top:3px;">${b.dosen}</div>
      <div style="color:rgba(255,255,255,.45);font-size:.73rem;">${b.ruangan} · ${b.kelas}</div>
    </div>`).join('');
}

async function cekStatus() {
  const kode = document.getElementById('cekInput').value.trim();
  if (!kode) { toast('warning','Masukkan kode reservasi!'); return; }
  const btn = document.getElementById('btnCek');
  btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>';

  const res = await fetch(`${API_R}?action=detail&kode=${encodeURIComponent(kode)}`).then(r=>r.json());
  btn.disabled=false; btn.textContent='Cek Status';

  const el = document.getElementById('cekResult');
  if (!res.success) {
    el.innerHTML='<div class="alert alert-danger rounded-3 d-flex gap-2"><i class="bi bi-exclamation-triangle-fill mt-1"></i><div>Kode tidak ditemukan. Periksa kembali kode Anda.</div></div>';
    return;
  }
  const d = res.data;
  const sc={pending:'#f59e0b',disetujui:'#10b981',ditolak:'#ef4444',dibatalkan:'#6c757d'};
  const si={pending:'bi-clock',disetujui:'bi-check-circle-fill',ditolak:'bi-x-circle-fill',dibatalkan:'bi-slash-circle'};
  const dt = new Date(d.tanggal+'T00:00:00');
  const tglFmt=`${HARI[dt.getDay()]}, ${dt.getDate()} ${BULAN[dt.getMonth()]} ${dt.getFullYear()}`;
  el.innerHTML=`
    <div class="p-3 rounded-3 mt-2" style="background:#f8fafc;border:1px solid var(--border);">
      <div class="d-flex align-items-center gap-3 mb-3 pb-2" style="border-bottom:1px solid var(--border);">
        <div style="width:48px;height:48px;border-radius:14px;background:${sc[d.status]}22;color:${sc[d.status]};display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">
          <i class="bi ${si[d.status]}"></i>
        </div>
        <div>
          <div class="fw-bold">${d.kode_reservasi}</div>
          <div style="font-size:.78rem;color:${sc[d.status]};font-weight:700;text-transform:uppercase;">${d.status}</div>
        </div>
      </div>
      ${[['Dosen',d.dosen],['Mata Kuliah',`${d.matakuliah} (${d.kode_mk})`],
         ['Kelas',d.kelas],['Laboratorium',d.ruangan],
         ['Tanggal',tglFmt],['Jam',d.jam],
         ...(d.keterangan?[['Keterangan',d.keterangan]]:[]),
         ...(d.catatan_admin?[['Catatan Admin',d.catatan_admin]]:[]),
         ['Diajukan',new Date(d.created_at).toLocaleString('id-ID')]]
        .map(([l,v])=>`<div class="res-row"><span class="res-lbl">${l}</span><span class="res-val">${v}</span></div>`).join('')}
    </div>`;
}

function fmtTgl(s) {
  const d=new Date(s+'T00:00:00');
  return `${d.getDate()} ${BULAN[d.getMonth()]} ${d.getFullYear()}`;
}
function toast(type, msg) {
  const el=document.createElement('div');
  el.className=`toast show align-items-center border-0 mb-2 text-bg-${type}`;
  el.style.cssText='border-radius:10px;min-width:260px;';
  el.innerHTML=`<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button></div>`;
  document.getElementById('toastWrap').appendChild(el);
  setTimeout(()=>el.remove(),4000);
}
</script>
</body>
</html>
