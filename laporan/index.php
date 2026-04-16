<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$currentPage = $_SERVER['PHP_SELF'];

// Ambil filter dari URL (tanpa default date agar bisa tampil semua)
$filter_bulan = $_GET['bulan'] ?? '';
$filter_tahun = $_GET['tahun'] ?? '';

// Bangun Clause WHERE secara dinamis
$where_clause = "";
if ($filter_bulan != '') {
    $where_clause .= " AND l.periode_bulan = '$filter_bulan'";
}
if ($filter_tahun != '') {
    $where_clause .= " AND l.periode_tahun = '$filter_tahun'";
}

// ================= DATA DP / PROSES =================
$data = mysqli_query($conn, "
    SELECT 
        l.*, 
        i.nomor_invoice, 
        v.nama_vendor, 
        i.tanggal_invoice, 
        i.total as total_inv,
        i.tanggal_dp,
        i.tanggal_lunas
    FROM laporan l
    JOIN invoice i ON l.id_invoice = i.id_invoice
    JOIN vendor v ON i.id_vendor = v.id_vendor
    WHERE l.status IN ('Proses', 'Lunas')
    $where_clause
    ORDER BY i.tanggal_invoice DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Invoice | Sistem Invoice</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Style CSS Anda tetap sama... */
        :root {
            --primary: #2f5bea;
            --secondary: #4f46e5;
            --sidebar: #2b3a8f;
            --sidebar-dark: #233070;
            --success: #10b981;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif
        }

        body {
            background: #f4f7ff;
            color: #334155;
        }

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
            font-size: 18px;
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
            margin-bottom: 8px;
            color: #e0e7ff;
            text-decoration: none;
            border-radius: 10px;
            transition: .3s
        }

        .sidebar ul li a.active {
            background: rgba(255, 255, 255, .15);
            transform: translateX(6px)
        }

        .main-content {
            margin-left: 260px;
            padding: 28px;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            padding: 25px 34px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px rgba(47, 91, 234, 0.2);
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.2);
            color: #fff;
            padding: 8px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.5);
        }

        .card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            margin-top: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        }

        .filter-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            padding: 10px 18px;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
        }

        .filter-group select {
            border: none;
            background: transparent;
            font-weight: 600;
            outline: none;
            cursor: pointer;
            color: #1e293b;
        }

        .btn-filter {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-filter:hover {
            background: var(--secondary);
            transform: scale(1.05);
        }

        .btn-generate {
            background: #fff;
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 8px 20px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-generate:hover {
            background: var(--primary);
            color: #fff;
        }

        .search-wrapper {
            position: relative;
        }

        #searchInvoice {
            padding: 12px 20px 12px 45px;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            width: 300px;
            background: #f8fafc;
            transition: 0.3s;
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .logos img {
            height: 50px;
            margin-left: 14px;
            background: #fff;
            padding: 6px 10px;
            border-radius: 12px
        }

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            margin-top: 10px;
        }

        .table-custom th {
            padding: 15px;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: left;
        }

        .table-custom tbody tr {
            background: #fff;
            transition: 0.2s;
        }

        .table-custom td {
            padding: 16px 15px;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .badge-lunas {
            background: #dcfce7;
            color: #166534;
        }

        .badge-proses {
            background: #fef3c7;
            color: #92400e;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
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
                <h2 style="letter-spacing: -1px;">Laporan Transaksi</h2>
                <div id="clock" data-server-time="<?= date('Y-m-d H:i:s') ?>" style="opacity: 0.8; font-size: 13px;"></div>
            </div>
            <a href="../auth/logout_proses.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">Logout ⎋</a>
        </div>

        <div class="card">
            <div class="filter-row">
                <form method="GET" action="" class="filter-group">
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

                <div style="display:flex; gap:10px;">
                    <form action="generate.php" method="POST">
                        <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
                        <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
                        <button type="submit" class="btn-generate">🔄 Generate Laporan</button>
                    </form>
                    <div class="search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchInvoice" placeholder="Cari vendor atau no. invoice...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Invoice & Vendor</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                            <th>Info Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($row = mysqli_fetch_assoc($data)):

                            $isLunas = $row['status'] == 'Lunas';
                        ?>

                            <tr class="row-data">
                                <td><?= $no++ ?></td>

                                <!-- Invoice -->
                                <td>
                                    <div style="font-weight:700; color:#1e293b;">
                                        <?= $row['nomor_invoice'] ?>
                                    </div>
                                    <div style="font-size:12px; color:#64748b;">
                                        <?= $row['nama_vendor'] ?>
                                    </div>
                                </td>

                                <!-- Nominal -->
                                <td style="font-weight:700; color: <?= $isLunas ? 'var(--success)' : 'var(--primary)' ?>;">
                                    Rp <?= number_format($row['total_inv'], 0, ',', '.') ?>
                                </td>

                                <!-- Status -->
                                <td>
                                    <?php if ($isLunas): ?>
                                        <span class="badge badge-lunas">LUNAS</span>
                                    <?php else: ?>
                                        <span class="badge badge-proses">PROSES / DP</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Tanggal -->
                                <td>
                                    <?php if ($isLunas): ?>
                                        <div style="font-size:14px; color:#64748b;">
                                            DP: <?= $row['tanggal_dp'] ? date('d/m/Y', strtotime($row['tanggal_dp'])) : '-' ?>
                                        </div>
                                        <div style="font-size:14px; color:#059669; font-weight:700;">
                                            Lunas: <?= $row['tanggal_lunas'] ? date('d/m/Y', strtotime($row['tanggal_lunas'])) : '-' ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="font-size:12px; color:#64748b;">
                                            Tgl DP:
                                            <span style="color:#1e293b; font-weight:600;">
                                                <?= $row['tanggal_dp'] ? date('d/m/Y', strtotime($row['tanggal_dp'])) : 'Belum Bayar' ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                        <?php if ($no == 1): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:50px; color:#94a3b8;">Tidak ada data pada periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="footer">
            © <?= date('Y') ?> <strong>PT KREASIJAYA ADHIKARYA</strong>. All Rights Reserved.
        </div>
    </div>

    <script>
        document.getElementById("searchInvoice").addEventListener("keyup", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".row-data").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(val) ? "" : "none";
            });
        });

        const clockEl = document.getElementById('clock');
        let serverTime = new Date(clockEl.dataset.serverTime.replace(' ', 'T'));

        function updateClock() {
            serverTime.setSeconds(serverTime.getSeconds() + 1);
            clockEl.innerHTML = serverTime.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            }) + ' • ' + serverTime.toLocaleTimeString('id-ID') + ' WIB';
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>

</html>