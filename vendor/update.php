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
$id_vendor     = (int) ($_POST['id_vendor'] ?? 0);
$nama_vendor   = trim($_POST['nama_vendor'] ?? '');
$alamat_vendor = trim($_POST['alamat_vendor'] ?? '');
$no_telp       = trim($_POST['no_telp'] ?? '');
$email         = trim($_POST['email'] ?? '');

if ($id_vendor <= 0 || $nama_vendor === '') {
    header("Location: index.php?error=data_tidak_valid");
    exit;
}

/* =====================
   UPDATE DATA (PREPARED)
===================== */
$stmt = $conn->prepare("
    UPDATE vendor SET
        nama_vendor = ?,
        alamat_vendor = ?,
        no_telp = ?,
        email = ?
    WHERE id_vendor = ?
");

$stmt->bind_param(
    "ssssi",
    $nama_vendor,
    $alamat_vendor,
    $no_telp,
    $email,
    $id_vendor
);

if (!$stmt->execute()) {
    die('Gagal update vendor: ' . $stmt->error);
}

/* =====================
   LOG AKTIVITAS
===================== */
logAktivitas(
    $conn,
    $_SESSION['user_id'],
    'Edit Vendor',
    'Vendor: ' . $nama_vendor
);

/* =====================
   REDIRECT
===================== */
header("Location: index.php?status=updated");
exit;