<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Akses ditolak');
}

$file = basename($_GET['file'] ?? '');
$path = __DIR__ . '/../uploads/invoice/' . $file;

if (!$file || !file_exists($path)) {
    die('File tidak ditemukan');
}

$mime = mime_content_type($path);

if (isset($_GET['dl'])) {
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$file\"");
} else {
    header("Content-Type: $mime");
}

header("Content-Length: " . filesize($path));
readfile($path);
exit;