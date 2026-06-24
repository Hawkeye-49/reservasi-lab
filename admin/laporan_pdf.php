<?php
require_once '../vendor/autoload.php';
require_once '../includes/config.php';

$dari = $_GET['dari'] ?? date('Y-m-01');
$ke   = $_GET['ke'] ?? date('Y-m-t');
$db = getDB();

$stmt = $db->prepare("
SELECT
    r.kode_reservasi,
    d.nama AS dosen,
    ru.nama AS ruangan,
    k.nama AS kelas,
    mk.nama AS matakuliah,
    r.tanggal,
    CONCAT(s.jam_mulai,' - ',s.jam_selesai) AS jam,
    r.status
FROM reservasi r
JOIN dosen d ON r.dosen_id = d.id
JOIN ruangan ru ON r.ruangan_id = ru.id
JOIN kelas k ON r.kelas_id = k.id
JOIN matakuliah mk ON r.matakuliah_id = mk.id
JOIN slot_waktu s ON r.slot_waktu_id = s.id
WHERE r.tanggal BETWEEN ? AND ?
ORDER BY r.tanggal, s.jam_mulai
");

$stmt->execute([$dari, $ke]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt->execute([$dari,$ke]);
$data = $stmt->fetchAll();

$html = '
<h2 style="text-align:center;">Laporan Reservasi Laboratorium Komputer</h2>
<p>Periode '.$dari.' s/d '.$ke.'</p>

<table border="1" width="100%" cellpadding="5" cellspacing="0">
<tr style="background:#f2f2f2;">
<th>No</th>
<th>Kode</th>
<th>Dosen</th>
<th>Ruangan</th>
<th>Kelas</th>
<th>Mata Kuliah</th>
<th>Tanggal</th>
<th>Jam</th>
<th>Status</th>
</tr>';

$no = 1;

foreach ($data as $d) {
    $html .= '
    <tr>
        <td>'.$no++.'</td>
        <td>'.$d['kode_reservasi'].'</td>
        <td>'.$d['dosen'].'</td>
        <td>'.$d['ruangan'].'</td>
        <td>'.$d['kelas'].'</td>
        <td>'.$d['matakuliah'].'</td>
        <td>'.$d['tanggal'].'</td>
        <td>'.$d['jam'].'</td>
        <td>'.$d['status'].'</td>
    </tr>';
}

$html .= '</table>';

$mpdf = new \Mpdf\Mpdf([
    'orientation' => 'L'
]);
$mpdf->WriteHTML($html);

$mpdf->Output(
    'laporan_reservasi_lab_'.date('Ymd_His').'.pdf',
    \Mpdf\Output\Destination::DOWNLOAD
);