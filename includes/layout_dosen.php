<?php
function dosenHead(string $title): string {
    return '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>'.$title.' – SiResLab Dosen</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="../assets/css/dosen.css" rel="stylesheet">
</head><body>';
}

function dosenTopbar(string $title): string {
    $nama = htmlspecialchars($_SESSION['dosen_nama'] ?? 'Dosen');
    return '<div class="topbar">
  <div class="d-flex align-items-center gap-2">
    <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="toggleSB()"><i class="bi bi-list fs-5"></i></button>
    <span class="topbar-title">'.$title.'</span>
  </div>
  <div class="d-flex align-items-center gap-3">
    <div class="text-end d-none d-md-block lh-1">
      <div style="font-size:.8rem;font-weight:700;color:#1a1a2e">'.$nama.'</div>
      <div style="font-size:.7rem;color:#6c757d">Dosen</div>
    </div>
  </div>
</div>';
}

function dosenSidebar(string $cur): string {
    $items = [
        ['section'=>'Menu'],
        ['href'=>'dashboard.php','icon'=>'bi-grid-1x2-fill','label'=>'Dashboard'],
        ['href'=>'reservasi.php','icon'=>'bi-calendar-plus-fill','label'=>'Buat Reservasi'],
        ['href'=>'riwayat.php','icon'=>'bi-clock-history','label'=>'Riwayat Reservasi'],
        ['href'=>'jadwal.php','icon'=>'bi-table','label'=>'Jadwal Ruangan'],
        ['section'=>'Akun'],
        ['href'=>'profil.php','icon'=>'bi-person-circle','label'=>'Profil Saya'],
        ['href'=>'logout.php','icon'=>'bi-box-arrow-left','label'=>'Logout','danger'=>true],
    ];
    $html = '<div class="sidebar" id="sidebar">
  <div class="sidebar-brand d-flex align-items-center">
    <div class="logo-icon"><i class="bi bi-pc-display text-white fs-5"></i></div>
    <div class="ms-2">
      <div style="color:#fff;font-weight:800;font-size:.95rem;">SiResLab</div>
      <div style="color:rgba(255,255,255,.4);font-size:.65rem;">Portal Dosen</div>
    </div>
  </div>
  <nav class="nav-menu">';
    foreach($items as $it){
        if(isset($it['section'])){
            $html .= '<div class="nav-section-label">'.$it['section'].'</div>';
        } else {
            $active = ($it['href']===$cur) ? 'active' : '';
            $style  = isset($it['danger']) ? ' style="color:rgba(255,100,100,.8)"' : '';
            $html  .= '<a href="'.$it['href'].'" class="nav-item-link '.$active.'"'.$style.'><i class="bi '.$it['icon'].'"></i>'.$it['label'].'</a>';
        }
    }
    $html .= '</nav></div><div class="sidebar-overlay" id="sOverlay" onclick="toggleSB()"></div>';
    return $html;
}

function dosenFoot(): string {
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
