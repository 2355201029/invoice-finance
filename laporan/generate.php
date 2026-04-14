<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$bulan = isset($_POST['bulan']) ? (int)$_POST['bulan'] : date('m');
$tahun = isset($_POST['tahun']) ? (int)$_POST['tahun'] : date('Y');

// ambil data invoice sesuai periode
$queryInvoice = "
    SELECT id_invoice, total, status, tanggal_invoice
    FROM invoice
    WHERE MONTH(tanggal_invoice) = '$bulan'
    AND YEAR(tanggal_invoice) = '$tahun'
";

$resultInvoice = mysqli_query($conn, $queryInvoice);

if (!$resultInvoice) {
    die("Query invoice gagal: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($resultInvoice)) {

    $id_invoice = $row['id_invoice'];
    $total = (float)$row['total'];
    $status_invoice = strtolower(trim($row['status']));

    // tentukan status laporan
    if ($status_invoice === 'lunas') {
        $status_laporan = 'Lunas';
    } else {
        // untuk DP atau belum bayar
        $status_laporan = 'Proses';
    }

    // cek apakah sudah ada di laporan
    $cek = mysqli_query($conn, "
        SELECT id_laporan
        FROM laporan
        WHERE id_invoice = '$id_invoice'
        AND periode_bulan = '$bulan'
        AND periode_tahun = '$tahun'
    ");

    if (!$cek) {
        die("Query cek laporan gagal: " . mysqli_error($conn));
    }

    // jika belum ada → insert
    if (mysqli_num_rows($cek) == 0) {

        $insert = mysqli_query($conn, "
            INSERT INTO laporan
            (id_invoice, periode_bulan, periode_tahun, total, status)
            VALUES
            (
                '$id_invoice',
                '$bulan',
                '$tahun',
                '$total',
                '$status_laporan'
            )
        ");

        if (!$insert) {
            die("Insert laporan gagal: " . mysqli_error($conn));
        }
    } 
    // jika sudah ada → update status
    else {
        mysqli_query($conn, "
            UPDATE laporan
            SET status = '$status_laporan',
                total = '$total'
            WHERE id_invoice = '$id_invoice'
            AND periode_bulan = '$bulan'
            AND periode_tahun = '$tahun'
        ");
    }
}

// redirect kembali ke halaman laporan
header("Location: index.php?bulan=$bulan&tahun=$tahun&generate=success");
exit;