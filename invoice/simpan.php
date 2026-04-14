<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

/* =====================
   CEK LOGIN
===================== */
if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =====================
   AMBIL DATA
===================== */
$nomor_invoice = $_POST['nomor_invoice'] ?? '';
$id_vendor     = $_POST['id_vendor'] ?? 0;
$total         = floatval($_POST['total'] ?? 0);

$dp_persen = isset($_POST['dp_persen']) && $_POST['dp_persen'] !== ''
    ? floatval($_POST['dp_persen'])
    : 0;

/* =====================
   VALIDASI
===================== */
if ($total <= 0) {
    die("Total tidak valid");
}

if ($dp_persen < 0 || $dp_persen > 100) {
    die("DP harus antara 0 - 100");
}

/* =====================
   HITUNG OTOMATIS
===================== */
$dp_nominal   = round(($dp_persen / 100) * $total);
$sisa_nominal = round($total - $dp_nominal);
$sisa_persen  = round(100 - $dp_persen, 2);

/* =====================
   STATUS + TANGGAL (LOGIKA FINAL)
===================== */
$tanggal_invoice = date('Y-m-d');
$tanggal_dp = null;
$tanggal_lunas = null;

if ($dp_persen == 0) {
    $status = 'Belum Bayar';

} elseif ($dp_persen > 0 && $dp_persen < 100) {
    $status = 'DP';
    $tanggal_dp = date('Y-m-d');

} elseif ($dp_persen == 100) {
    $status = 'Lunas';
    $tanggal_dp = date('Y-m-d');     // opsional
    $tanggal_lunas = date('Y-m-d');
}

/* =====================
   TRANSAKSI
===================== */
$conn->begin_transaction();

try {

    /* =====================
       INSERT INVOICE
    ===================== */
    $stmt = $conn->prepare("
        INSERT INTO invoice (
            nomor_invoice,
            id_vendor,
            tanggal_invoice,
            total,
            dp_nominal,
            dp_persen,
            sisa_nominal,
            sisa_persen,
            status,
            tanggal_dp,
            tanggal_lunas
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sisddddssss",
        $nomor_invoice,
        $id_vendor,
        $tanggal_invoice,
        $total,
        $dp_nominal,
        $dp_persen,
        $sisa_nominal,
        $sisa_persen,
        $status,
        $tanggal_dp,
        $tanggal_lunas
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal simpan invoice");
    }

    // ✅ ambil ID invoice
    $id_invoice = $conn->insert_id;

    $stmt->close();

    /* =====================
       INSERT ARSIP
    ===================== */
    $stmtArsip = $conn->prepare("
        INSERT INTO arsip_invoice (id_invoice, tanggal_arsip)
        VALUES (?, NOW())
    ");

    $stmtArsip->bind_param("i", $id_invoice);

    if (!$stmtArsip->execute()) {
        throw new Exception("Gagal simpan arsip");
    }

    $stmtArsip->close();

    /* =====================
       LOG AKTIVITAS
    ===================== */
    logAktivitas(
        $conn,
        $_SESSION['user_id'],
        "Tambah Invoice",
        "Invoice $nomor_invoice berhasil ditambahkan"
    );

    /* =====================
       COMMIT
    ===================== */
    $conn->commit();

    header("Location: index.php?success=1");
    exit;

} catch (Exception $e) {

    $conn->rollback();

    echo "<h3 style='color:red'>ERROR:</h3>";
    echo $e->getMessage();
    exit;
}
?>