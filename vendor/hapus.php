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

/* =====================
   VALIDASI ID
===================== */
$id_vendor = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_vendor <= 0) {
    header("Location: index.php?error=id_tidak_valid");
    exit;
}

/* =====================
   CEK APAKAH DIPAKAI INVOICE
===================== */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM invoice 
    WHERE id_vendor = ?
");
$stmt->bind_param("i", $id_vendor);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data['total'] > 0) {
    echo "<script>
        alert('Vendor tidak bisa dihapus karena masih digunakan di invoice!');
        window.location='index.php';
    </script>";
    exit;
}

/* =====================
   AMBIL NAMA VENDOR (UNTUK LOG)
===================== */
$qVendor = $conn->prepare("
    SELECT nama_vendor 
    FROM vendor 
    WHERE id_vendor = ?
");
$qVendor->bind_param("i", $id_vendor);
$qVendor->execute();
$vendor = $qVendor->get_result()->fetch_assoc();

/* =====================
   HAPUS VENDOR
===================== */
$del = $conn->prepare("
    DELETE FROM vendor 
    WHERE id_vendor = ?
");
$del->bind_param("i", $id_vendor);

if (!$del->execute()) {
    die('Gagal hapus vendor: ' . $del->error);
}

/* =====================
LOG AKTIVITAS
===================== */
logAktivitas(
    $conn,
    $_SESSION['user_id'],
    'Hapus Vendor',
    'Vendor: ' . ($vendor['nama_vendor'] ?? '-')
);

/* =====================
 REDIRECT
===================== */
header("Location: index.php?status=hapus");
exit;