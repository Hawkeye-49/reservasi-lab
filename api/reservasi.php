<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
$db  = getDB();
$act = $_GET['action'] ?? '';
$met = $_SERVER['REQUEST_METHOD'];

switch($act){
  case 'slot_status':   slotStatus($db); break;
  case 'jadwal':        jadwal($db); break;
  case 'list':          listReservasi($db); break;
  case 'detail':        detail($db); break;
  case 'statistik':     statistik($db); break;
  case 'laporan':       laporan($db); break;
  case 'buat':          buat($db); break;
  case 'update_status': updateStatus($db); break;
  case 'batal':         batalkan($db); break;
  default: jsonRes(false,'action tidak dikenal');
}

// helpers
function baseQuery(): string {
    return "SELECT r.*,d.nama dosen,d.nidn,d.email email_dosen,
            ru.nama ruangan,ru.kode kode_ruangan,ru.lokasi,ru.kapasitas,ru.fasilitas,
            mk.nama matakuliah,mk.kode kode_mk,mk.sks,
            kl.nama kelas,
            sw.label jam,sw.sesi
            FROM reservasi r
            JOIN dosen d ON r.dosen_id=d.id
            JOIN ruangan ru ON r.ruangan_id=ru.id
            JOIN matakuliah mk ON r.matakuliah_id=mk.id
            JOIN kelas kl ON r.kelas_id=kl.id
            JOIN slot_waktu sw ON r.slot_waktu_id=sw.id";
}

// status slot, cek ketersediaan ruangan & tanggal
function slotStatus(PDO $db): void {
    $rid  = (int)($_GET['ruangan_id']??0);
    $tgl  = $_GET['tanggal']??'';
    if(!$rid||!$tgl) jsonRes(false,'parameter kurang');
    $stmt = $db->prepare("SELECT sw.id,sw.sesi,sw.label,sw.jam_mulai, (SELECT COUNT(*) FROM reservasi WHERE ruangan_id=? AND tanggal=? AND slot_waktu_id=sw.id AND is_active=1) booked FROM slot_waktu sw ORDER BY sw.sesi");
    $stmt->execute([$rid,$tgl]);
    $rows = $stmt->fetchAll();


    if($tgl === date('Y-m-d')) {
        foreach($rows as &$r) {
            $waktuMulai = strtotime($tgl.' '.$r['jam_mulai']);
            if($waktuMulai <= time()) {
                $r['booked'] = 1;
                $r['expired'] = 1;
            } else {
                $r['expired'] = 0;
            }
            unset($r['jam_mulai']);
        }
    } else {
        foreach($rows as &$r) { unset($r['jam_mulai']); }
    }

    jsonRes(true,'ok',['data'=>$rows]);
}

// jadwal
function jadwal(PDO $db): void {
    $tgl = $_GET['tanggal'] ?? date('Y-m-d');
    $stmt = $db->prepare(baseQuery()." WHERE r.tanggal=? AND r.is_active=1 ORDER BY sw.sesi");
    $stmt->execute([$tgl]);
    $data = $stmt->fetchAll();
    $ruangan = $db->query("SELECT id,kode,nama FROM ruangan WHERE status='aktif' ORDER BY nama")->fetchAll();
    $slots   = $db->query("SELECT id,sesi,label FROM slot_waktu ORDER BY sesi")->fetchAll();
    jsonRes(true,'ok',['data'=>$data,'ruangan'=>$ruangan,'slots'=>$slots]);
}

