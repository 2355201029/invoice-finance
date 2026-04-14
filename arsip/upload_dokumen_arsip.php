<?php
require_once __DIR__ . '/../config/database.php';

$id_invoice = $_POST['id_invoice'];
$file = $_FILES['dokumen'];

if ($file['error'] !== 0) {
    header("Location: detail.php?id=$id_invoice");
    exit;
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$nama_file = 'ARSIP_' . time() . '_' . rand(100,999) . '.' . $ext;

$folder = "../uploads/arsip/";
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

move_uploaded_file($file['tmp_name'], $folder . $nama_file);

/* SIMPAN TANPA MENGHAPUS FILE LAIN */
mysqli_query($conn, "
    INSERT INTO invoice_dokumen (id_invoice, nama_file)
    VALUES ('$id_invoice', '$nama_file')
");

header("Location: detail.php?id=$id_invoice");
exit;