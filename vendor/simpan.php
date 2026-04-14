<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

/* =====================
CEK LOGIN
===================== */
if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

/* =====================
AMBIL & VALIDASI INPUT
===================== */
$nama_vendor   = trim($_POST['nama_vendor'] ?? '');
$alamat_vendor = trim($_POST['alamat_vendor'] ?? '');
$no_telp       = trim($_POST['no_telp'] ?? '');
$email         = trim($_POST['email'] ?? '');

if ($nama_vendor === '') {
    header("Location: tambah.php?error=nama_kosong");
    exit;
}

/* =====================
SIMPAN DATA (PREPARED)
===================== */
$stmt = $conn->prepare("
    INSERT INTO vendor
    (nama_vendor, alamat_vendor, no_telp, email)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "ssss",
    $nama_vendor,
    $alamat_vendor,
    $no_telp,
    $email
);

if (!$stmt->execute()) {
    die('Gagal simpan vendor: ' . $stmt->error);
}

/* =====================
LOG AKTIVITAS
===================== */
logAktivitas(
    $conn,
    $_SESSION['user_id'],
    'Tambah Vendor',
    'Vendor: ' . $nama_vendor
);

/* =====================
REDIRECT
===================== */
header("Location: index.php?status=success");
exit;