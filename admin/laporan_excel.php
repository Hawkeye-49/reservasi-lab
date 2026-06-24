<?php
require_once '../vendor/autoload.php';
require_once '../includes/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

$dari = $_GET['dari'] ?? date('Y-m-01');
$ke   = $_GET['ke']   ?? date('Y-m-t');
$db   = getDB();

$stmt = $db->prepare("
    SELECT
        r.kode_reservasi,
        d.nama AS dosen,
        ru.nama AS ruangan,
        k.nama  AS kelas,
        mk.nama AS matakuliah,
        r.tanggal,
        CONCAT(s.jam_mulai,' - ',s.jam_selesai) AS jam,
        r.status
    FROM reservasi r
    JOIN dosen      d  ON r.dosen_id      = d.id
    JOIN ruangan    ru ON r.ruangan_id    = ru.id
    JOIN kelas      k  ON r.kelas_id      = k.id
    JOIN matakuliah mk ON r.matakuliah_id = mk.id
    JOIN slot_waktu s  ON r.slot_waktu_id = s.id
    WHERE r.tanggal BETWEEN ? AND ?
    ORDER BY r.tanggal, s.jam_mulai
");
$stmt->execute([$dari, $ke]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total     = count($data);
$disetujui = count(array_filter($data, fn($r) => $r['status'] === 'disetujui'));
$pending   = count(array_filter($data, fn($r) => $r['status'] === 'pending'));
$ditolak   = count(array_filter($data, fn($r) => $r['status'] === 'ditolak'));
$dibatalkan   = count(array_filter($data, fn($r) => $r['status'] === 'dibatalkan'));

function formatTglAll(string $tgl): string {
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
    [$y, $m, $d] = explode('-', $tgl);
    return (int)$d . ' ' . $bulan[(int)$m] . ' ' . $y;
}

$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Reservasi');

const CLR_BIRU       = '0099CC';
const CLR_BIRU_TUA   = '006699';
const CLR_BIRU_MUDA  = 'E8F4FD';
const CLR_BG_STRIP   = 'F5FBFF';
const CLR_HIJAU_BG   = 'D4EDDA';
const CLR_HIJAU_FONT = '155724';
const CLR_HIJAU_BDR  = 'C3E6CB';
const CLR_KUNING_BG  = 'FFF3CD';
const CLR_KUNING_FONT= '856404';
const CLR_KUNING_BDR = 'FFEEBA';
const CLR_MERAH_BG   = 'F8D7DA';
const CLR_MERAH_FONT = '721C24';
const CLR_MERAH_BDR  = 'F5C6CB';
const CLR_ABU_BG   = 'D3D3D3';
const CLR_ABU_FONT = '000000';
const CLR_ABU_BDR  = 'C3C3C3';

function applyStyle($sheet, string $range, array $style): void {
    $sheet->getStyle($range)->applyFromArray($style);
}

function borderAll(string $color = 'DDDDDD'): array {
    return [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['rgb' => $color],
            ],
        ],
    ];
}

$sheet->getColumnDimension('A')->setWidth(13);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(28);
$sheet->getColumnDimension('D')->setWidth(18);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(28);
$sheet->getColumnDimension('G')->setWidth(14);
$sheet->getColumnDimension('H')->setWidth(16);
$sheet->getColumnDimension('I')->setWidth(14);

$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', 'LAPORAN RESERVASI LABORATORIUM KOMPUTER');
$sheet->getRowDimension(1)->setRowHeight(32);
applyStyle($sheet, 'A1:I1', [
    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => CLR_BIRU]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => CLR_BIRU_TUA]]],
]);

$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2', 'Periode: ' . formatTglAll($dari) . ' s/d ' . formatTglAll($ke));
$sheet->getRowDimension(2)->setRowHeight(22);
applyStyle($sheet, 'A2:I2', [
    'font'      => ['size' => 11, 'color' => ['rgb' => '444444'], 'name' => 'Calibri'],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => CLR_BIRU_MUDA]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);

$sheet->getRowDimension(3)->setRowHeight(10);

$sheet->getRowDimension(4)->setRowHeight(20);

// Total Reservasi
$sheet->setCellValue('A4', 'Total Reservasi');
$sheet->setCellValue('B4', $total);
applyStyle($sheet, 'A4', array_merge(['font' => ['bold' => true, 'size' => 10, 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F8FF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll('CCCCCC')));
applyStyle($sheet, 'B4', array_merge(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => CLR_BIRU], 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll('CCCCCC')));

// Disetujui
$sheet->setCellValue('D4', 'Disetujui');
$sheet->setCellValue('E4', $disetujui);
applyStyle($sheet, 'D4', array_merge(['font' => ['bold' => true, 'size' => 10, 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F8FF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll('CCCCCC')));
applyStyle($sheet, 'E4', array_merge(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => CLR_HIJAU_FONT], 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => CLR_HIJAU_BG]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll(CLR_HIJAU_BDR)));

// Pending
$sheet->setCellValue('G4', 'Pending');
$sheet->setCellValue('H4', $pending);
applyStyle($sheet, 'G4', array_merge(['font' => ['bold' => true, 'size' => 10, 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F8FF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll('CCCCCC')));
applyStyle($sheet, 'H4', array_merge(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => CLR_KUNING_FONT], 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => CLR_KUNING_BG]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll(CLR_KUNING_BDR)));

// Ditolak
$sheet->getRowDimension(5)->setRowHeight(20);
$sheet->setCellValue('D5', 'Ditolak');
$sheet->setCellValue('E5', $ditolak);
applyStyle($sheet, 'D5', array_merge(['font' => ['bold' => true, 'size' => 10, 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F8FF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll('CCCCCC')));
applyStyle($sheet, 'E5', array_merge(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => CLR_MERAH_FONT], 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => CLR_MERAH_BG]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll(CLR_MERAH_BDR)));

// Dibatalkan
$sheet->getRowDimension(5)->setRowHeight(20);
$sheet->setCellValue('G5', 'Dibatalkan');
$sheet->setCellValue('H5', $dibatalkan);
applyStyle($sheet, 'G5', array_merge(['font' => ['bold' => true, 'size' => 10, 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F8FF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll('CCCCCC')));
applyStyle($sheet, 'H5', array_merge(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => CLR_ABU_FONT], 'name' => 'Calibri'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => CLR_ABU_BG]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]], borderAll(CLR_ABU_BDR)));

