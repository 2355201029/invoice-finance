<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

/* 🔒 CEK APAKAH SUDAH ADA PEMBAYARAN */
$cek = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM pembayaran 
    WHERE id_invoice = '$id'
");
$data = mysqli_fetch_assoc($cek);

if ($data['total'] > 0) {
    echo "<script>
        alert('Invoice tidak bisa dihapus karena sudah memiliki pembayaran!');
        window.location='index.php';
    </script>";
    exit;
}

/* 🗑️ HAPUS INVOICE */
$hapus = mysqli_query($conn, "
    DELETE FROM invoice 
    WHERE id_invoice = '$id'
");

if ($hapus) {
    echo "<script>
        alert('Invoice berhasil dihapus');
        window.location='index.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal menghapus invoice');
        window.location='index.php';
    </script>";
}