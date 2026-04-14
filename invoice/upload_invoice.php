<?php
require_once '../config/database.php';

$id_invoice = $_POST['id_invoice'];

if (!isset($_FILES['invoice']) || $_FILES['invoice']['error'] !== 0) {
    die("File tidak valid");
}

$nama_file = time() . "_" . basename($_FILES['invoice']['name']);
$tmp       = $_FILES['invoice']['tmp_name'];
$folder    = "../uploads/invoice/";

if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

move_uploaded_file($tmp, $folder . $nama_file);

mysqli_query($conn, "
    UPDATE invoice 
    SET file_invoice = '$nama_file'
    WHERE id_invoice = '$id_invoice'
");

header("Location: detail.php?id=$id_invoice");
exit;
