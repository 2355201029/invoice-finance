<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'];

// --- LOGIKA FILTER ---
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

$query_filter = " WHERE i.status_arsip = 'Aktif' ";

if ($filter_bulan != '') {
    $query_filter .= " AND MONTH(i.tanggal_invoice) = '$filter_bulan' ";
}
if ($filter_tahun != '') {
    $query_filter .= " AND YEAR(i.tanggal_invoice) = '$filter_tahun' ";
}

// ✅ QUERY DENGAN FILTER
$qInvoice = mysqli_query($conn, "
    SELECT *
    FROM invoice i
    LEFT JOIN vendor v ON i.id_vendor = v.id_vendor
    $query_filter
    ORDER BY i.id_invoice DESC
");

function rupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Invoice | Sistem Invoice</title>

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
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #f4f7ff;
            overflow-x: hidden;
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
            z-index: 1000;
        }

        .brand-link {
            display: block;
            padding: 22px;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            background: rgba(255, 255, 255, .08);
            text-decoration: none;
            color: #fff;
        }

        .logos img {
            height: 40px;
            margin: 0 5px;
            background: #fff;
            padding: 4px;
            border-radius: 8px;
        }

        .sidebar {
            padding: 20px 15px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li a {
            display: block;
            padding: 12px 18px;
            margin-bottom: 10px;
            color: #e0e7ff;
            text-decoration: none;
            border-radius: 10px;
            transition: .3s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: rgba(255, 255, 255, .15);
            transform: translateX(6px);
        }

        .main-content {
            margin-left: 260px;
            padding: 28px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            padding: 28px 34px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .18);
            margin-bottom: 30px;
        }

        .table-card {
            background: #fff;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .08);
            width: 100%;
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        /* FILTER STYLES */
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
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-reset {
            background: #64748b;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 13px;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 1100px;
        }

        thead th {
            background: #f8fafc;
            padding: 14px 12px;
            font-weight: 600;
            text-align: left;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
        }

        .price {
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .belum-bayar {
            background: #fee2e2;
            color: #991b1b;
        }

        .dp {
            background: #fef3c7;
            color: #92400e;
        }

        .lunas {
            background: #dcfce7;
            color: #166534;
        }

        #searchInvoice {
            padding: 10px 18px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            width: 250px;
            outline: none;
            transition: 0.3s;
        }

        .btn-add-invoice {
            background: var(--primary);
            color: #fff !important;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-danger {
            background: #fee2e2;
            color: #dc2626;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
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

        .btn-logout-header {
            background: rgba(239, 68, 68, .18);
        }

        .btn-logout-header:hover {
            background: rgba(239, 68, 68, .35);
        }

        .hide-row {
            display: none !important;
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

        @media (max-width: 992px) {
            .main-sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .header {
                flex-direction: column;
                text-align: center;
            }

            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            #searchInvoice {
                width: 100%;
            }
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
            <h2>SISTEM INVOICE</h2>
            <div id="clock" data-server-time="<?= date('Y-m-d H:i:s') ?>" style="font-weight: 500;"></div>
            <a href="../auth/logout_proses.php" class="btn-logout-header"
                onclick="return confirm('Yakin ingin logout?')">
                <span class="logout-icon">⎋</span>
                <span class="logout-text">Logout</span>
            </a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3>Data Invoice</h3>
                <form method="GET" action="" class="filter-box">
                    <select name="bulan">
                        <option value="">-- Bulan --</option>
                        <?php
                        $nama_bulan = [1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                        foreach ($nama_bulan as $num => $nama): ?>
                            <option value="<?= $num ?>" <?= $filter_bulan == $num ? 'selected' : '' ?>><?= $nama ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="tahun">
                        <option value="">-- Tahun --</option>
                        <?php
                        $thn_skrg = date('Y');
                        for ($i = $thn_skrg; $i >= 2020; $i--): ?>
                            <option value="<?= $i ?>" <?= $filter_tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>

                    <button type="submit" class="btn-filter">Filter</button>
                    <?php if ($filter_bulan || $filter_tahun): ?>
                        <a href="index.php" class="btn-reset">Reset</a>
                    <?php endif; ?>
                </form>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <?php if ($role === 'admin' || $role === 'user'): ?>
                        <a href="tambah.php" class="btn-add-invoice">+ Tambah Invoice</a>
                    <?php endif; ?>
                    <input type="text" id="searchInvoice" placeholder="Cari nomor invoice atau vendor...">
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nomor Inv / PO</th>
                            <th>Vendor</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>DP</th>
                            <th class="text-center">DP %</th>
                            <th>Sisa</th>
                            <th class="text-center">Sisa %</th>
                            <th>Tgl DP</th>
                            <th>Tgl Lunas</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        if (mysqli_num_rows($qInvoice) > 0):
                            while ($r = mysqli_fetch_assoc($qInvoice)):
                                $status_class = ($r['status'] == 'Belum Bayar') ? 'belum-bayar' : (($r['status'] == 'DP') ? 'dp' : 'lunas');
                                $tgl_dp = $r['tanggal_dp'] ? date('d/m/y', strtotime($r['tanggal_dp'])) : '-';
                                $tgl_lunas = $r['tanggal_lunas'] ? date('d/m/y', strtotime($r['tanggal_lunas'])) : '-';
                        ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><strong><?= $r['nomor_invoice'] ?></strong></td>
                                    <td><?= $r['nama_vendor'] ?></td>
                                    <td><?= date('d/m/y', strtotime($r['tanggal_invoice'])) ?></td>
                                    <td class="price"><?= rupiah($r['total']) ?></td>
                                    <td class="price"><?= $r['dp_nominal'] ? rupiah($r['dp_nominal']) : '-' ?></td>
                                    <td class="text-center"><?= $r['dp_persen'] ?? 0 ?>%</td>
                                    <td class="price"><?= $r['sisa_nominal'] ? rupiah($r['sisa_nominal']) : '-' ?></td>
                                    <td class="text-center"><?= $r['sisa_persen'] ?? 0 ?>%</td>
                                    <td><?= $tgl_dp ?></td>
                                    <td><?= $tgl_lunas ?></td>
                                    <td class="text-center">
                                        <a href="detail.php?id=<?= $r['id_invoice'] ?>" style="text-decoration:none;">
                                            <span class="badge <?= $status_class ?>"><?= $r['status'] ?></span>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($role === 'admin'): ?>
                                            <a href="hapus.php?id=<?= $r['id_invoice'] ?>" class="btn-danger" onclick="return confirm('Hapus invoice ini?')">Hapus</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="13" class="text-center" style="padding: 20px;">Data tidak ditemukan untuk periode ini.</td>
                            </tr>
                        <?php endif; ?>
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
        // Realtime Search
        document.getElementById("searchInvoice").addEventListener("keyup", function() {
            const keyword = this.value.toLowerCase();
            const rows = document.querySelectorAll("tbody tr");
            rows.forEach(row => {
                if (row.cells.length > 1) { // Hindari row "Data tidak ditemukan"
                    const text = row.innerText.toLowerCase();
                    row.classList.toggle("hide-row", !text.includes(keyword));
                }
            });
        });

        // Realtime Clock
        const clockEl = document.getElementById('clock');
        let serverTime = new Date(clockEl.dataset.serverTime.replace(' ', 'T'));
        setInterval(() => {
            serverTime.setSeconds(serverTime.getSeconds() + 1);
            clockEl.innerHTML = serverTime.toLocaleString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }) + ' WIB';
        }, 1000);
    </script>
</body>

</html>