<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard/index.php");
    exit;
}

/* BASE URL AMAN */
$base_url = dirname($_SERVER['SCRIPT_NAME']);
$base_url = str_replace('\\', '/', $base_url) . '/';

/* QUERY USER */
$query = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Verifiksi User | Sistem Invoice</title>

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

    .hide-row {
        display: none !important;
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

    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
    }

    .container {
        width: 95%;
        margin: 30px auto;
        background: #fff;
        padding: 20px;
        border-radius: 10px;
    }

    /* ===================== RESPONSIVE TABLE ===================== */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .responsive-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    /* ===================== AKSI COLUMN ===================== */
    .aksi-col {
        min-width: 240px;
        white-space: nowrap;
    }

    /* ===================== MOBILE VIEW ===================== */
    @media (max-width: 768px) {

        .main-content {
            margin-left: 0;
            padding: 16px;
        }

        .header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .table-card {
            padding: 15px;
        }

        .badge {
            font-size: 11px;
            padding: 4px 8px;
        }

        .btn-aksi {
            font-size: 11px;
            padding: 6px 10px;
        }

        .search-wrapper {
            max-width: 100%;
        }
    }


    th {
        background: #2c3e50;
        color: white;
        padding: 12px;
    }

    td {
        padding: 10px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 5px;
        color: white;
    }

    .active {
        background: #2ecc71;
    }

    .pending {
        background: #f39c12;
    }

    .nonactive {
        background: #7f8c8d;
        /* abu-abu */
    }

    .btn {
        padding: 6px 10px;
        border-radius: 5px;
        text-decoration: none;
        color: white;
        font-size: 13px;
    }

    .btn-acc {
        background: #27ae60;
    }

    .btn-nonactive {
        background: #7f8c8d;
    }

    .btn-admin {
        background: #2980b9;
    }

    .btn:hover {
        opacity: .9;
    }

    /* ACTIVE */
    .badge.active {
        background: linear-gradient(135deg, #bbf7d0, #22c55e);
        color: #14532d;
    }

    /* BLOCKED */
    .badge.blocked {
        background: linear-gradient(135deg, #e5e7eb, #9ca3af);
        color: #374151;
    }

    /* ADMIN */
    .badge.admin {
        background: linear-gradient(135deg, #2563eb, #1e3a8a);
        color: #ffffff;
    }

    /* ================== HOVER EFFECT ================== */
    .badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(0, 0, 0, .18);
        transition: .25s;
    }

    /* ================== AKSI BUTTON ================== */
    .btn-aksi {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 14px;
        margin: 3px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border-radius: 10px;
        transition: all .25s ease;
        cursor: pointer;
        border: none;
        box-shadow: 0 6px 14px rgba(0, 0, 0, .12);
        white-space: nowrap;
    }

    /* ================== ACC ================== */
    .btn-acc {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #fff;
    }

    .btn-acc:hover {
        background: linear-gradient(135deg, #16a34a, #15803d);
        transform: translateY(-2px);
    }

    /* ================== NONAKTIF ================== */
    .btn-nonaktif {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
    }

    .btn-nonaktif:hover {
        background: linear-gradient(135deg, #d97706, #b45309);
        transform: translateY(-2px);
    }


    /* ================== ADMIN ================== */
    .btn-admin {
        background: linear-gradient(135deg, #2563eb, #1e3a8a);
        color: #fff;
    }

    .btn-admin:hover {
        background: linear-gradient(135deg, #1e3a8a, #172554);
        transform: translateY(-2px);
    }

    /* ================== DISABLED ================== */
    .disabled {
        display: inline-block;
        padding: 8px 14px;
        margin: 3px;
        font-size: 12px;
        border-radius: 10px;
        background: #e5e7eb;
        color: #6b7280;
        cursor: not-allowed;
        font-weight: 600;
    }


    /* SEARCH LOG */
    .search-wrapper {
        position: relative;
        max-width: 420px;
        margin: 0 0 16px auto;
    }

    .search-wrapper input {
        width: 100%;
        padding: 10px 14px 10px 42px;
        border-radius: 25px;
        border: 1px solid #d1d5db;
        outline: none;
        font-size: 14px;
        transition: all 0.3s ease;
        background: #fff;
    }

    .search-wrapper input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    .search-icon {
        position: absolute;
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
        font-size: 16px;
        color: #6b7280;
        pointer-events: none;
    }

    .hide-row {
        display: none !important;
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
                <h3>👥 Verifikasi & Manajemen User</h3>
            </div>

            <div class="table-header">
                <span class="subtitle">
                    Kelola status user: approve, nonactive atau jadikan admin
                </span>
            </div>

            <div class="search-wrapper">
                <i class="search-icon"></i>
                <input type="text" id="searchUser" placeholder="Cari nama, email, role, atau status..."
                    autocomplete="off">
            </div>

            <div class="table-responsive">
                <table id="tabelUser" class="responsive-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no=1; while($row=mysqli_fetch_assoc($query)){ ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= $row['role'] ?></td>
                            <td>
                                <span class="badge <?= $row['status'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="aksi-col">
                                <a class="btn-aksi btn-acc"
                                    href="<?= $base_url ?>proses_verifikasi.php?id=<?= $row['id_user'] ?>&aksi=acc"
                                    onclick="return confirm('ACC user ini?')">
                                    ACC
                                </a>

                                <?php if ($row['role'] !== 'admin') { ?>
                                <a class="btn-aksi btn-admin"
                                    href="<?= $base_url ?>proses_verifikasi.php?id=<?= $row['id_user'] ?>&aksi=admin"
                                    onclick="return confirm('Jadikan admin?')">
                                    Admin
                                </a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
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

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        const searchInput = document.getElementById('searchUser');
        const table = document.getElementById('tabelUser');
        const rows = table.querySelectorAll('tbody tr');

        searchInput.addEventListener('keyup', function() {
            const keyword = this.value.toLowerCase().trim();

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(keyword)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        const table = document.getElementById('tabelUser');
        const searchInput = document.getElementById('searchUser');

        // ====== SEARCH MANUAL (TETAP RINGAN & RESPONSIVE) ======
        searchInput.addEventListener('keyup', function() {
            const keyword = this.value.toLowerCase();

            Array.from(table.tBodies[0].rows).forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(keyword) ? '' : 'none';
            });
        });

        // ====== RESPONSIVE AUTO FIX (MOBILE) ======
        function makeTableResponsive() {
            if (window.innerWidth <= 768) {
                table.classList.add('mobile-table');
            } else {
                table.classList.remove('mobile-table');
            }
        }

        makeTableResponsive();
        window.addEventListener('resize', makeTableResponsive);

    });
    </script>

</body>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLE JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

</html>