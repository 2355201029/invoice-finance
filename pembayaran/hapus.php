<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

/* =====================
CEK LOGIN
===================== */
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id_pembayaran = (int) $_GET['id'];

/* =====================
    TRANSACTION
===================== */
$conn->begin_transaction();

try {

    /* =====================
    AMBIL DATA PEMBAYARAN
    ===================== */
    $stmt = $conn->prepare("
        SELECT id_invoice, bukti
        FROM pembayaran
        WHERE id_pembayaran = ?
    ");
    $stmt->bind_param("i", $id_pembayaran);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if (!$data) {
        throw new Exception('Data pembayaran tidak ditemukan');
    }

    $id_invoice = $data['id_invoice'];

    /* =====================
       HAPUS FILE BUKTI
    ===================== */
    if (!empty($data['bukti'])) {
        $file = __DIR__ . '/../uploads/bukti/' . $data['bukti'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /* =====================
       HAPUS PEMBAYARAN
    ===================== */
    $stmt = $conn->prepare("
        DELETE FROM pembayaran
        WHERE id_pembayaran = ?
    ");
    $stmt->bind_param("i", $id_pembayaran);
    $stmt->execute();

    /* =====================
       HITUNG ULANG PEMBAYARAN
    ===================== */
    $stmt = $conn->prepare("
        SELECT IFNULL(SUM(jumlah),0) AS total_bayar
        FROM pembayaran
        WHERE id_invoice = ?
    ");
    $stmt->bind_param("i", $id_invoice);
    $stmt->execute();
    $bayar = $stmt->get_result()->fetch_assoc();

    /* =====================
       AMBIL TOTAL INVOICE
    ===================== */
    $stmt = $conn->prepare("
        SELECT total
        FROM invoice
        WHERE id_invoice = ?
    ");
    $stmt->bind_param("i", $id_invoice);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();

    if (!$invoice) {
        throw new Exception('Invoice tidak ditemukan');
    }

    $totalInvoice = (float) $invoice['total'];
    $totalBayar   = (float) $bayar['total_bayar'];

    /* =====================
       TENTUKAN STATUS BARU
    ===================== */
    if ($totalBayar >= $totalInvoice) {
        $status        = 'Lunas';
        $dp_nominal    = $totalInvoice;
        $dp_persen     = 100;
        $sisa_nominal  = 0;
        $sisa_persen   = 0;

    } elseif ($totalBayar > 0) {
        $status        = 'DP';
        $dp_nominal    = $totalBayar;
        $dp_persen     = round(($totalBayar / $totalInvoice) * 100, 2);
        $sisa_nominal  = $totalInvoice - $totalBayar;
        $sisa_persen   = 100 - $dp_persen;

    } else {
        $status        = 'Belum Bayar';
        $dp_nominal    = 0;
        $dp_persen     = 0;
        $sisa_nominal  = $totalInvoice;
        $sisa_persen   = 100;
    }

    /* =====================
       UPDATE INVOICE
    ===================== */
    $stmt = $conn->prepare("
        UPDATE invoice SET
            status        = ?,
            dp_nominal    = ?,
            dp_persen     = ?,
            sisa_nominal  = ?,
            sisa_persen   = ?
        WHERE id_invoice = ?
    ");
    $stmt->bind_param(
        "sddddi",
        $status,
        $dp_nominal,
        $dp_persen,
        $sisa_nominal,
        $sisa_persen,
        $id_invoice
    );
    $stmt->execute();

    /* =====================
       HAPUS ARSIP JIKA TIDAK LUNAS
    ===================== */
    if ($status !== 'Lunas') {
        $stmt = $conn->prepare("
            DELETE FROM arsip_invoice
            WHERE id_invoice = ?
        ");
        $stmt->bind_param("i", $id_invoice);
        $stmt->execute();
    }

    /* =====================
       LOG AKTIVITAS
    ===================== */
    logAktivitas(
        $conn,
        $_SESSION['user_id'],
        'Hapus Pembayaran',
        'Hapus pembayaran ID ' . $id_pembayaran . ' (Invoice ID ' . $id_invoice . ')'
    );

    $conn->commit();

    header("Location: index.php?hapus=success");
    exit;

} catch (Exception $e) {

    $conn->rollback();

    logAktivitas(
        $conn,
        $_SESSION['user_id'],
        'Gagal Hapus Pembayaran',
        'ID Pembayaran ' . $id_pembayaran
    );

    header("Location: index.php?hapus=gagal");
    exit;
}