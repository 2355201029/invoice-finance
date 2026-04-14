<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

/* ===============================
   CEK LOGIN
=============================== */
$id_user = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 0;

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* ===============================
   CEK METHOD
=============================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

/* ===============================
   AMBIL DATA
=============================== */
$id_invoice  = intval($_POST['id_invoice'] ?? 0);
$vendor      = trim($_POST['vendor'] ?? '');
$kode_lemari = trim($_POST['kode_lemari'] ?? '');
$lemari_ke   = intval($_POST['lemari_ke'] ?? 0);
$rak_ke      = intval($_POST['rak_ke'] ?? 0);
$nama_bank   = trim($_POST['nama_bank'] ?? '');
$nomor_sap   = trim($_POST['nomor_sap'] ?? '');

$tanggal = date('Y-m-d H:i:s');
$bulan   = date('m');
$tahun   = date('Y');

/* ===============================
   VALIDASI INPUT
=============================== */
if (
    $id_invoice <= 0 ||
    empty($vendor) ||
    empty($kode_lemari) ||
    $lemari_ke <= 0 ||
    $rak_ke <= 0 ||
    empty($nama_bank) ||
    empty($nomor_sap)
) {

    logAktivitas(
        $conn,
        $id_user,
        'Gagal Tambah Lemari Dokumen',
        'Data tidak lengkap'
    );

    header("Location: tambah.php?error=invalid");
    exit;
}

/* ===============================
   CEK DUPLIKAT
=============================== */
$cek = $conn->prepare("SELECT id_lemari FROM lemari_dokumen_invoice WHERE kode_lemari = ?");

if (!$cek) {
    die("Prepare Cek Gagal: " . $conn->error);
}

$cek->bind_param("s", $kode_lemari);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {

    logAktivitas(
        $conn,
        $id_user,
        'Gagal Tambah Lemari Dokumen',
        "Kode lemari sudah digunakan: $kode_lemari"
    );

    header("Location: tambah.php?error=duplicate");
    exit;
}

$cek->close();

/* ===============================
   INSERT DATA
=============================== */
$stmt = $conn->prepare("
    INSERT INTO lemari_dokumen_invoice
    (id_invoice, vendor, lemari_ke, rak_ke, nama_bank, tanggal, bulan, tahun, nomor_sap, created_at, kode_lemari)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
");

if (!$stmt) {
    die("Prepare Insert Gagal: " . $conn->error);
}

$stmt->bind_param(
    "isiississs",
    $id_invoice,
    $vendor,
    $lemari_ke,
    $rak_ke,
    $nama_bank,
    $tanggal,
    $bulan,
    $tahun,
    $nomor_sap,
    $kode_lemari
);

if ($stmt->execute()) {

    /* ===============================
       LOG BERHASIL
    =============================== */
    logAktivitas(
        $conn,
        $id_user,
        'Tambah Lemari Dokumen Invoice',
        "Invoice ID: $id_invoice | Kode: $kode_lemari | Vendor: $vendor | Lemari: $lemari_ke | Rak: $rak_ke | SAP: $nomor_sap"
    );

    header("Location: index.php?success=1");
    exit;

} else {

    logAktivitas(
        $conn,
        $id_user,
        'Gagal Tambah Lemari Dokumen',
        "Error Database: " . $stmt->error
    );

    header("Location: tambah.php?error=database");
    exit;
}

$stmt->close();
$conn->close();
?>