// list reservasi
function listReservasi(PDO $db): void {
    $status    = $_GET['status']??'';
    $tgl       = $_GET['tanggal']??'';
    $dosen_id  = (int)($_GET['dosen_id']??0);
    $limit     = min((int)($_GET['limit']??50),200);
    $offset    = (int)($_GET['offset']??0);

    $w=[]; $p=[];
    if($status){ $w[]="r.status=?"; $p[]=$status; }
    if($tgl)   { $w[]="r.tanggal=?"; $p[]=$tgl; }
    if($dosen_id){ $w[]="r.dosen_id=?"; $p[]=$dosen_id; }
    $where = $w ? "WHERE ".implode(' AND ',$w) : '';

    $stmt=$db->prepare(baseQuery()." $where ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($p);
    $total=$db->prepare("SELECT COUNT(*) FROM reservasi r $where");
    $total->execute($p);
    jsonRes(true,'ok',['data'=>$stmt->fetchAll(),'total'=>(int)$total->fetchColumn()]);
}

// detail reservasi
function detail(PDO $db): void {
    $id = (int)($_GET['id']??0);
    $kode = $_GET['kode']??'';
    $col = $id ? 'r.id' : 'r.kode_reservasi';
    $val = $id ?: $kode;
    if(!$val) jsonRes(false,'parameter kurang');
    $stmt=$db->prepare(baseQuery()." WHERE $col=?");
    $stmt->execute([$val]);
    $row=$stmt->fetch();
    if(!$row) jsonRes(false,'Reservasi tidak ditemukan');
    jsonRes(true,'ok',['data'=>$row]);
}

// statistik
function statistik(PDO $db): void {
    $total =$db->query("SELECT COUNT(*) FROM reservasi")->fetchColumn();
    $pending =$db->query("SELECT COUNT(*) FROM reservasi WHERE status='pending'")->fetchColumn();
    $setuju =$db->query("SELECT COUNT(*) FROM reservasi WHERE status='disetujui'")->fetchColumn();
    $tolak =$db->query("SELECT COUNT(*) FROM reservasi WHERE status='ditolak'")->fetchColumn();
    $batal =$db->query("SELECT COUNT(*) FROM reservasi WHERE status='dibatalkan'")->fetchColumn();
    $hariIni =$db->query("SELECT COUNT(*) FROM reservasi WHERE tanggal=CURDATE() AND status='disetujui'")->fetchColumn();
    $bulanIni =$db->query("SELECT COUNT(*) FROM reservasi WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())")->fetchColumn();
    $ruangan =$db->query("SELECT ru.nama,COUNT(*) n FROM reservasi r JOIN ruangan ru ON r.ruangan_id=ru.id GROUP BY ru.id ORDER BY n DESC")->fetchAll();
    $trend =$db->query("SELECT DATE_FORMAT(tanggal,'%Y-%m') bln,COUNT(*) n FROM reservasi WHERE tanggal>=DATE_SUB(CURDATE(),INTERVAL 5 MONTH) GROUP BY bln ORDER BY bln")->fetchAll();
    jsonRes(true,'ok',['data'=>compact('total','pending','setuju','tolak','batal','hariIni','bulanIni','ruangan','trend')]);
}

// laporan
function laporan(PDO $db): void {
    $dari = $_GET['dari'] ?? date('Y-m-01');
    $ke = $_GET['ke']   ?? date('Y-m-t');
    $stmt = $db->prepare(baseQuery()." WHERE r.tanggal BETWEEN ? AND ? ORDER BY r.tanggal,sw.sesi");
    $stmt->execute([$dari,$ke]);
    $data = $stmt->fetchAll();
    $per_ruangan = $db->prepare("SELECT ru.nama,COUNT(*) n FROM reservasi r JOIN ruangan ru ON r.ruangan_id=ru.id WHERE r.tanggal BETWEEN ? AND ? AND r.status='disetujui' GROUP BY ru.id ORDER BY n DESC");
    $per_ruangan->execute([$dari,$ke]);
    $per_dosen = $db->prepare("SELECT d.nama,COUNT(*) n FROM reservasi r JOIN dosen d ON r.dosen_id=d.id WHERE r.tanggal BETWEEN ? AND ? AND r.status='disetujui' GROUP BY d.id ORDER BY n DESC LIMIT 10");
    $per_dosen->execute([$dari,$ke]);
    jsonRes(true,'ok',['data'=>$data,'per_ruangan'=>$per_ruangan->fetchAll(),'per_dosen'=>$per_dosen->fetchAll()]);
}

// buat reservasi
function buat(PDO $db): void {
    $in = json_decode(file_get_contents('php://input'),true) ?? $_POST;
    $did =(int)($in['dosen_id']??0);
    $rid =(int)($in['ruangan_id']??0);
    $mkid =(int)($in['matakuliah_id']??0);
    $klid =(int)($in['kelas_id']??0);
    $slid =(int)($in['slot_waktu_id']??0);
    $tgl = sanitize($in['tanggal']??'');
    $ket = sanitize($in['keterangan']??'');

    if(!$did||!$rid||!$mkid||!$klid||!$slid||!$tgl) jsonRes(false,'Semua field wajib diisi');

    try {
        $kode = generateKode();
        $db->prepare("INSERT INTO reservasi(kode_reservasi,dosen_id,ruangan_id,matakuliah_id,kelas_id,slot_waktu_id,tanggal,jurusan,keterangan,status,is_active) VALUES(?,?,?,?,?,?,?,'Informatika',?,'pending',1)")
           ->execute([$kode,$did,$rid,$mkid,$klid,$slid,$tgl,$ket]);
        jsonRes(true,'Reservasi berhasil diajukan! Menunggu persetujuan admin.',['kode'=>$kode,'id'=>(int)$db->lastInsertId()]);
    } catch(PDOException $e){
        if($e->getCode()==23000) jsonRes(false,'Konflik: ruangan sudah terisi');
        jsonRes(false,'Error: '.$e->getMessage());
    }
}

// update status (admin)
function updateStatus(PDO $db): void {
    if(!isAdmin()) jsonRes(false,'Akses ditolak');
    $in = json_decode(file_get_contents('php://input'),true) ?? $_POST;
    $id = (int)($in['id']??0);
    $status = sanitize($in['status']??'');
    $catatan = sanitize($in['catatan']??'');
    if(!$id||!in_array($status,['disetujui','ditolak','dibatalkan'])) jsonRes(false,'Parameter tidak valid');
    $isActive = in_array($status, ['pending','disetujui'], true) ? 1 : 0;
    $db->prepare("UPDATE reservasi SET status=?,catatan_admin=?,is_active=? WHERE id=?")->execute([$status,$catatan,$isActive,$id]);
    jsonRes(true,"Reservasi berhasil $status");
}

// batalkan reservasi (dosen)
function batalkan(PDO $db): void {
    $in = json_decode(file_get_contents('php://input'),true) ?? $_POST;
    $id = (int)($in['id']??0);
    $did = (int)($in['dosen_id']??$_SESSION['dosen_id']??0);
    if(!$id) jsonRes(false,'ID tidak valid');
    $row=$db->prepare("SELECT id,status,dosen_id,tanggal,slot_waktu_id FROM reservasi WHERE id=?");
    $row->execute([$id]);
    $r=$row->fetch();
    if(!$r) jsonRes(false,'Reservasi tidak ditemukan');
    if($r['dosen_id']!=$did && !isAdmin()) jsonRes(false,'Tidak memiliki akses');
    if(!in_array($r['status'],['pending','disetujui'])) jsonRes(false,'Reservasi tidak dapat dibatalkan');
    $sw=$db->prepare("SELECT jam_mulai FROM slot_waktu WHERE id=?");
    $sw->execute([$r['slot_waktu_id']]);
    $slot=$sw->fetch();
    $waktuMulai = strtotime($r['tanggal'].' '.$slot['jam_mulai']);
    if($waktuMulai - time() < 7200 && !isAdmin()) jsonRes(false,'Pembatalan hanya bisa dilakukan minimal 2 jam sebelum sesi');
    $db->prepare("UPDATE reservasi SET status='dibatalkan',is_active=0 WHERE id=?")->execute([$id]);
    jsonRes(true,'Reservasi berhasil dibatalkan');
}
