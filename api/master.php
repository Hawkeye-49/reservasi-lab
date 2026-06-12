<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
$db = getDB();
$action = $_GET['action'] ?? '';

switch($action){
  case 'dosen':       jsonRes(true,'ok',['data'=>$db->query("SELECT id,nidn,nama,email,no_hp,status FROM dosen ORDER BY nama")->fetchAll()]); break;
  case 'matakuliah':  jsonRes(true,'ok',['data'=>$db->query("SELECT id,kode,nama,sks,jurusan,status FROM matakuliah ORDER BY nama")->fetchAll()]); break;
  case 'kelas':       jsonRes(true,'ok',['data'=>$db->query("SELECT id,nama,jurusan,semester,tahun_ajaran,kapasitas,status FROM kelas ORDER BY nama")->fetchAll()]); break;
  case 'ruangan':     jsonRes(true,'ok',['data'=>$db->query("SELECT id,kode,nama,kapasitas,lokasi,fasilitas,status FROM ruangan ORDER BY nama")->fetchAll()]); break;
  case 'slot_waktu':  jsonRes(true,'ok',['data'=>$db->query("SELECT id,sesi,label FROM slot_waktu ORDER BY sesi")->fetchAll()]); break;
  case 'save_dosen':  saveDosen($db); break;
  case 'del_dosen':   delItem($db,'dosen',$_POST['id']??0); break;
  case 'save_mk':     saveMK($db); break;
  case 'del_mk':      delItem($db,'matakuliah',$_POST['id']??0); break;
  case 'save_kelas':  saveKelas($db); break;
  case 'del_kelas':   delItem($db,'kelas',$_POST['id']??0); break;
  case 'save_ruangan':saveRuangan($db); break;
  case 'del_ruangan': delItem($db,'ruangan',$_POST['id']??0); break;
  default: jsonRes(false,'action tidak dikenal');
}

function inp(string $k): string { return sanitize($_POST[$k] ?? ''); }
function inpI(string $k): int   { return (int)($_POST[$k] ?? 0); }

function delItem(PDO $db, string $tbl, int $id): void {
    if(!$id) jsonRes(false,'ID tidak valid');
    $allowed = ['dosen','matakuliah','kelas','ruangan'];
    if(!in_array($tbl,$allowed)) jsonRes(false,'tabel tidak valid');
    $db->prepare("DELETE FROM $tbl WHERE id=?")->execute([$id]);
    jsonRes(true,'Data berhasil dihapus');
}

function saveDosen(PDO $db): void {
    $id   = inpI('id');
    $nidn = inp('nidn'); $nama = inp('nama');
    $email= inp('email'); $hp   = inp('no_hp');
    $stat = inp('status') ?: 'aktif';
    $pass = inp('password');
    if(!$nidn||!$nama||!$email) jsonRes(false,'NIDN, nama, email wajib diisi');
    if($id){
        if($pass){
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $db->prepare("UPDATE dosen SET nidn=?,nama=?,email=?,password=?,no_hp=?,status=? WHERE id=?")->execute([$nidn,$nama,$email,$hash,$hp,$stat,$id]);
        } else {
            $db->prepare("UPDATE dosen SET nidn=?,nama=?,email=?,no_hp=?,status=? WHERE id=?")->execute([$nidn,$nama,$email,$hp,$stat,$id]);
        }
        jsonRes(true,'Data dosen berhasil diperbarui');
    } else {
        if(!$pass) jsonRes(false,'Password wajib diisi untuk dosen baru');
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO dosen(nidn,nama,email,password,no_hp,status) VALUES(?,?,?,?,?,?)")->execute([$nidn,$nama,$email,$hash,$hp,$stat]);
        jsonRes(true,'Dosen berhasil ditambahkan');
    }
}

function saveMK(PDO $db): void {
    $id=inpI('id'); $kode=inp('kode'); $nama=inp('nama'); $sks=inpI('sks'); $stat=inp('status')?:'aktif';
    if(!$kode||!$nama||!$sks) jsonRes(false,'Kode, nama, SKS wajib diisi');
    if($id){
        $db->prepare("UPDATE matakuliah SET kode=?,nama=?,sks=?,status=? WHERE id=?")->execute([$kode,$nama,$sks,$stat,$id]);
        jsonRes(true,'Mata kuliah berhasil diperbarui');
    } else {
        $db->prepare("INSERT INTO matakuliah(kode,nama,sks,jurusan,status) VALUES(?,?,?,'Informatika',?)")->execute([$kode,$nama,$sks,$stat]);
        jsonRes(true,'Mata kuliah berhasil ditambahkan');
    }
}

function saveKelas(PDO $db): void {
    $id=inpI('id'); $nama=inp('nama'); $sem=inpI('semester'); $ta=inp('tahun_ajaran'); $kap=inpI('kapasitas'); $stat=inp('status')?:'aktif';
    if(!$nama||!$sem||!$ta||!$kap) jsonRes(false,'Nama kelas, semester, tahun ajaran, dan kapasitas wajib diisi');
    if($id){
        $db->prepare("UPDATE kelas SET nama=?,semester=?,tahun_ajaran=?,kapasitas=?,status=? WHERE id=?")->execute([$nama,$sem,$ta,$kap,$stat,$id]);
        jsonRes(true,'Kelas berhasil diperbarui');
    } else {
        $db->prepare("INSERT INTO kelas(nama,jurusan,semester,tahun_ajaran,kapasitas,status) VALUES(?,'Informatika',?,?,?,?)")->execute([$nama,$sem,$ta,$kap,$stat]);
        jsonRes(true,'Kelas berhasil ditambahkan');
    }
}

function saveRuangan(PDO $db): void {
    $id=inpI('id'); $kode=inp('kode'); $nama=inp('nama'); $kap=inpI('kapasitas'); $lok=inp('lokasi'); $fas=inp('fasilitas'); $stat=inp('status')?:'aktif';
    if(!$kode||!$nama) jsonRes(false,'Kode dan nama ruangan wajib diisi');
    if($id){
        $db->prepare("UPDATE ruangan SET kode=?,nama=?,kapasitas=?,lokasi=?,fasilitas=?,status=? WHERE id=?")->execute([$kode,$nama,$kap,$lok,$fas,$stat,$id]);
        jsonRes(true,'Ruangan berhasil diperbarui');
    } else {
        $db->prepare("INSERT INTO ruangan(kode,nama,kapasitas,lokasi,fasilitas,status) VALUES(?,?,?,?,?,?)")->execute([$kode,$nama,$kap,$lok,$fas,$stat]);
        jsonRes(true,'Ruangan berhasil ditambahkan');
    }
}
