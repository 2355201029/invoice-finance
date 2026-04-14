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
   AMBIL INPUT
===================== */
$id_invoice      = (int) $_POST['id_invoice'];
$nomor_invoice   = mysqli_real_escape_string($conn, $_POST['nomor_invoice']);
$id_vendor       = (int) $_POST['id_vendor'];
$tanggal_invoice = $_POST['tanggal_invoice'];

/* Bersihkan format rupiah */
$total = preg_replace('/[^0-9]/', '', $_POST['total']);
$total = (float) $total;

$status    = $_POST['status'];
$dp_persen = isset($_POST['dp_persen']) && $_POST['dp_persen'] !== ''
    ? (int) $_POST['dp_persen']
    : 0;

/* =====================
   LOGIKA STATUS
===================== */
$dp_nominal   = 0;
$sisa_nominal = $total;
$sisa_persen  = 100;

if ($status === 'DP') {

    if ($dp_persen < 1 || $dp_persen >= 100) {
        $dp_persen = 0;
    }

    $dp_nominal   = round($total * ($dp_persen / 100), 2);
    $sisa_nominal = round($total - $dp_nominal, 2);
    $sisa_persen  = 100 - $dp_persen;

} elseif ($status === 'Lunas') {

    $dp_persen   = 100;
    $dp_nominal  = $total;
    $sisa_nominal= 0;
    $sisa_persen = 0;

} else { // Belum Bayar

    $status       = 'Belum Bayar';
    $dp_persen   = 0;
    $dp_nominal  = 0;
    $sisa_nominal= $total;
    $sisa_persen = 100;
}

/* =====================
   UPDATE DATABASE
===================== */
$stmt = $conn->prepare("
    UPDATE invoice SET
        nomor_invoice   = ?,
        id_vendor       = ?,
        tanggal_invoice = ?,
        total           = ?,
        dp_persen       = ?,
        dp_nominal      = ?,
        sisa_persen     = ?,
        sisa_nominal    = ?,
        status          = ?
    WHERE id_invoice = ?
");

$stmt->bind_param(
    "sisdididsi",
    $nomor_invoice,
    $id_vendor,
    $tanggal_invoice,
    $total,
    $dp_persen,
    $dp_nominal,
    $sisa_persen,
    $sisa_nominal,
    $status,
    $id_invoice
);

if ($stmt->execute()) {

    /* =====================
       LOG AKTIVITAS
    ===================== */
    logAktivitas(
        $conn,
        $_SESSION['user_id'],
        'Edit Invoice',
        'Invoice ' . $nomor_invoice . ' diperbarui'
    );

    header("Location: index.php?success=update");
    exit;

} else {

    logAktivitas(
        $conn,
        $_SESSION['user_id'],
        'Gagal Edit Invoice',
        'Invoice ' . $nomor_invoice
    );

    header("Location: edit.php?id=" . $id_invoice . "&error=1");
    exit;
}