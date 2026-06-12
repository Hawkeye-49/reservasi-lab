<?php
$current = basename($_SERVER['PHP_SELF']);
function navLink($href,$icon,$label,$cur){
    $active = (basename($href)===$cur||strpos($cur,$href)!==false)?'active':'';
    echo "<a href='$href' class='nav-item-link $active'><i class='bi $icon'></i>$label</a>";
}
?>
<div class="sidebar" id="sidebar">
  <div class="sidebar-brand d-flex align-items-center">
    <div class="logo-icon"><i class="bi bi-pc-display text-white fs-5"></i></div>
    <div class="ms-2">
      <div style="color:#fff;font-weight:800;font-size:.95rem;">SiResLab</div>
      <div style="color:rgba(255,255,255,.4);font-size:.65rem;">Admin Panel</div>
    </div>
  </div>
  <nav class="nav-menu">
    <div class="nav-section-label">Menu Utama</div>
    <?php navLink('dashboard.php','bi-grid-1x2-fill','Dashboard',$current); ?>
    <?php navLink('reservasi.php','bi-calendar-check-fill','Kelola Reservasi',$current); ?>
    <?php navLink('jadwal.php','bi-table','Jadwal Ruangan',$current); ?>
    <?php navLink('laporan.php','bi-bar-chart-fill','Laporan Penggunaan',$current); ?>
    <div class="nav-section-label">Master Data</div>
    <?php navLink('dosen.php','bi-person-badge-fill','Data Dosen',$current); ?>
    <?php navLink('matakuliah.php','bi-book-fill','Mata Kuliah',$current); ?>
    <?php navLink('kelas.php','bi-people-fill','Data Kelas',$current); ?>
    <?php navLink('ruangan.php','bi-building-fill','Laboratorium',$current); ?>
    <div class="nav-section-label">Akun</div>
    <a href="logout.php" class="nav-item-link" style="color:rgba(255,100,100,.8);">
      <i class="bi bi-box-arrow-left"></i>Logout
    </a>
  </nav>
</div>
<div class="sidebar-overlay" id="sOverlay" onclick="toggleSB()"></div>
