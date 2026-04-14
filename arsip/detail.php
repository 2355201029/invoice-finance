<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* ===============================
   VALIDASI ID
================================ */
$id_invoice = intval($_GET['id'] ?? 0);
if ($id_invoice <= 0) {
    die("ID Invoice tidak valid");
}

/* ===============================
   DATA INVOICE (FIXED)
================================ */
$queryInvoice = mysqli_query($conn, "
    SELECT 
        i.id_invoice,
        i.nomor_invoice,
        i.total,
        i.status,
        i.file_invoice,
        ai.tanggal_arsip,
        v.nama_vendor
    FROM invoice i
    JOIN vendor v ON i.id_vendor = v.id_vendor
    LEFT JOIN arsip_invoice ai ON ai.id_invoice = i.id_invoice
    WHERE i.id_invoice = $id_invoice
    LIMIT 1
");

$invoice = mysqli_fetch_assoc($queryInvoice);

if (!$invoice) {
    die("Data invoice tidak ditemukan");
}

/* ===============================
   DOKUMEN ARSIP TAMBAHAN
================================ */
$dokumen = mysqli_query($conn, "
    SELECT * FROM invoice_dokumen
    WHERE id_invoice = $id_invoice
    ORDER BY id_dokumen DESC
");
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
        background: #f4f7ff;
        font-family: 'Segoe UI', sans-serif
    }

    .container {
        max-width: 900px;
        margin: 40px auto
    }

    .card {
        background: #fff;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, .08)
    }

    .sub {
        color: #64748b;
        font-size: 14px
    }

    .info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin: 24px 0
    }

    .info div {
        background: #f8fafc;
        padding: 14px;
        border-radius: 12px;
        font-size: 14px
    }

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

    .list {
        list-style: none;
        padding: 0
    }

    .list li {
        background: #f8fafc;
        padding: 14px;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px
    }

    .btn {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none
    }

    .view {
        background: #e0f2fe;
        color: #0369a1
    }

    .download {
        background: #dcfce7;
        color: #166534
    }

    .upload-box {
        display: flex;
        gap: 10px;
        margin-top: 18px
    }

    .btn-upload {
        background: #22c55e;
        color: #fff;
        border: none;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer
    }

    .back {
        display: inline-block;
        margin-top: 25px;
        color: #2563eb;
        font-weight: 600;
        text-decoration: none
    }

    /* WRAPPER AKSI */
    .arsip-aksi,
    .list li div {
        display: flex;
        gap: 8px;
    }

    /* BASE BUTTON */
    .btn,
    .btn-view,
    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all .25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
    }

    /* LIHAT */
    .btn-view,
    .view {
        background: linear-gradient(135deg, #e0f2fe, #bae6fd);
        color: #0369a1;
    }

    .btn-view:hover,
    .view:hover {
        background: linear-gradient(135deg, #bae6fd, #7dd3fc);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(2, 132, 199, .25);
    }

    /* DOWNLOAD */
    .btn-download,
    .download {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
    }

    .btn-download:hover,
    .download:hover {
        background: linear-gradient(135deg, #bbf7d0, #86efac);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(22, 163, 74, .25);
    }

    /* ICON OPTIONAL (kalau pakai emoji / icon) */
    .btn-view::before {
        content: "👁️";
    }

    .btn-download::before {
        content: "⬇️";
    }

    /* TOMBOL KEMBALI */
    .back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 26px;
        padding: 8px 18px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        color: #1d4ed8;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        box-shadow: 0 6px 16px rgba(29, 78, 216, .15);
        transition: all .25s ease;
    }

    /* ICON PANAH */
    .back::before {
        content: "←";
        font-size: 15px;
    }

    /* HOVER EFFECT */
    .back:hover {
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        transform: translateY(-2px);
        box-shadow: 0 10px 26px rgba(29, 78, 216, .25);
        color: #1e40af;
    }

    /* ACTIVE / CLICK */
    .back:active {
        transform: translateY(0);
        box-shadow: 0 4px 12px rgba(29, 78, 216, .2);
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
            <div class="card">

                <h2>📄 Detail Arsip Invoice</h2>
                <div class="sub">Invoice utama & dokumen arsip</div>

                <div class="info">
                    <div><b>Nomor Invoice</b><br><?= $invoice['nomor_invoice'] ?></div>
                    <div><b>Vendor</b><br><?= $invoice['nama_vendor'] ?></div>
                    <div><b>Total</b><br>Rp <?= number_format($invoice['total'],0,',','.') ?></div>
                    <div><b>Status</b><br><span class="badge success"><?= $invoice['status'] ?></span></div>
                    <div><b>Tanggal Arsip</b><br><?= date('d M Y',strtotime($invoice['tanggal_arsip'])) ?></div>
                </div>

                <!-- INVOICE UTAMA -->
                <h3>📑 Dokumen Invoice Utama</h3>

                <?php if ($invoice['file_invoice']): ?>
                <ul class="list">
                    <li>
                        <?= $invoice['file_invoice'] ?>
                        <div>
                            <a class="btn view" target="_blank"
                                href="../uploads/invoice/<?= $invoice['file_invoice'] ?>">Lihat</a>
                            <a class="btn download" href="../uploads/invoice/<?= $invoice['file_invoice'] ?>"
                                download>Download</a>
                        </div>
                    </li>
                </ul>
                <?php else: ?>
                <em>Invoice utama belum di-upload</em>
                <?php endif; ?>

                <!-- ARSIP TAMBAHAN -->
                <h3 style="margin-top:30px">📂 Dokumen Arsip Tambahan</h3>

                <?php if (mysqli_num_rows($dokumen) == 0): ?>
                <em>Belum ada dokumen arsip</em>
                <?php else: ?>
                <ul class="list">
                    <?php while ($d = mysqli_fetch_assoc($dokumen)): ?>
                    <li>
                        <?= $d['nama_file'] ?>
                        <div>
                            <a class="btn view" target="_blank" href="../uploads/arsip/<?= $d['nama_file'] ?>">Lihat</a>
                            <a class="btn download" href="../uploads/arsip/<?= $d['nama_file'] ?>" download>Download</a>
                        </div>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <?php endif; ?>

                <!-- UPLOAD ARSIP -->
                <form action="upload_dokumen_arsip.php" method="post" enctype="multipart/form-data" class="upload-box">
                    <input type="hidden" name="id_invoice" value="<?= $invoice['id_invoice'] ?>">
                    <input type="file" name="dokumen" accept="application/pdf" required>
                    <button type="submit" class="btn-upload">Upload Dokumen Arsip</button>
                </form>

                <a href="index.php" class="back">Kembali ke Arsip</a>

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