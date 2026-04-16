<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'];

/* ===============================
   LOGIKA FILTER BULAN & TAHUN
================================ */
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

$where_clause = "";
if ($filter_bulan != '') {
    $where_clause .= " AND MONTH(pembayaran.tanggal_bayar) = '$filter_bulan'";
}
if ($filter_tahun != '') {
    $where_clause .= " AND YEAR(pembayaran.tanggal_bayar) = '$filter_tahun'";
}

/* ===============================
   QUERY PEMBAYARAN LUNAS (DENGAN FILTER)
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
    WHERE 1=1 $where_clause
    HAVING total_bayar >= total_invoice
    ORDER BY pembayaran.tanggal_bayar DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pembayaran | Sistem Invoice</title>
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
            color: #fff;
            z-index: 100;
        }

        .brand-link {
            display: block;
            padding: 22px;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            background: rgba(255, 255, 255, .08);
            color: #fff;
            text-decoration: none;
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

        /* TABLE CARD */
        .table-card {
            margin-top: 25px;
            background: #fff;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .08)
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* FILTER & ACTION BOX */
        .table-action {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
            background: #f8fafc;
            padding: 10px 15px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .filter-group select {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            outline: none;
            font-size: 13px;
        }

        .filter-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .filter-box select {
            border: none;
            background: transparent;
            outline: none;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
        }

        .btn-filter {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 7px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        .btn-filter:hover {
            background: var(--secondary);
            opacity: 0.9;
        }

        #searchPembayaran {
            padding: 10px 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            outline: none;
            font-size: 14px;
            width: 240px;
            transition: all 0.2s ease;
        }

        #searchPembayaran:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(47, 91, 234, 0.1);
        }

        /* TABLE STYLING */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 14px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .invoice-table th {
            background: #f8fafc;
            color: #64748b;
            text-align: left;
            padding: 16px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }

        .invoice-table td {
            padding: 16px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .price {
            font-weight: 700;
            color: #1e293b;
        }

        .empty {
            text-align: center;
            color: #94a3b8;
            padding: 40px;
            font-style: italic;
        }

        /* BUTTONS */
        .btn-add-pembayaran {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #fff !important;
            text-decoration: none;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            box-shadow: 0 8px 20px rgba(79, 70, 229, .25);
            transition: .3s;
        }

        .btn-add-pembayaran:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(79, 70, 229, .35);
        }

        .btn-view {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #0891b2;
            background: #ecfeff;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #cffafe;
            transition: .2s;
        }

        .btn-view:hover {
            background: #0891b2;
            color: #fff;
        }

        .btn-hapus {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #e11d48;
            background: #fff1f2;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #ffe4e6;
            transition: .2s;
        }

        .btn-hapus:hover {
            background: #e11d48;
            color: #fff;
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

        .btn-logout-header {
            background: rgba(239, 68, 68, .15);
            color: #fff;
            padding: 10px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }
    </style>
</head>

<body>

    <aside class="main-sidebar">
        <a class="brand-link">
            <div class="logos">
                <img src="../assets/img/astra.jpeg">
                <img src="../assets/img/klk.jpeg">
            </div>
            PT KREASIJAYA ADHIKARYA
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

    <div class="main-content">
        <div class="header">
            <div>
                <h2>SISTEM INVOICE</h2>
            </div>
            <div id="clock" data-server-time="<?= date('Y-m-d H:i:s') ?>" style="font-size:14px; font-weight:500;"></div>
            <div class="header-actions">
                <a href="../auth/logout_proses.php" class="btn-logout-header" onclick="return confirm('Yakin ingin logout?')">Logout</a>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3>Data Pembayaran</h3>

                <div class="table-action">
                    <form method="GET" action="" class="filter-box">
                        <select name="bulan">
                            <option value="">-- Bulan --</option>
                            <?php
                            $months = [1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                            foreach ($months as $num => $name) {
                                $selected = ($filter_bulan == $num) ? 'selected' : '';
                                echo "<option value='$num' $selected>$name</option>";
                            }
                            ?>
                        </select>
                        <select name="tahun">
                            <option value="">-- Tahun --</option>
                            <?php
                            $start_year = 2022;
                            $current_year = date('Y');
                            for ($y = $current_year; $y >= $start_year; $y--) {
                                $selected = ($filter_tahun == $y) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn-filter">Filter</button>
                        <?php if ($filter_bulan || $filter_tahun): ?>
                            <a href="index.php" style="font-size: 11px; color: #ef4444; text-decoration: none; font-weight: bold; margin-left: 5px;">Reset</a>
                        <?php endif; ?>
                    </form>

                    <input type="text" id="searchPembayaran" placeholder="Cari nomor invoice..." autocomplete="off">

                    <?php if ($role === 'admin' || $role === 'user'): ?>
                        <a href="tambah.php" class="btn-add-pembayaran">Tambah Data</a>
                    <?php endif; ?>
                </div>
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
                            <th style="text-align:center">Bukti</th>
                            <?php if ($role === 'admin'): ?><th style="text-align:center">Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_lunas) > 0): ?>
                            <?php $no = 1;
                            while ($row = mysqli_fetch_assoc($query_lunas)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= $row['nomor_invoice'] ?></strong></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal_bayar'])) ?></td>
                                    <td class="price">Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                    <td><span style="background:#f1f5f9; padding:4px 8px; border-radius:6px; font-size:12px"><?= $row['metode'] ?? '-' ?></span></td>
                                    <td style="text-align:center">
                                        <?php if ($row['bukti']): ?>
                                            <a href="../uploads/bukti/<?= $row['bukti'] ?>" target="_blank" class="btn-view">Buka File</a>
                                        <?php else: ?>
                                            <span style="color:#cbd5e1">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($role === 'admin'): ?>
                                        <td style="text-align:center">
                                            <a href="hapus.php?id=<?= $row['id_pembayaran'] ?>" class="btn-hapus" onclick="return confirm('Hapus data ini?')">Hapus</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty">Data tidak ditemukan untuk periode ini.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

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
        // Realtime Search (JavaScript)
        document.getElementById("searchPembayaran").addEventListener("keyup", function() {
            let keyword = this.value.toLowerCase();
            let rows = document.querySelectorAll(".invoice-table tbody tr");
            rows.forEach(row => {
                if (!row.classList.contains('empty-row')) {
                    row.style.display = row.innerText.toLowerCase().includes(keyword) ? "" : "none";
                }
            });
        });

        // Realtime Clock
        const clockEl = document.getElementById('clock');
        let serverTime = new Date(clockEl.dataset.serverTime.replace(' ', 'T'));

        function updateClock() {
            serverTime.setSeconds(serverTime.getSeconds() + 1);
            clockEl.innerHTML = serverTime.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }) + ' • ' +
                serverTime.toLocaleTimeString('id-ID') + ' WIB';
        }
        setInterval(updateClock, 1000);
    </script>
</body>

</html>