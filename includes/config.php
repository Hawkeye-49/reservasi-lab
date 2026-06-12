<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'reservasi_lab');
define('DB_CHARSET', 'utf8mb4');
define('APP_NAME', 'SiResLab');
define('APP_FULL_NAME', 'Sistem Reservasi Laboratorium Komputer');
define('BASE_URL', '');

date_default_timezone_set('Asia/Jakarta');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['success'=>false,'message'=>'DB Error: '.$e->getMessage()]));
        }
    }
    return $pdo;
}

function generateKode(): string {
    return 'RSV-'.date('Ymd').'-'.strtoupper(substr(md5(uniqid(mt_rand(),true)),0,6));
}

function sanitize(string $s): string {
    return htmlspecialchars(strip_tags(trim($s)), ENT_QUOTES, 'UTF-8');
}

function formatTgl(string $d): string {
    $bl=['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun',
         '07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
    $dt = date_create($d);
    return date_format($dt,'d').' '.$bl[date_format($dt,'m')].' '.date_format($dt,'Y');
}

function hariID(string $d): string {
    $h=['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
        'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    return $h[date('l',strtotime($d))];
}

function badgeStatus(string $s): string {
    $m=['pending'=>'<span class="badge bg-warning text-dark">Pending</span>',
        'disetujui'=>'<span class="badge bg-success">Disetujui</span>',
        'ditolak'=>'<span class="badge bg-danger">Ditolak</span>',
        'dibatalkan'=>'<span class="badge bg-secondary">Dibatalkan</span>'];
    return $m[$s] ?? "<span class='badge bg-light text-dark'>$s</span>";
}

function jsonRes(bool $ok, string $msg, array $extra=[]): void {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success'=>$ok,'message'=>$msg], $extra));
    exit;
}

function isAdmin(): bool { return !empty($_SESSION['admin_id']); }
function isDosen(): bool { return !empty($_SESSION['dosen_id']); }

function requireAdmin(): void {
    if (!isAdmin()) { header('Location: login.php'); exit; }
}
function requireDosen(): void {
    if (!isDosen()) { header('Location: ../dosen/login.php'); exit; }
}