$sheet->getRowDimension(6)->setRowHeight(10);

$headers = ['No', 'Kode Reservasi', 'Dosen', 'Ruangan', 'Kelas', 'Mata Kuliah', 'Tanggal', 'Jam', 'Status'];
$cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

$sheet->getRowDimension(7)->setRowHeight(22);
foreach ($headers as $i => $header) {
    $cell = $cols[$i] . '7';
    $sheet->setCellValue($cell, $header);
    applyStyle($sheet, $cell, [
        'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => CLR_BIRU]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => CLR_BIRU_TUA]]],
    ]);
}

$startRow = 8;

if (empty($data)) {
    $emptyRow = $startRow;
    $sheet->mergeCells("A{$emptyRow}:I{$emptyRow}");
    $sheet->setCellValue("A{$emptyRow}", 'Tidak ada data untuk periode ini.');
    $sheet->getRowDimension($emptyRow)->setRowHeight(30);
    applyStyle($sheet, "A{$emptyRow}:I{$emptyRow}", array_merge(
        ['font' => ['size' => 9, 'name' => 'Calibri'], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]],
        borderAll()
    ));
    $lastDataRow = $emptyRow;
} else {
    $no      = 1;
    $currRow = $startRow;
    foreach ($data as $row) {
        $isAlt = ($no % 2 === 0);
        $bgNormal = $isAlt ? CLR_BG_STRIP : 'FFFFFF';
        $sheet->getRowDimension($currRow)->setRowHeight(18);

        // Style dasar (left-align)
        $tdStyle = [
            'font'      => ['size' => 9, 'color' => ['rgb' => '333333'], 'name' => 'Calibri'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgNormal]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ];
        // Style center
        $tdCStyle = array_merge($tdStyle, ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]);

        // Style badge status
        [$badgeBg, $badgeFont, $badgeBdr] = match($row['status']) {
            'disetujui'               => [CLR_HIJAU_BG,  CLR_HIJAU_FONT,  CLR_HIJAU_BDR],
            'pending'                 => [CLR_KUNING_BG, CLR_KUNING_FONT, CLR_KUNING_BDR],
            'ditolak'                 => [CLR_MERAH_BG,  CLR_MERAH_FONT,  CLR_MERAH_BDR],
            'dibatalkan'              => [CLR_ABU_BG,    CLR_ABU_FONT,    CLR_ABU_BDR],
            default                   => ['E2E3E5',       '383D41',        'D6D8DB'],
        };
        $badgeStyle = [
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $badgeFont], 'name' => 'Calibri'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $badgeBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $badgeBdr]]],
        ];

        $sheet->setCellValue("A{$currRow}", $no);
        $sheet->setCellValue("B{$currRow}", $row['kode_reservasi']);
        $sheet->setCellValue("C{$currRow}", $row['dosen']);
        $sheet->setCellValue("D{$currRow}", $row['ruangan']);
        $sheet->setCellValue("E{$currRow}", $row['kelas']);
        $sheet->setCellValue("F{$currRow}", $row['matakuliah']);
        $sheet->setCellValue("G{$currRow}", $row['tanggal']);
        $sheet->setCellValue("H{$currRow}", $row['jam']);
        $sheet->setCellValue("I{$currRow}", ucfirst($row['status']));

        applyStyle($sheet, "A{$currRow}", $tdCStyle);
        applyStyle($sheet, "B{$currRow}", $tdCStyle);
        applyStyle($sheet, "C{$currRow}", $tdStyle);
        applyStyle($sheet, "D{$currRow}", $tdStyle);
        applyStyle($sheet, "E{$currRow}", $tdCStyle);
        applyStyle($sheet, "F{$currRow}", $tdStyle);
        applyStyle($sheet, "G{$currRow}", $tdCStyle);
        applyStyle($sheet, "H{$currRow}", $tdCStyle);
        applyStyle($sheet, "I{$currRow}", $badgeStyle);

        $no++;
        $currRow++;
    }
    $lastDataRow = $currRow - 1;
}

$spaceRow  = $lastDataRow + 1;
$footerRow = $lastDataRow + 2;

$sheet->getRowDimension($spaceRow)->setRowHeight(10);
$sheet->getRowDimension($footerRow)->setRowHeight(18);
$sheet->mergeCells("A{$footerRow}:I{$footerRow}");
$sheet->setCellValue("A{$footerRow}", 'Dicetak pada: ' . date('d/m/Y H:i:s') . ' — ' . APP_FULL_NAME);
applyStyle($sheet, "A{$footerRow}:I{$footerRow}", [
    'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '888888'], 'name' => 'Calibri'],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
]);

$sheet->freezePane('A8');

$sheet->getPageSetup()
    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
    ->setFitToPage(true)
    ->setFitToWidth(1)
    ->setFitToHeight(0);

$sheet->getPageMargins()
    ->setTop(0.75)->setBottom(0.75)
    ->setLeft(0.7)->setRight(0.7);

$filename = 'laporan_reservasi_lab_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$spreadsheet->disconnectWorksheets();
unset($spreadsheet);
exit;