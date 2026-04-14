<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$currentPage = $_SERVER['PHP_SELF'];
$role = $_SESSION['role'];

/* ===============================
   QUERY PER BANK (JOIN FIX)
================================ */

$qBNI = mysqli_query($conn,"
    SELECT 
        l.*,
        i.nomor_invoice,
        v.nama_vendor
    FROM lemari_dokumen_invoice l
    INNER JOIN invoice i ON l.id_invoice = i.id_invoice
    INNER JOIN vendor v ON i.id_vendor = v.id_vendor
    WHERE l.nama_bank = 'BNI'
    ORDER BY l.id_lemari DESC
");

$qCIMB = mysqli_query($conn,"
    SELECT 
        l.*,
        i.nomor_invoice,
        v.nama_vendor
    FROM lemari_dokumen_invoice l
    INNER JOIN invoice i ON l.id_invoice = i.id_invoice
    INNER JOIN vendor v ON i.id_vendor = v.id_vendor
    WHERE l.nama_bank = 'CIMB'
    ORDER BY l.id_lemari DESC
");

$qMANDIRI = mysqli_query($conn,"
    SELECT 
        l.*,
        i.nomor_invoice,
        v.nama_vendor
    FROM lemari_dokumen_invoice l
    INNER JOIN invoice i ON l.id_invoice = i.id_invoice
    INNER JOIN vendor v ON i.id_vendor = v.id_vendor
    WHERE l.nama_bank = 'MANDIRI'
    ORDER BY l.id_lemari DESC
");

if(!$qBNI || !$qCIMB || !$qMANDIRI){
    die("Query Error: " . mysqli_error($conn));
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Invoice | Sistem Invoice</title>

    <!-- DATATABLE CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">


    <style>
    :root {
        --primary: #2f5bea;
        --secondary: #4f46e5;
        --sidebar: #2b3a8f;
        --sidebar-dark: #233070;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', sans-serif
    }

    body {
        background: #f4f7ff
    }

    /* SIDEBAR */
    .main-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 260px;
        height: 100vh;
        background: linear-gradient(180deg, var(--sidebar), var(--sidebar-dark));
        color: #fff
    }

    .brand-link {
        display: block;
        padding: 22px;
        font-size: 20px;
        font-weight: 700;
        text-align: center;
        background: rgba(255, 255, 255, .08)
    }

    .sidebar {
        padding: 20px 15px
    }

    .sidebar ul {
        list-style: none
    }

    .sidebar ul li a {
        display: block;
        padding: 12px 18px;
        margin-bottom: 10px;
        color: #e0e7ff;
        text-decoration: none;
        border-radius: 10px;
        transition: .3s
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
        background: rgba(255, 255, 255, .15);
        transform: translateX(6px)
    }

    /* MAIN */
    .main-content {
        margin-left: 260px;
        padding: 28px;
    }

    /* HEADER */
    .header {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: #fff;
        padding: 28px 34px;
        border-radius: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 15px 35px rgba(0, 0, 0, .18)
    }

    .logos img {
        height: 50px;
        margin-left: 14px;
        background: #fff;
        padding: 6px 10px;
        border-radius: 12px
    }

    /* TABLE */
    .table-card {
        margin-top: 35px;
        background: #fff;
        border-radius: 18px;
        padding: 25px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, .08)
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px
    }

    .btn-add {
        background: linear-gradient(135deg, #4f46e5, #2f5bea);
        color: #fff;
        padding: 10px 18px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse
    }

    .invoice-table thead {
        background: #f1f5f9
    }

    .invoice-table th,
    .invoice-table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px
    }

    .invoice-table tr:hover {
        background: #f9fafb
    }

    .price {
        font-weight: 600;
        color: #1f2937
    }

    /* ===== CARD ===== */
    .card-arsip {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        max-width: 100%;
    }

    .card-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
    }

    .card-header p {
        margin-top: 4px;
        color: #64748b;
        font-size: 14px;
    }

    /* ===== TABLE ===== */
    .table-responsive {
        overflow-x: auto;
        margin-top: 20px;
    }

    .table-arsip {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .table-arsip thead th {
        text-align: left;
        font-size: 13px;
        color: #475569;
        padding: 12px;
    }

    .table-arsip tbody tr {
        background: #f8fafc;
        transition: all 0.3s ease;
    }

    .table-arsip tbody tr:hover {
        background: #eef2ff;
        transform: scale(1.005);
    }

    .table-arsip td {
        padding: 14px 12px;
        font-size: 14px;
        color: #334155;
    }

    .table-arsip tbody tr td:first-child {
        border-radius: 12px 0 0 12px;
    }

    .table-arsip tbody tr td:last-child {
        border-radius: 0 12px 12px 0;
    }

    /* ===== BADGE ===== */
    .badge-success {
        background: #dcfce7;
        color: #166534;
        padding: 5px 14px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 20px;
    }

    /* ===== TEXT ===== */
    .invoice-number {
        font-weight: 600;
        color: #2563eb;
    }

    .text-right {
        text-align: right;
    }

    .empty-data {
        text-align: center;
        padding: 30px;
        color: #94a3b8;
        font-size: 14px;
    }


    /* BADGE */
    .badge {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600
    }

    .success {
        background: #dcfce7;
        color: #166534
    }

    .warning {
        background: #fef3c7;
        color: #92400e
    }

    .danger {
        background: #fee2e2;
        color: #991b1b
    }

    /* BUTTON */
    .aksi a {
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        text-decoration: none;
        margin-right: 4px
    }

    .btn-view {
        background: #e0f2fe;
        color: #0369a1
    }

    .btn-edit {
        background: #ede9fe;
        color: #5b21b6
    }

    .btn-delete {
        background: #fee2e2;
        color: #991b1b
    }

    .empty {
        text-align: center;
        color: #6b7280;
        padding: 20px
    }

    /* ===== SEARCH ARSIP ===== */
    .table-action {
        margin: 18px 0;
        display: flex;
        justify-content: flex-end;
    }

    #searchArsip {
        width: 280px;
        padding: 12px 18px 12px 44px;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        font-size: 14px;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.868-3.834zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 16px center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        transition: all .3s ease;
    }

    #searchArsip::placeholder {
        color: #9ca3af;
    }

    #searchArsip:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, .18),
            0 12px 30px rgba(0, 0, 0, .12);
        transform: translateY(-1px);
    }

    #searchArsip:hover {
        border-color: #c7d2fe;
    }

    @media(max-width:768px) {
        .table-action {
            justify-content: center;
        }

        #searchArsip {
            width: 100%;
        }
    }

    .hide-row {
        display: none !important;
    }

    /* RAPATKAN HEADER TABLE */
    .table-header h3 {
        font-weight: 600;
        color: #1f2937;
    }

    .footer {
        margin-top: 40px;
        padding: 18px 24px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .06);

        display: flex;
        flex-direction: column;
        /* ⬅ vertikal */
        align-items: center;
        /* ⬅ tengah horizontal */
        justify-content: center;
        /* ⬅ tengah vertikal */
        gap: 6px;

        color: #6b7280;
        font-size: 14px;
        text-align: center;
        /* ⬅ teks tengah */
    }

    .footer span {
        font-weight: 600;
        color: var(--primary);
    }

    .footer .version {
        font-size: 13px;
        color: #9ca3af;
    }

    /* ================= HEADER LOGOUT ================= */
    .header-actions {
        display: flex;
        align-items: center;
    }

    .btn-logout-header {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .18);
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all .3s ease;
        backdrop-filter: blur(6px);
    }

    .btn-logout-header:hover {
        background: rgba(255, 255, 255, .32);
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .2);
    }

    .logout-icon {
        font-size: 16px;
        line-height: 1;
    }

    .logout-text {
        display: inline-block;
    }

    .btn-logout-header {
        background: rgba(239, 68, 68, .18);
    }

    .btn-logout-header:hover {
        background: rgba(239, 68, 68, .35);
    }

    /* ANIMATION */
    @keyframes fadeDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Base button */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;

        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;

        border-radius: 8px;
        text-decoration: none;
        border: none;

        transition: all 0.25s ease;
    }

    /* DETAIL – biru informatif */
    .btn-info {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
    }

    .btn-info:hover {
        background: #2563eb;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.35);
    }

    /* Klik */
    .btn:active {
        transform: scale(0.96);
    }

    /* Badge Base */
    .badge {
        display: inline-block;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 999px;
        text-align: center;
    }

    /* LUNAS */
    .badge-success {
        color: #166534;
        background: rgba(34, 197, 94, 0.18);
        border: 1px solid rgba(34, 197, 94, 0.4);
    }

    /* ================= LEMARI DOKUMEN INVOICE ================= */
    body {
        background: #f4f7ff;
        font-family: 'Segoe UI', sans-serif
    }

    .main-content {
        margin-left: 260px;
        padding: 28px
    }

    .table-card {
        margin-top: 35px;
        background: #fff;
        border-radius: 18px;
        padding: 25px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, .08)
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #f1f5f9;
    }

    th,
    td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    .badge {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600
    }

    .bank {
        background: #e0f2fe;
        color: #0369a1
    }

    .btn-detail {
        padding: 6px 12px;
        border-radius: 8px;
        background: #eef2ff;
        color: #3730a3;
        text-decoration: none;
        font-size: 13px;
    }

    .btn-danger {
        background: #fee2e2;
        color: #991b1b;
        padding: 6px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
    }

    h2 {
        margin-bottom: 10px
    }

    /* ================= SEARCH & BUTTON LEMARI ================= */

    .table-action {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
    }

    /* ==============================
   BUTTON TAMBAH LEMARI
============================== */

    .btn-tambah {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 22px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #1e40af);
        box-shadow: 0 10px 25px rgba(37, 99, 235, .35);
        transition: all .3s ease;
        position: relative;
        overflow: hidden;
    }

    /* Icon */
    .btn-tambah .icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        background: rgba(255, 255, 255, .2);
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
    }

    /* Hover Effect */
    .btn-tambah:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(37, 99, 235, .45);
    }

    /* Active */
    .btn-tambah:active {
        transform: translateY(0);
        box-shadow: 0 6px 18px rgba(37, 99, 235, .3);
    }

    /* Responsive */
    @media(max-width:768px) {
        .btn-tambah {
            width: 100%;
            justify-content: center;
        }
    }


    @media(max-width:768px) {
        .table-action {
            flex-direction: column;
            align-items: stretch;
        }

        #searchLemari {
            width: 100%;
        }
    }


    @media(max-width:768px) {
        .search-box {
            justify-content: center;
        }

        .search-box input {
            width: 100%;
        }
    }

    /* ==============================
   CONTAINER
============================== */
    .container {
        max-width: 1400px;
        margin: auto;
        padding: 30px;
    }

    /* ===============================
   BUTTON TAMBAH LEMARI
=================================*/
    .btn-tambah-lemari {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg, #4f46e5, #2f5bea);
        box-shadow: 0 6px 18px rgba(79, 70, 229, .35);
        transition: all .3s ease;
    }

    .btn-tambah-lemari .icon {
        font-size: 16px;
        font-weight: bold;
    }

    .btn-tambah-lemari:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(79, 70, 229, .45);
    }

    .btn-tambah-lemari:active {
        transform: scale(.96);
    }

    /* ==============================
   CARD STYLE
============================== */
    .table-card {
        background: #ffffff;
        padding: 25px;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        margin-bottom: 40px;
        transition: all 0.3s ease;
    }

    .table-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    }

    .table-card h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        border-left: 5px solid #2563eb;
        padding-left: 10px;
    }

    /* ==============================
   RESPONSIVE WRAPPER
============================== */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    /* ==============================
   TABLE
============================== */
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1100px;
    }

    thead {
        background: linear-gradient(135deg, #2563eb, #1e40af);
    }

    thead th {
        color: #ffffff;
        padding: 14px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tbody td {
        padding: 12px;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        color: #374151;
    }

    tbody tr {
        transition: all 0.2s ease;
    }

    tbody tr:hover {
        background: #f8fafc;
    }

    /* ==============================
   BADGE BANK
============================== */
    .badge.bank {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        color: #ffffff;
    }

    /* Warna berbeda tiap bank */
    .badge.bank:contains("CIMB") {
        background: #dc2626;
    }

    .badge.bank:contains("MANDIRI") {
        background: #f59e0b;
    }

    .badge.bank:contains("BNI") {
        background: #ea580c;
    }

    /* fallback */
    .badge.bank {
        background: #2563eb;
    }

    /* ==============================
   BUTTON
============================== */
    .btn {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 12px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-info {
        background: #2563eb;
        color: #ffffff;
    }

    .btn-info:hover {
        background: #1e40af;
    }

    /* ==============================
   EMPTY DATA
============================== */
    .empty {
        text-align: center;
        padding: 20px;
        font-style: italic;
        color: #9ca3af;
    }

    /* ==============================
   MOBILE RESPONSIVE
============================== */
    @media(max-width:768px) {

        .container {
            padding: 15px;
        }

        .table-card {
            padding: 15px;
            border-radius: 14px;
        }

        thead th,
        tbody td {
            padding: 8px;
            font-size: 11px;
            white-space: nowrap;
        }

        .btn {
            font-size: 10px;
            padding: 5px 8px;
        }
    }

    /* ================= SEARCH LEMARI ================= */
    #searchLemari {
        width: 340px;
        padding: 14px 18px 14px 46px;
        border-radius: 18px;
        border: 1px solid #e5e7eb;
        font-size: 14px;
        background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.868-3.834zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 18px center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        transition: all .3s ease;
    }

    #searchLemari::placeholder {
        color: #9ca3af;
    }

    #searchLemari:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow:
            0 0 0 4px rgba(37, 99, 235, .15),
            0 15px 35px rgba(0, 0, 0, .12);
        transform: translateY(-2px);
    }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="main-sidebar">
        <a class="brand-link">
            <div class="logos">
                <img src="../assets/img/astra.jpeg">
                <img src="../assets/img/klk.jpeg">
            </div>PT KREASIJAYA ADHIKARYA
        </a>
        <div class="sidebar">
            <ul class="menu">

                <li>
                    <a href="../dashboard/index.php"
                        class="<?= strpos($currentPage, '/dashboard/') !== false ? 'active' : '' ?>">
                        Dashboard
                    </a>
                </li>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li>
                    <a href="../verifikasi/index.php"
                        class="<?= strpos($currentPage, '/verifikasi/') !== false ? 'active' : '' ?>">
                        Verifikasi & Approve User
                    </a>
                </li>
                <?php endif; ?>

                <li>
                    <a href="../invoice/index.php"
                        class="<?= strpos($currentPage, '/invoice/') !== false ? 'active' : '' ?>">
                        Invoice
                    </a>
                </li>

                <li>
                    <a href="../pembayaran/index.php"
                        class="<?= strpos($currentPage, '/pembayaran/') !== false ? 'active' : '' ?>">
                        Pembayaran
                    </a>
                </li>

                <li>
                    <a href="../vendor/index.php"
                        class="<?= strpos($currentPage, '/vendor/') !== false ? 'active' : '' ?>">
                        Vendor
                    </a>
                </li>

                <li>
                    <a href="../laporan/index.php"
                        class="<?= strpos($currentPage, '/laporan/') !== false ? 'active' : '' ?>">
                        Laporan
                    </a>
                </li>

                <li>
                    <a href="../arsip/index.php"
                        class="<?= strpos($currentPage, '/arsip/') !== false ? 'active' : '' ?>">
                        Arsip
                    </a>
                </li>

                <li>
                    <a href="../lemari_dokumen_invoice/index.php"
                        class="<?= strpos($currentPage, '/arsip/') !== false ? 'active' : '' ?>">
                        Lemari Dokumen Invoice
                    </a>
                </li>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- ✅ LOG AKTIVITAS (FIXED) -->
                <li>
                    <a href="../log/index.php" class="<?= strpos($currentPage, '/log/') !== false ? 'active' : '' ?>">
                        Log Aktivitas
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- HEADER -->
        <div class="header">
            <div>
                <h2>SISTEM INVOICE</h2>
            </div>

            <div id="clock" data-server-time="<?= date('Y-m-d H:i:s') ?>"
                style="margin-top:6px;font-size:14px;opacity:.9;font-weight:500;">
            </div>

            <!-- USER DROPDOWN -->
            <div class="header-actions">
                <a href="../auth/logout_proses.php" class="btn-logout-header"
                    onclick="return confirm('Yakin ingin logout?')">
                    <span class="logout-icon">⎋</span>
                    <span class="logout-text">Logout</span>
                </a>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2>Data Lemari Dokumen Invoice</h2>

                <div class="table-action">
                    <?php if ($role === 'admin' || $role === 'user'): ?>
                    <a href="tambah.php" class="btn-tambah-lemari">
                        <span class="icon">＋</span>
                        Tambah Lemari Dokumen
                    </a>


                    <?php endif; ?>

                    <input type="text" id="searchLemari" placeholder="Cari kode, vendor, SAP, dll..."
                        autocomplete="off">
                </div>
            </div>

            <?php
            function getDataBank($conn, $bank){
                $sql = "
                    SELECT 
                        l.*,
                        i.nomor_invoice,
                        v.nama_vendor
                    FROM lemari_dokumen_invoice l
                    LEFT JOIN invoice i ON l.id_invoice = i.id_invoice
                    LEFT JOIN vendor v ON i.id_vendor = v.id_vendor
                    WHERE l.nama_bank = '$bank'
                    ORDER BY l.tanggal DESC
                ";
            
                $query = mysqli_query($conn, $sql);
            
                if(!$query){
                    die("Query Error: " . mysqli_error($conn));
                }
            
                return $query;
            }
            
            $qBNI      = getDataBank($conn, 'BNI');
            $qCIMB     = getDataBank($conn, 'CIMB');
            $qMANDIRI  = getDataBank($conn, 'MANDIRI');
            ?>

            <!-- ================== BNI ================== -->
            <div class="table-responsive">
                <div class="table-card">
                    <h3>Dokumen Bank BNI</h3>
                    <table border="1" width="100%" cellpadding="5">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>KODE LEMARI</th>
                                <th>INVOICE</th>
                                <th>VENDOR</th>
                                <th>LEMARI</th>
                                <th>RAK</th>
                                <th>BANK</th>
                                <th>NOMOR SAP</th>
                                <th>TANGGAL ARSIP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php if(mysqli_num_rows($qBNI) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($qBNI)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['kode_lemari'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nomor_invoice'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_vendor'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['lemari_ke'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['rak_ke'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_bank'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nomor_sap'] ?? '-') ?></td>
                                <td>
                                    <?= !empty($row['tanggal']) 
                                ? date('d-m-Y H:i', strtotime($row['tanggal'])) 
                                : '-' ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9" class="empty">Tidak ada data BNI</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================== CIMB ================== -->
            <div class="table-responsive">
                <div class="table-card">
                    <h3>Dokumen Bank CIMB</h3>
                    <table border="1" width="100%" cellpadding="5">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>KODE LEMARI</th>
                                <th>INVOICE</th>
                                <th>VENDOR</th>
                                <th>LEMARI</th>
                                <th>RAK</th>
                                <th>BANK</th>
                                <th>NOMOR SAP</th>
                                <th>TANGGAL ARSIP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php if(mysqli_num_rows($qCIMB) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($qCIMB)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['kode_lemari'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nomor_invoice'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_vendor'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['lemari_ke'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['rak_ke'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_bank'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nomor_sap'] ?? '-') ?></td>
                                <td>
                                    <?= !empty($row['tanggal']) 
                                ? date('d-m-Y H:i', strtotime($row['tanggal'])) 
                                : '-' ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9" class="empty">Tidak ada data CIMB</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================== MANDIRI ================== -->
            <div class="table-responsive">
                <div class="table-card">
                    <h3>Dokumen Bank MANDIRI</h3>
                    <table border="1" width="100%" cellpadding="5">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>KODE LEMARI</th>
                                <th>INVOICE</th>
                                <th>VENDOR</th>
                                <th>LEMARI</th>
                                <th>RAK</th>
                                <th>BANK</th>
                                <th>NOMOR SAP</th>
                                <th>TANGGAL ARSIP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php if(mysqli_num_rows($qMANDIRI) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($qMANDIRI)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['kode_lemari'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nomor_invoice'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_vendor'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['lemari_ke'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['rak_ke'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_bank'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nomor_sap'] ?? '-') ?></td>
                                <td>
                                    <?= !empty($row['tanggal']) 
                                ? date('d-m-Y H:i', strtotime($row['tanggal'])) 
                                : '-' ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9" class="empty">Tidak ada data MANDIRI</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div>
                © <?= date('Y') ?> <span> <a href="https://www.klk.com.my/">PT KREASIJAYA ADHIKARYA</a></span>. All
                Rights Reserved
            </div>
            <div class="version">
                Sistem Invoice v1.0
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {

        const searchInput = document.getElementById("searchLemari");

        searchInput.addEventListener("keyup", function() {

            const keyword = this.value.toLowerCase().trim();

            document.querySelectorAll("table tbody tr").forEach(function(row) {

                const text = row.innerText.toLowerCase();

                if (text.includes(keyword)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }

            });

        });

    });
    </script>

    <script>
    const clockEl = document.getElementById('clock');

    // ambil waktu server dari PHP
    let serverTime = new Date(clockEl.dataset.serverTime.replace(' ', 'T'));

    function updateClock() {
        serverTime.setSeconds(serverTime.getSeconds() + 1);

        const tanggal = serverTime.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });

        const waktu = serverTime.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        clockEl.innerHTML = `${tanggal} • ${waktu} WIB`;
    }

    updateClock();
    setInterval(updateClock, 1000);
    </script>

</body>

</html>