<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';
require_once __DIR__ . '/../helpers/upload_dokumen.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ======================= CEK LOGIN ======================= */
if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* ======================= AMBIL DATA FORM ======================= */
$id_invoice        = (int) ($_POST['id_invoice'] ?? 0);
$tanggal_bayar     = $_POST['tanggal_bayar'] ?? '';
$status_pembayaran = $_POST['status_pembayaran'] ?? '';
$metode            = $_POST['metode'] ?? '';
$no_invoice        = $_POST['nomor_invoice'] ?? '';
$vendor            = $_POST['vendor_nama'] ?? '';

if ($id_invoice <= 0 || !$tanggal_bayar || !$status_pembayaran || !$metode) {
    die("Data pembayaran tidak lengkap!");
}

/* ======================= AMBIL DATA INVOICE ======================= */
$stmtInv = $conn->prepare("SELECT total FROM invoice WHERE id_invoice=?");
$stmtInv->bind_param("i", $id_invoice);
$stmtInv->execute();
$resultInv = $stmtInv->get_result();
$inv = $resultInv->fetch_assoc();

if (!$inv) {
    die("Invoice tidak ditemukan!");
}

$totalInvoice = (float)$inv['total'];

/* ======================= HITUNG JUMLAH BAYAR ======================= */
if ($status_pembayaran === 'DP') {
    $jumlah = $totalInvoice * 0.5;
} elseif ($status_pembayaran === 'Lunas') {
    $jumlah = $totalInvoice;
} else {
    die("Status pembayaran tidak valid!");
}

/* ======================= UPLOAD BUKTI ======================= */
$bukti = null;

if (!empty($_FILES['bukti']['name'])) {

    $tglFormat = date('Ymd', strtotime($tanggal_bayar));

    $upload = uploadDokumen(
        $_FILES['bukti'],
        __DIR__ . '/../uploads/bukti',
        'BuktiPembayaran_' . $no_invoice . '_' . $vendor . '_' . $tglFormat
    );

    if (!$upload['status']) {
        die($upload['msg']);
    }

    $bukti = $upload['file'];
}

/* ======================= SIMPAN PEMBAYARAN ======================= */
$stmt = $conn->prepare("
    INSERT INTO pembayaran 
    (id_invoice, tanggal_bayar, jumlah, metode, bukti)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isdss",
    $id_invoice,
    $tanggal_bayar,
    $jumlah,
    $metode,
    $bukti
);

if (!$stmt->execute()) {
    die("Insert pembayaran gagal: " . $stmt->error);
}

/* ======================= HITUNG TOTAL BAYAR ======================= */
$stmtTotal = $conn->prepare("
    SELECT COALESCE(SUM(jumlah),0) AS total_bayar
    FROM pembayaran
    WHERE id_invoice=?
");

$stmtTotal->bind_param("i", $id_invoice);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$dataTotal = $resultTotal->fetch_assoc();

$totalBayar = (float)$dataTotal['total_bayar'];

/* ======================= STATUS INVOICE ======================= */
$status       = 'Belum Bayar';
$dp_nominal   = 0;
$dp_persen    = 0;
$sisa_nominal = $totalInvoice;
$sisa_persen  = 100;

if ($totalBayar > 0 && $totalBayar < $totalInvoice) {

    $status       = 'DP';
    $dp_nominal   = $totalBayar;
    $dp_persen    = round(($totalBayar / $totalInvoice) * 100);
    $sisa_nominal = $totalInvoice - $totalBayar;
    $sisa_persen  = 100 - $dp_persen;

} elseif ($totalBayar >= $totalInvoice) {

    $status       = 'Lunas';
    $sisa_nominal = 0;
    $sisa_persen  = 0;
}

/* ======================= STATUS ARSIP (OTOMATIS SAAT LUNAS) ======================= */
$status_arsip  = 'Aktif';
$tanggal_arsip = null;

if (strtolower(trim($status)) === 'lunas') {
    $status_arsip  = 'Arsip';
    $tanggal_arsip = date('Y-m-d');
}

/* ======================= UPDATE INVOICE ======================= */
$stmtUpdate = $conn->prepare("
    UPDATE invoice SET
        status=?,
        dp_nominal=?,
        dp_persen=?,
        sisa_nominal=?,
        sisa_persen=?,
        status_arsip=?,
        tanggal_arsip=?
    WHERE id_invoice=?
");

$stmtUpdate->bind_param(
    "siddissi",
    $status,
    $dp_nominal,
    $dp_persen,
    $sisa_nominal,
    $sisa_persen,
    $status_arsip,
    $tanggal_arsip,
    $id_invoice
);

if (!$stmtUpdate->execute()) {
    die("Update invoice gagal: " . $stmtUpdate->error);
}

/* ======================= LOG ======================= */
logAktivitas(
    $conn,
    $_SESSION['user_id'],
    'Tambah Pembayaran',
    "Invoice $no_invoice → $status"
);

header("Location: index.php?success=1");
exit;