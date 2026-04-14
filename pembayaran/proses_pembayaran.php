<?php
require_once __DIR__ . '/../config/database.php';

/* ============================= */
/* VALIDASI INPUT */
/* ============================= */

if (!isset($_POST['id_invoice']) || !isset($_POST['jumlah_bayar'])) {
    die("Data tidak lengkap");
}

$id_invoice   = intval($_POST['id_invoice']);
$jumlah_bayar = floatval($_POST['jumlah_bayar']);

if ($id_invoice <= 0 || $jumlah_bayar <= 0) {
    die("Data tidak valid");
}


/* ============================= */
/* 1️⃣ SIMPAN PEMBAYARAN */
/* ============================= */

$simpanPembayaran = mysqli_query($conn, "
    INSERT INTO pembayaran (id_invoice, jumlah_bayar)
    VALUES ('$id_invoice', '$jumlah_bayar')
");

if (!$simpanPembayaran) {
    die("Gagal simpan pembayaran: " . mysqli_error($conn));
}


/* ============================= */
/* 2️⃣ HITUNG TOTAL PEMBAYARAN */
/* ============================= */

$qTotal = mysqli_query($conn, "
    SELECT 
        i.total AS total_invoice,
        COALESCE(SUM(p.jumlah_bayar),0) AS total_bayar
    FROM invoice i
    LEFT JOIN pembayaran p ON p.id_invoice = i.id_invoice
    WHERE i.id_invoice='$id_invoice'
");

$data = mysqli_fetch_assoc($qTotal);

$total_invoice = floatval($data['total_invoice']);
$total_bayar   = floatval($data['total_bayar']);


/* ============================= */
/* 3️⃣ TENTUKAN STATUS */
/* ============================= */

if ($total_bayar >= $total_invoice) {

    $status_invoice = 'Lunas';

    /* ============================= */
    /* UPDATE INVOICE JADI LUNAS */
    /* ============================= */

    $updateInvoice = mysqli_query($conn, "
        UPDATE invoice 
        SET 
            status = 'Lunas',
            status_arsip = 'Arsip',
            tanggal_arsip = CURDATE()
        WHERE id_invoice = '$id_invoice'
    ");

    if (!$updateInvoice) {
        die("Gagal update invoice: " . mysqli_error($conn));
    }


    /* ============================= */
    /* 4️⃣ MASUK KE TABEL ARSIP */
/* ============================= */

    $cekArsip = mysqli_query($conn, "
        SELECT id_arsip 
        FROM arsip_invoice
        WHERE id_invoice='$id_invoice'
    ");

    if (mysqli_num_rows($cekArsip) == 0) {

        $simpanArsip = mysqli_query($conn, "
            INSERT INTO arsip_invoice (
                id_invoice,
                tanggal_arsip
            )
            VALUES (
                '$id_invoice',
                CURDATE()
            )
        ");

        if (!$simpanArsip) {
            die("Gagal simpan arsip: " . mysqli_error($conn));
        }
    }

} else {

    $status_invoice = 'DP';

    /* ============================= */
    /* UPDATE STATUS DP */
/* ============================= */

    $updateInvoice = mysqli_query($conn, "
        UPDATE invoice 
        SET status = 'DP'
        WHERE id_invoice='$id_invoice'
    ");

    if (!$updateInvoice) {
        die("Gagal update status DP: " . mysqli_error($conn));
    }
}


/* ============================= */
/* REDIRECT */
/* ============================= */

header("Location: ../invoice/index.php?status=success");
exit;
?>