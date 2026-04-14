<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role']; // ✅ pastikan role terdefinisi

/* ===============================
   QUERY PEMBAYARAN DP
================================ */
$query_dp = mysqli_query($conn, "
    SELECT 
        pembayaran.*,
        invoice.nomor_invoice,
        invoice.total AS total_invoice,
        (
            SELECT SUM(jumlah)
            FROM pembayaran
            WHERE id_invoice = invoice.id_invoice
        ) AS total_bayar
    FROM pembayaran
    JOIN invoice ON pembayaran.id_invoice = invoice.id_invoice
    HAVING total_bayar < total_invoice
    ORDER BY pembayaran.tanggal_bayar DESC
");

/* ===============================
   QUERY PEMBAYARAN LUNAS
================================ */
$query_lunas = mysqli_query($conn, "
    SELECT 
        pembayaran.*,
        invoice.nomor_invoice,
        invoice.total AS total_invoice,
        (
            SELECT SUM(jumlah)
            FROM pembayaran
            WHERE id_invoice = invoice.id_invoice
        ) AS total_bayar
    FROM pembayaran
    JOIN invoice ON pembayaran.id_invoice = invoice.id_invoice
    HAVING total_bayar >= total_invoice
    ORDER BY pembayaran.tanggal_bayar DESC
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

    .btn-add-pembayaran {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 10px 22px;
        border-radius: 999px;

        font-size: 14px;
        font-weight: 600;
        letter-spacing: .2px;

        color: #fff !important;
        text-decoration: none;

        background: linear-gradient(135deg, #4f46e5, #6366f1);
        border: none;

        box-shadow:
            0 8px 20px rgba(79, 70, 229, .35),
            inset 0 -2px 0 rgba(255, 255, 255, .15);

        transition: all .25s ease;
    }

    /* hover */
    .btn-add-invoice:hover,
    .btn-tambah-vendor:hover,
    .btn-add-pembayaran:hover {
        background: linear-gradient(135deg, #4338ca, #4f46e5);
        transform: translateY(-2px);
        box-shadow:
            0 14px 30px rgba(79, 70, 229, .45);
    }

    /* klik */
    .btn-add-invoice:active,
    .btn-tambah-vendor:active,
    .btn-add-pembayaran:active {
        transform: scale(.97);
    }

    /* icon + */
    .btn-add-invoice::before,
    .btn-tambah-vendor::before,
    .btn-add-pembayaran::before {
        content: "+";
        display: inline-flex;
        width: 18px;
        height: 18px;
        align-items: center;
        justify-content: center;

        font-size: 14px;
        font-weight: 700;

        background: rgba(255, 255, 255, .25);
        border-radius: 50%;
    }

    /* ===============================
   RESPONSIVE TABLE PEMBAYARAN
   TANPA UBAH STRUKTUR
================================ */

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 14px;
    }

    /* Scrollbar halus */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #edf0f7;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #6366f1, #818cf8);
        border-radius: 10px;
    }

    /* TABEL */
    .table-responsive table {
        width: 100%;
        min-width: 900px;
        /* KUNCI RESPONSIVE PEMBAYARAN */
        border-collapse: collapse;
    }

    /* Header */
    .table-responsive th {
        background: #f6f8ff;
        font-size: 13px;
        font-weight: 600;
        padding: 12px;
        white-space: nowrap;
    }

    /* Isi */
    .table-responsive td {
        padding: 12px;
        font-size: 13px;
        border-bottom: 1px solid #e5e9f2;
        white-space: nowrap;
    }

    /* Hover */
    .table-responsive tbody tr:hover {
        background: #f9fbff;
    }

    /* MOBILE */
    @media (max-width: 768px) {
        .table-responsive table {
            min-width: 850px;
        }
    }

    .btn-view {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 7px 16px;
        font-size: 13px;
        font-weight: 600;

        color: #0f766e;
        /* hijau tosca profesional */
        background: rgba(15, 118, 110, 0.12);

        border-radius: 8px;
        text-decoration: none;
        border: 1px solid rgba(15, 118, 110, 0.25);

        transition: all 0.25s ease;
    }

    /* Hover – kesan buka tab baru */
    .btn-view:hover {
        background: #0f766e;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 118, 110, 0.35);
    }

    /* Klik */
    .btn-view:active {
        transform: scale(0.97);
    }

    /* HAPUS */
    .btn-hapus {
        background: #fee2e2;
        color: #b91c1c;
    }

    .btn-hapus:hover {
        background: #dc2626;
        color: #fff;
        box-shadow: 0 6px 18px rgba(220, 38, 38, .35);
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
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3>Data Pembayaran Invoice</h3>

                <div class="table-action">
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'user'): ?>
                    <a href="tambah.php" class="btn-add-pembayaran">
                        Tambah Pembayaran
                    </a>
                    <?php endif; ?>
                    <input type="text" id="searchPembayaran" placeholder="Cari data pembayaran..." autocomplete="off">

                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>Pembayaran DP</h3>
                </div>

                <div class="table-responsive">
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Invoice</th>
                                <th>Tanggal Bayar</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Bukti</th>
                                <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query_dp) > 0): ?>
                            <?php $no=1; while($row = mysqli_fetch_assoc($query_dp)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= $row['nomor_invoice']; ?></strong></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_bayar'])); ?></td>
                                <td class="price">Rp <?= number_format($row['jumlah'], 0, ',', '.'); ?></td>
                                <td><?= $row['metode']; ?></td>

                                <!-- BUKTI -->
                                <td>
                                    <?php if($row['bukti']): ?>
                                    <a href="../uploads/bukti/<?= $row['bukti']; ?>" target="_blank"
                                        class="btn-view">Lihat</a>
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </td>

                                <!-- AKSI -->
                                <?php if ($role === 'admin'): ?>
                                <td class="aksi">
                                    <a href="hapus.php?id=<?= $row['id_pembayaran'] ?>" class="btn-hapus"
                                        onclick="return confirm('Yakin ingin menghapus pembayaran ini?')">
                                        Hapus
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="<?= $role === 'admin' ? 7 : 6 ?>" class="empty">
                                    Tidak ada pembayaran DP
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="table-card">
                <div class="table-header">
                    <h3>Pembayaran Lunas</h3>
                </div>
                <div class="table-responsive">
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Invoice</th>
                                <th>Tanggal Bayar</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Bukti</th>
                                <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query_lunas)>0): ?>
                            <?php $no=1; while($row=mysqli_fetch_assoc($query_lunas)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= $row['nomor_invoice'] ?></strong></td>
                                <td><?= date('d M Y',strtotime($row['tanggal_bayar'])) ?></td>
                                <td class="price">Rp <?= number_format($row['jumlah'],0,',','.') ?></td>
                                <td><?= $row['metode'] ?? '-' ?></td>
                                <td>
                                    <?php if($row['bukti']): ?>
                                    <a href="../uploads/bukti/<?= $row['bukti'] ?>" target="_blank"
                                        class="btn-view">Lihat</a>
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <?php if ($role === 'admin'): ?>
                                <td class="aksi">
                                    <a href="hapus.php?id=<?= $row['id_pembayaran'] ?>" class="btn-hapus"
                                        onclick="return confirm('Yakin ingin menghapus pembayaran ini?')">Hapus</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty">Belum ada pembayaran lunas</td>
                            </tr>
                            <?php endif ?>
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
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {

        const input = document.getElementById("searchPembayaran");
        const rows = document.querySelectorAll(".invoice-table tbody tr");

        input.addEventListener("keyup", function() {
            const keyword = this.value.toLowerCase();

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(keyword) ? "" : "none";
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

    <script>
    document.querySelectorAll('.table-responsive').forEach(function(el) {
        el.style.webkitOverflowScrolling = 'touch';
    });
    </script>

</body>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLE JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

</html>