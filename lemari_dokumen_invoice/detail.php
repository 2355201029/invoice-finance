<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID Lemari tidak valid");
}

$query = mysqli_query($conn, "
    SELECT 
        l.*,
        i.nomor_invoice,
        i.total,
        i.status,
        i.file_invoice,
        v.nama_vendor
    FROM lemari_dokumen_invoice l
    JOIN invoice i ON l.id_invoice = i.id_invoice
    JOIN vendor v ON i.id_vendor = v.id_vendor
    WHERE l.id_lemari = $id
    LIMIT 1
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data lemari tidak ditemukan");
}
?>