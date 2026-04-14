<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_POST['id_invoice']) || !isset($_FILES['dokumen'])) {
    header("Location: index.php");
    exit;
}

$id_invoice = mysqli_real_escape_string($conn, $_POST['id_invoice']);
$file = $_FILES['dokumen'];

/* VALIDASI */
if ($file['error'] !== UPLOAD_ERR_OK) {
    header("Location: detail.php?id=$id_invoice&error=upload");
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    header("Location: detail.php?id=$id_invoice&error=type");
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    header("Location: detail.php?id=$id_invoice&error=size");
    exit;
}

/* SIMPAN FILE */
$nama_file = 'ARSIP_INV_' . $id_invoice . '_' . time() . '.pdf';
$folder = __DIR__ . '/../uploads/arsip/';

if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

move_uploaded_file($file['tmp_name'], $folder . $nama_file);

/* INSERT (TIDAK MENYENTUH FILE LAMA) */
mysqli_query($conn, "
    INSERT INTO invoice_dokumen (id_invoice, nama_file)
    VALUES ('$id_invoice', '$nama_file')
");

header("Location: detail.php?id=$id_invoice&success=1");
exit;
