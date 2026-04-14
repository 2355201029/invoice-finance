<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? '';

$q = mysqli_query($conn, "
    SELECT i.*, v.nama_vendor
    FROM invoice i
    LEFT JOIN vendor v ON i.id_vendor = v.id_vendor
    WHERE i.id_invoice = '$id'
");

$data = mysqli_fetch_assoc($q);
if (!$data) die('Data invoice tidak ditemukan');
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

    /* ACTION HEADER (TAMBAH + SEARCH) */
    .table-action {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    #searchVendor,
    #searchInvoice,
    #searchPembayaran {
        padding: 8px 14px;
        border: 1px solid #d1d5db;
        border-radius: 20px;
        outline: none;
        font-size: 14px;
        width: 220px;
        transition: all 0.2s ease;
    }

    #searchVendor:focus,
    #searchInvoice:focus,
    #searchPembayaran:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
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

    body {
        background: #f1f5f9;
        font-family: Segoe UI, Arial, sans-serif;
        padding: 40px;
    }

    .invoice-card {
        max-width: 720px;
        margin: auto;
        background: #ffffff;
        padding: 32px 36px;
        border-radius: 18px;
        box-shadow: 0 25px 45px rgba(0, 0, 0, .08);
    }

    h3 {
        margin: 0 0 22px;
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        padding-bottom: 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    /* === FORM STYLE === */
    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .form-value {
        width: 100%;
        padding: 12px 14px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        color: #111827;
    }

    .form-value.amount {
        font-weight: 700;
        font-size: 15px;
        color: #16a34a;
        background: #ecfdf5;
        border-color: #bbf7d0;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* === STATUS === */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 16px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff;
    }

    .status-badge.dp {
        background: linear-gradient(135deg, #fde047, #facc15);
        color: #78350f;
    }

    /* === DOKUMEN === */
    .doc-section {
        margin-top: 26px;
        padding-top: 22px;
        border-top: 1px dashed #e5e7eb;
    }

    .doc-title {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 14px;
    }

    .doc-actions {
        display: flex;
        gap: 12px;
    }

    .btn-view,
    .btn-dl {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #fff;
        transition: all .25s ease;
    }

    .btn-view {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }

    .btn-dl {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    .btn-view:hover,
    .btn-dl:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, .18);
    }

    .doc-empty {
        font-size: 13px;
        color: #9ca3af;
        font-style: italic;
    }

    /* === BACK BUTTON === */
    .back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 28px;
        padding: 10px 18px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        color: #374151;
        background: #f3f4f6;
        transition: all .25s ease;
    }

    .back:hover {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
        transform: translateX(-4px);
        box-shadow: 0 8px 22px rgba(79, 70, 229, .35);
    }

    .back::before {
        content: "←";
        font-size: 15px;
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
        </div><br><br>

        <div class="container">
            <div class="invoice-card">
                <h3>Detail Invoice</h3>

                <div class="form-group">
                    <label>Nomor Invoice</label>
                    <div class="form-value"><?= $data['nomor_invoice'] ?></div>
                </div>

                <div class="form-group">
                    <label>Vendor</label>
                    <div class="form-value"><?= $data['nama_vendor'] ?></div>
                </div>

                <div class="form-group">
                    <label>Tanggal Invoice</label>
                    <div class="form-value"><?= date('d M Y', strtotime($data['tanggal_invoice'])) ?></div>
                </div>

                <div class="form-group">
                    <label>Total</label>
                    <div class="form-value amount">
                        Rp <?= number_format($data['total'],0,',','.') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <span class="status-badge <?= $data['status']=='DP'?'dp':'' ?>">
                        <?= $data['status'] ?>
                    </span>
                </div>

                <?php if($data['status']=='DP'): ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>DP</label>
                        <div class="form-value">
                            Rp <?= number_format($data['dp_nominal'],0,',','.') ?> (<?= $data['dp_persen'] ?>%)
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sisa Pelunasan</label>
                        <div class="form-value">
                            Rp <?= number_format($data['sisa_nominal'],0,',','.') ?> (<?= $data['sisa_persen'] ?>%)
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="doc-section">
                    <div class="doc-title">Dokumen Invoice</div>

                    <?php if (!empty($data['file_invoice'])): ?>
                    <div class="doc-actions">
                        <a href="download_invoice.php?file=<?= $data['file_invoice'] ?>" target="_blank"
                            class="btn-view">
                            Lihat Dokumen
                        </a>
                        <a href="download_invoice.php?file=<?= $data['file_invoice'] ?>&dl=1" class="btn-dl">
                            Download
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="doc-empty">Belum ada dokumen invoice</div>
                    <?php endif; ?>
                </div>

                <a href="index.php" class="back">Kembali</a>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div>
                © <?= date('Y') ?> <span>PT KREASIJAYA ADHIKARYA</span>. All Rights Reserved
            </div>
            <div class="version">
                Sistem Invoice v1.0
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {

        function realtimeSearch(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;

            const rows = document.querySelectorAll("table tbody tr");

            input.addEventListener("keyup", function() {
                const keyword = this.value.toLowerCase();

                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.classList.toggle("hide-row", !text.includes(keyword));
                });
            });
        }

        realtimeSearch("searchVendor");
        realtimeSearch("searchInvoice");
        realtimeSearch("searchPembayaran");

    });
    </script>
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

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLE JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

</html>