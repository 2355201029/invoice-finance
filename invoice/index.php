<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'];

// ✅ QUERY GABUNG SEMUA STATUS
$qInvoice = mysqli_query($conn,"
    SELECT i.*, v.nama_vendor
    FROM invoice i
    INNER JOIN vendor v ON i.id_vendor = v.id_vendor
    WHERE i.status_arsip = 'Aktif'
    ORDER BY i.id_invoice DESC
");

function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
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

    /* ===============================
    TOMBOL TAMBAH INVOICE
================================ */
    .btn-add-invoice {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 10px 20px;
        border-radius: 999px;

        font-size: 14px;
        font-weight: 600;
        letter-spacing: .2px;

        background: linear-gradient(135deg, #4f46e5, #6366f1);
        border: none;

        box-shadow:
            0 8px 20px rgba(79, 70, 229, .35),
            inset 0 -2px 0 rgba(255, 255, 255, .15);

        transition: all .25s ease;
    }

    /* hover */
    .btn-add-invoice:hover {
        background: linear-gradient(135deg, #4338ca, #4f46e5);
        transform: translateY(-2px);
        box-shadow:
            0 14px 30px rgba(79, 70, 229, .45);
    }

    /* klik */
    .btn-add-invoice:active {
        transform: scale(.97);
        box-shadow:
            0 6px 16px rgba(79, 70, 229, .35);
    }

    /* icon plus (opsional efek) */
    .btn-add-invoice::before {
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

    /* paksa teks tombol tambah invoice jadi putih */
    .btn-add-invoice,
    .btn-add-invoice:hover,
    .btn-add-invoice:focus,
    .btn-add-invoice:active {
        color: #ffffff !important;
        text-decoration: none;
    }


    .btn-download {
        background: #dcfce7;
        color: #166534;
    }

    /* ===== TABEL UMUM ===== */
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 14px;
    }

    thead th {
        background: #f4f7ff;
        padding: 14px 12px;
        font-weight: 600;
        text-align: center;
        color: #374151;
        white-space: nowrap;
    }

    tbody td {
        padding: 14px 12px;
        border-bottom: 1px solid #eef2ff;
        vertical-align: middle;
        white-space: nowrap;
    }

    /* ===== ALIGN OTOMATIS BERDASARKAN KOLOM ===== */
    /* Kolom angka (Total, DP, Sisa) */
    tbody td:nth-child(5),
    tbody td:nth-child(6),
    tbody td:nth-child(8) {
        text-align: right;
        font-weight: 500;
    }

    /* Kolom persen */
    tbody td:nth-child(7),
    tbody td:nth-child(9) {
        text-align: center;
    }

    /* Kolom nomor */
    tbody td:nth-child(1) {
        text-align: center;
        font-weight: 600;
    }

    /* Kolom status */
    tbody td:nth-child(10) {
        text-align: center;
    }

    /* Kolom aksi */
    tbody td:nth-child(11) {
        text-align: center;
        min-width: 180px;
    }

    /* ===== BADGE STATUS ===== */
    .badge {
        display: inline-block;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 999px;
    }

    .badge.belum-bayar {
        background: #fee2e2;
        color: #b91c1c;
    }

    .badge.dp {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.lunas {
        background: #dcfce7;
        color: #166534;
    }

    /* ===============================
   TOMBOL AKSI DETAIL
================================ */
    .btn-detail {
        display: inline-flex;
        align-items: center;
        gap: 6px;

        padding: 6px 14px;
        border-radius: 999px;

        font-size: 13px;
        font-weight: 600;
        text-decoration: none;

        color: #2563eb;
        background: #eef2ff;

        border: 1px solid #c7d2fe;

        transition: all .25s ease;
    }

    /* hover */
    .btn-detail:hover {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 6px 18px rgba(37, 99, 235, .35);
        transform: translateY(-1px);
    }

    /* klik */
    .btn-detail:active {
        transform: scale(.96);
    }

    /* icon */
    .btn-detail::before {
        content: "📄";
        font-size: 13px;
    }

    /* ===============================
   TOMBOL AKSI ADMIN (OVERRIDE BOOTSTRAP)
================================ */

    /* UMUM */
    .btn-warning.btn-sm,
    .btn-danger.btn-sm {
        display: inline-flex;
        align-items: center;
        gap: 6px;

        padding: 6px 14px;
        border-radius: 999px;

        font-size: 13px;
        font-weight: 600;

        transition: all .25s ease;
        box-shadow: 0 3px 10px rgba(0, 0, 0, .08);
    }

    /* ===============================
    EDIT (WARNING → UNGU ELEGAN)
================================ */
    .btn-warning.btn-sm {
        background: #f5f3ff !important;
        color: #7c3aed !important;
        border: 1px solid #ddd6fe !important;
    }

    .btn-warning.btn-sm:hover {
        background: #7c3aed !important;
        color: #fff !important;
        box-shadow: 0 6px 18px rgba(124, 58, 237, .35);
        transform: translateY(-1px);
    }

    .btn-warning.btn-sm::before {
        content: "✏️";
    }

    /* ===============================
   HAPUS (DANGER → MERAH HALUS)
================================ */
    .btn-danger.btn-sm {
        background: #fef2f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fecaca !important;
    }

    .btn-danger.btn-sm:hover {
        background: #dc2626 !important;
        color: #fff !important;
        box-shadow: 0 6px 18px rgba(220, 38, 38, .35);
        transform: translateY(-1px);
    }

    .btn-danger.btn-sm::before {
        content: "🗑️";
    }

    /* EFEK KLIK */
    .btn-warning.btn-sm:active,
    .btn-danger.btn-sm:active {
        transform: scale(.96);
    }

    /* ===============================
   RESPONSIVE TABLE
================================ */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    @media (max-width: 768px) {

        .main-content {
            margin-left: 0;
            padding: 16px;
        }

        .main-sidebar {
            position: fixed;
            left: -260px;
            transition: .3s;
            z-index: 999;
        }

        .main-sidebar.active {
            left: 0;
        }

        table {
            font-size: 13px;
        }

        thead th {
            font-size: 12px;
            padding: 10px;
        }

        tbody td {
            padding: 10px;
        }

        .btn-detail,
        .btn-warning,
        .btn-danger {
            font-size: 11px;
            padding: 6px 10px;
            margin-bottom: 4px;
            display: inline-flex;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .table-action {
            width: 100%;
            flex-wrap: wrap;
        }

        #searchInvoice {
            width: 100%;
        }
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
                <h2>Data Invoice</h2>

                <div class="table-action">
                    <?php if ($role === 'admin' || $role === 'user'): ?>
                    <a href="tambah.php" class="btn btn-primary btn-add-invoice">
                        Tambah Invoice
                    </a>
                    <?php endif; ?>

                    <input type="text" id="searchInvoice" placeholder="Cari data invoice..." autocomplete="off">
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Inv / PO</th>
                        <th>Vendor</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>DP</th>
                        <th>DP %</th>
                        <th>Sisa</th>
                        <th>Sisa %</th>
                        <th>Tgl DP</th>
                        <th>Tgl Lunas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $no=1; while($r=mysqli_fetch_assoc($qInvoice)): ?>

                    <?php
                // STATUS BADGE
                if ($r['status'] == 'Belum Bayar') {
                    $badge = "<span class='badge belum-bayar'>Belum Bayar</span>";
                } elseif ($r['status'] == 'DP') {
                    $badge = "<span class='badge dp'>DP</span>";
                } else {
                    $badge = "<span class='badge lunas'>Lunas</span>";
                }

                // TANGGAL DP & LUNAS (AMAN NULL)
                $tgl_dp = !empty($r['tanggal_dp']) 
                    ? date('d M Y', strtotime($r['tanggal_dp'])) 
                    : '-';

                $tgl_lunas = !empty($r['tanggal_lunas']) 
                    ? date('d M Y', strtotime($r['tanggal_lunas'])) 
                    : '-';
            ?>

                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $r['nomor_invoice'] ?></td>
                        <td><?= $r['nama_vendor'] ?></td>
                        <td><?= date('d M Y', strtotime($r['tanggal_invoice'])) ?></td>

                        <td class="price"><?= rupiah($r['total']) ?></td>

                        <td class="price">
                            <?= $r['dp_nominal'] ? rupiah($r['dp_nominal']) : '-' ?>
                        </td>

                        <td><?= $r['dp_persen'] ?? 0 ?>%</td>

                        <td class="price">
                            <?= $r['sisa_nominal'] ? rupiah($r['sisa_nominal']) : '-' ?>
                        </td>

                        <td><?= $r['sisa_persen'] ?? 0 ?>%</td>

                        <td><?= $tgl_dp ?></td>
                        <td><?= $tgl_lunas ?></td>

                        <td><?= $badge ?></td>

                        <td>
                            <a href="detail.php?id=<?= $r['id_invoice'] ?>" class="btn-detail">
                                Detail
                            </a>

                            <?php if ($role === 'admin'): ?>
                            <a href="hapus.php?id=<?= $r['id_invoice'] ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin menghapus invoice ini?')">
                                Hapus
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php endwhile; ?>
                </tbody>
            </table>
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