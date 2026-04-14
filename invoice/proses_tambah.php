<?php
require_once '../config/database.php';
require_once __DIR__ . '/../config/log.php';

if (isset($_POST['simpan'])) {

    $nomor_invoice   = $_POST['nomor_invoice'];
    $vendor          = $_POST['vendor'];
    $tanggal_invoice = $_POST['tanggal_invoice'];
    $total           = $_POST['total'];

    /* ===============================
       LOGIKA DP & STATUS (FINAL)
    =============================== */

    // Ambil DP persen (boleh kosong)
    $dp_persen = $_POST['dp_persen'] ?? '';

    // Normalisasi nilai DP
    if ($dp_persen === '' || $dp_persen === null) {
        // Tidak isi DP → dianggap LUNAS
        $dp_persen = 100;
    } else {
        $dp_persen = (int)$dp_persen;
    }

    // Tentukan status
    if ($dp_persen >= 100) {
        $status    = 'Lunas';
        $dp_persen = 100;
    }
    elseif ($dp_persen > 0 && $dp_persen < 100) {
        $status = 'DP';
    }
    else {
        $status    = 'Belum Bayar';
        $dp_persen = 0;
    }

    /* ===============================
       UPLOAD FILE
    =============================== */

    $file_name = $_FILES['file']['name'];
    $tmp       = $_FILES['file']['tmp_name'];
    $path      = '../uploads/' . $file_name;

    move_uploaded_file($tmp, $path);

    /* ===============================
       INSERT DATABASE
    =============================== */

    $sql = "INSERT INTO invoice (
                nomor_invoice,
                nama_vendor,
                tanggal_invoice,
                total,
                dp_persen,
                status,
                file_invoice
            ) VALUES (
                '$nomor_invoice',
                '$vendor',
                '$tanggal_invoice',
                '$total',
                '$dp_persen',
                '$status',
                '$file_name'
            )";

    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit;
}