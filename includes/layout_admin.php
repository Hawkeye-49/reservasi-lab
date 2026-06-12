<?php
// Shared HTML head + sidebar untuk semua halaman admin
// Usage: require_once '../includes/layout_admin.php'; // lalu echo adminHead($title); echo adminSidebar($current);
function adminHead(string $title): string {
    return '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>'.$title.' – SiResLab Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="../assets/css/admin.css" rel="stylesheet">
</head><body>';
}

function adminTopbar(string $title): string {
    $nama = htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin');
    return '<div class="topbar">
  <div class="d-flex align-items-center gap-2">
    <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="toggleSB()"><i class="bi bi-list fs-5"></i></button>
    <span class="topbar-title">'.$title.'</span>
  </div>
  <div class="d-flex align-items-center gap-3">
    <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-primary rounded-3 d-none d-md-inline-flex align-items-center gap-1">
      <i class="bi bi-globe2"></i> Website</a>
    <div class="text-end d-none d-md-block lh-1">
      <div style="font-size:.8rem;font-weight:700;color:#1a1a2e">'.$nama.'</div>
      <div style="font-size:.7rem;color:#6c757d">Administrator</div>
    </div>
  </div>
</div>';
}

function adminSidebar(string $cur): string {
    $items = [
        ['section'=>'Menu Utama'],
        ['href'=>'dashboard.php','icon'=>'bi-grid-1x2-fill','label'=>'Dashboard'],
        ['href'=>'reservasi.php','icon'=>'bi-calendar-check-fill','label'=>'Kelola Reservasi'],
        ['href'=>'jadwal.php','icon'=>'bi-table','label'=>'Jadwal Ruangan'],
        ['href'=>'laporan.php','icon'=>'bi-bar-chart-fill','label'=>'Laporan Penggunaan'],
        ['section'=>'Master Data'],
        ['href'=>'dosen.php','icon'=>'bi-person-badge-fill','label'=>'Data Dosen'],
        ['href'=>'matakuliah.php','icon'=>'bi-book-fill','label'=>'Mata Kuliah'],
        ['href'=>'kelas.php','icon'=>'bi-people-fill','label'=>'Data Kelas'],
        ['href'=>'ruangan.php','icon'=>'bi-building-fill','label'=>'Laboratorium'],
        ['section'=>'Akun'],
        ['href'=>'logout.php','icon'=>'bi-box-arrow-left','label'=>'Logout','danger'=>true],
    ];
    $html = '<div class="sidebar" id="sidebar">
  <div class="sidebar-brand d-flex align-items-center">
    <div class="logo-icon"><i class="bi bi-pc-display text-white fs-5"></i></div>
    <div class="ms-2">
      <div style="color:#fff;font-weight:800;font-size:.95rem;">SiResLab</div>
      <div style="color:rgba(255,255,255,.4);font-size:.65rem;">Admin Panel</div>
    </div>
  </div>
  <nav class="nav-menu">';
    foreach($items as $it){
        if(isset($it['section'])){
            $html .= '<div class="nav-section-label">'.$it['section'].'</div>';
        } else {
            $active = ($it['href']===$cur) ? 'active' : '';
            $style = isset($it['danger']) ? ' style="color:rgba(255,100,100,.8)"' : '';
            $html .= '<a href="'.$it['href'].'" class="nav-item-link '.$active.'"'.$style.'><i class="bi '.$it['icon'].'"></i>'.$it['label'].'</a>';
        }
    }
    $html .= '</nav></div><div class="sidebar-overlay" id="sOverlay" onclick="toggleSB()"></div>';
    return $html;
}

function adminFoot(): string {
    return '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSB(){
  document.getElementById("sidebar").classList.toggle("show");
  document.getElementById("sOverlay").classList.toggle("show");
}
function showToast(type,msg){
  const c=document.getElementById("toastContainer");
  if(!c)return;
  const el=document.createElement("div");
  el.className="toast show align-items-center border-0 mb-2 text-bg-"+type;
  el.style.cssText="border-radius:10px;min-width:260px;";
  el.innerHTML=`<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest(\'.toast\').remove()"></button></div>`;
  c.appendChild(el);setTimeout(()=>el.remove(),4000);
}
</script>
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index:9999"></div>
</body></html>';
}
