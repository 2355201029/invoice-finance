<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role']; // admin / user

$query = mysqli_query($conn, "SELECT * FROM vendor ORDER BY id_vendor DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Vendor | Sistem Invoice</title>

    <!-- DATATABLE -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

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
        border-radius: 12px;
    }

    /* CARD TABLE */
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

    /* BUTTON & SEARCH */
    .table-action {
        display: flex;
        gap: 14px;
        align-items: center
    }

    .btn-add {
        background: linear-gradient(135deg, #4f46e5, #2f5bea);
        color: #fff;
        padding: 10px 18px;
        border-radius: 12px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600
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


    /* ROW HIDDEN (REALTIME SEARCH) */
    tr.hide-row {
        display: none;
    }


    /* TABLE */
    .vendor-table {
        width: 100%;
        border-collapse: collapse
    }

    .vendor-table thead {
        background: #f1f5f9
    }

    .vendor-table th,
    .vendor-table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px
    }

    .vendor-table tr:hover {
        background: #f9fafb
    }

    /* ACTION */
    .aksi a {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        text-decoration: none;
        margin-right: 6px
    }

    /* ===============================
   EDIT (AMAN UNTUK USER)
================================ */
    .btn-edit {
        background: #ecfeff;
        color: #0f766e;
        border: 1px solid #99f6e4;
    }

    .btn-edit:hover {
        background: #0f766e;
        color: #fff;
        box-shadow: 0 6px 18px rgba(15, 118, 110, .35);
        transform: translateY(-1px);
    }

    .btn-edit::before {
        content: "✏️";
    }

    /* ===============================
   HAPUS (KHUSUS ADMIN)
================================ */
    .btn-hapus {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .btn-hapus:hover {
        background: #dc2626;
        color: #fff;
        box-shadow: 0 6px 18px rgba(220, 38, 38, .35);
        transform: translateY(-1px);
    }

    .btn-hapus::before {
        content: "🗑️";
    }

    /* ===============================
   AKSI RAPHI
================================ */
    td.aksi {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    /* FOOTER */
    .footer {
        margin-top: 40px;
        padding: 18px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .06);
        text-align: center;
        color: #6b7280
    }

    .footer span {
        color: var(--primary);
        font-weight: 600
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

    /* ===============================
    TOMBOL TAMBAH VENDOR
================================ */
    .btn-tambah-vendor {
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
    .btn-tambah-vendor:hover {
        background: linear-gradient(135deg, #4338ca, #4f46e5);
        transform: translateY(-2px);
        box-shadow:
            0 14px 30px rgba(79, 70, 229, .45);
    }

    /* klik */
    .btn-add-invoice:active,
    .btn-tambah-vendor:active {
        transform: scale(.97);
        box-shadow:
            0 6px 16px rgba(79, 70, 229, .35);
    }

    /* icon plus */
    .btn-add-invoice::before,
    .btn-tambah-vendor::before {
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
   RESPONSIVE TABLE VENDOR
================================ */

    /* wrapper scroll horizontal */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* mobile */
    @media (max-width: 768px) {

        .vendor-table {
            min-width: 750px;
            /* biar tidak pecah */
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .table-action {
            width: 100%;
            flex-wrap: wrap;
            gap: 10px;
        }

        #searchVendor {
            width: 100%;
        }

        .btn-tambah-vendor {
            width: 100%;
            justify-content: center;
        }

        td.aksi {
            flex-direction: column;
            align-items: stretch;
        }

        .aksi a {
            width: 100%;
        }
    }


    /* EDIT */
    .btn-edit {
        background: #ede9fe;
        color: #6d28d9;
    }

    .btn-edit:hover {
        background: #7c3aed;
        color: #fff;
        box-shadow: 0 6px 18px rgba(124, 58, 237, .35);
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

    .vendor-table th {
        background: #f1f5f9;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #475569;
    }

    .vendor-table td {
        vertical-align: middle;
    }

    .vendor-table td:first-child {
        text-align: center;
        font-weight: 600;
    }

    .aksi {
        display: flex;
        gap: 8px;
    }

    .aksi a {
        min-width: 70px;
        text-align: center;
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

    <!-- MAIN -->
    <div class="main-content">

        <div class="header">
            <div>
                <h2>SISTEM INVOICE</h2>
            </div>

            <div id="clock" data-server-time="<?= date('Y-m-d H:i:s') ?>"
                style="margin-top:6px;font-size:14px;opacity:.9;font-weight:500;">
            </div>

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
                <h3>Data Vendor</h3>
                <div class="table-action">
                    <?php if ($role === 'admin' || $role === 'user'): ?>
                    <a href="tambah.php" class="btn btn-tambah-vendor">
                        Tambah Vendor
                    </a>
                    <?php endif; ?>

                    <input type="text" id="searchVendor" placeholder="Cari data vendor..." autocomplete="off">
                </div>
            </div>

            <div class="table-responsive">
                <table id="vendorTable" class="vendor-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Vendor</th>
                            <th>Alamat</th>
                            <th>No. Telp</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query)>0): $no=1; ?>
                        <?php while($v=mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= $v['nama_vendor'] ?></strong></td>
                            <td><?= $v['alamat_vendor'] ?></td>
                            <td><?= $v['no_telp'] ?></td>
                            <td><?= $v['email'] ?></td>

                            <td class="aksi">

                                <!-- EDIT: ADMIN & USER -->
                                <?php if ($role === 'admin' || $role === 'user'): ?>
                                <a href="edit.php?id=<?= $v['id_vendor'] ?>" class="btn btn-edit btn-sm">
                                    Edit
                                </a>
                                <?php endif; ?>

                                <!-- HAPUS: ADMIN SAJA -->
                                <?php if ($role === 'admin'): ?>
                                <a href="hapus.php?id=<?= $v['id_vendor'] ?>" class="btn btn-hapus btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    Hapus
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:#6b7280">
                                Belum ada data vendor
                            </td>
                        </tr>
                        <?php endif ?>
                    </tbody>
                </table>
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
    document.addEventListener("DOMContentLoaded", function() {

        const searchInput = document.getElementById("searchVendor");
        const rows = document.querySelectorAll("#vendorTable tbody tr");

        // realtime search
        searchInput.addEventListener("keyup", function() {
            const keyword = this.value.toLowerCase();

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(keyword) ? "" : "none";
            });
        });

    });
    </script>

</body>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLE JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

</html>