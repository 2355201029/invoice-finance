<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';

/* =====================
   CEK LOGIN
===================== */
if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =====================
   VARIABEL WAJIB
===================== */
$currentPage = $_SERVER['REQUEST_URI']; // FIX sidebar active
$where = "";                             // FIX ERROR line 28

/* =====================
   SESSION
===================== */
$role = $_SESSION['role'];        // admin / user
$nama = $_SESSION['nama'] ?? 'User';

/* =====================
   QUERY AMAN
===================== */
$query = mysqli_query($conn, "
    SELECT * FROM invoice
    $where
    ORDER BY tanggal_invoice DESC
");


/* =====================
    FILTER TAHUN (WAJIB)
===================== */
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

/* =====================
    STATISTIK DASHBOARD
===================== */
$total_invoice = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM invoice"))[0];
$invoice_lunas = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM invoice WHERE status='Lunas'"))[0];
$invoice_dp    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM invoice WHERE status='DP'"))[0];
$vendor        = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM vendor"))[0];

/* =====================
    DATA TABEL DASHBOARD
===================== */

// Semua Invoice
$data_all = mysqli_query($conn, "
    SELECT i.*, v.nama_vendor
    FROM invoice i
    JOIN vendor v ON i.id_vendor = v.id_vendor
    ORDER BY i.tanggal_invoice DESC
");

// Invoice Lunas
$data_lunas = mysqli_query($conn, "
    SELECT i.*, v.nama_vendor
    FROM invoice i
    JOIN vendor v ON i.id_vendor = v.id_vendor
    WHERE i.status = 'Lunas'
    ORDER BY i.tanggal_invoice DESC
");

// Invoice DP
$data_dp = mysqli_query($conn, "
    SELECT i.*, v.nama_vendor
    FROM invoice i
    JOIN vendor v ON i.id_vendor = v.id_vendor
    WHERE i.status = 'DP'
    ORDER BY i.tanggal_invoice DESC
");

// Vendor
$data_vendor = mysqli_query($conn, "
    SELECT nama_vendor, alamat_vendor, no_telp, email
    FROM vendor
    ORDER BY nama_vendor ASC
");


/* =====================
    GRAFIK INVOICE BULANAN
===================== */
$bulan = [];
$total_invoice_bulan = [];

$queryGrafik = mysqli_query($conn, "
    SELECT MONTH(tanggal_invoice) AS bulan, COUNT(*) AS total
    FROM invoice
    WHERE YEAR(tanggal_invoice) = '$tahun'
    GROUP BY MONTH(tanggal_invoice)
    ORDER BY MONTH(tanggal_invoice)
");

$namaBulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

while ($row = mysqli_fetch_assoc($queryGrafik)) {
    $bulan[] = $namaBulan[$row['bulan']];
    $total_invoice_bulan[] = $row['total'];
}
$nama_user = $_SESSION['nama'] ?? 'User';

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | Sistem Invoice</title>

    <style>
    :root {
        --primary: #2f5bea;
        --secondary: #4f46e5;
        --sidebar: #2b3a8f;
        --sidebar-dark: #233070;
    }

    /* RESET */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', system-ui, sans-serif;
    }

    body {
        background: #f4f7ff;
    }

    /* ================= SIDEBAR ================= */
    .main-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 260px;
        height: 100vh;
        background: linear-gradient(180deg, var(--sidebar), var(--sidebar-dark));
        color: #fff;
    }

    .brand-link {
        display: block;
        padding: 22px;
        font-size: 20px;
        font-weight: 700;
        text-align: center;
        background: rgba(255, 255, 255, .08);
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

    /* ================= MAIN CONTENT ================= */
    .main-content {
        margin-left: 260px;
        width: calc(100% - 260px);
        padding: 28px;
    }

    /* FULL WIDTH CONTAINER */
    .container {
        width: 100%;
        max-width: 100%;
    }

    /* ================= HEADER ================= */
    .header {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: #fff;
        padding: 28px 34px;
        border-radius: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 15px 35px rgba(0, 0, 0, .18);
    }

    .header h2 {
        font-size: 26px;
    }

    .header p {
        margin-top: 6px;
        opacity: .95;
    }

    .logos img {
        height: 50px;
        margin-left: 14px;
        background: #fff;
        padding: 6px 10px;
        border-radius: 12px;
    }

    /* ================= STATS ================= */
    .stats {
        margin-top: 30px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
    }

    .card {
        background: #fff;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
        position: relative;
        transition: .3s;
    }

    .card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        height: 6px;
        width: 100%;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .card:hover {
        transform: translateY(-6px);
    }

    .card h2 {
        font-size: 42px;
        color: var(--primary);
    }

    .card p {
        margin-top: 10px;
        color: #6b7280;
    }

    /* ================= RESPONSIVE ================= */
    @media(max-width: 900px) {
        .main-sidebar {
            width: 220px;
        }

        .main-content {
            margin-left: 220px;
            width: calc(100% - 220px);
        }
    }

    .chart-card {
        margin-top: 35px;
        background: #fff;
        border-radius: 18px;
        padding: 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, .08);
    }

    .chart-card h3 {
        margin-bottom: 20px;
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

    .card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .card-link .card {
        cursor: pointer;
    }

    .card-link .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 18px 40px rgba(0, 0, 0, .15);
    }

    /* ================= TOOLTIP ================= */
    .card-link {
        position: relative;
    }

    .card-link::after {
        content: "Klik untuk melihat detail";
        position: absolute;
        bottom: -42px;
        left: 50%;
        transform: translateX(-50%) translateY(8px);

        background: #111827;
        color: #fff;
        font-size: 12px;
        font-weight: 500;

        padding: 6px 12px;
        border-radius: 8px;
        white-space: nowrap;

        opacity: 0;
        pointer-events: none;
        transition: all .25s ease;
    }

    .card-link::before {
        content: "";
        position: absolute;
        bottom: -14px;
        left: 50%;
        transform: translateX(-50%) translateY(8px);

        border-width: 6px;
        border-style: solid;
        border-color: #111827 transparent transparent transparent;

        opacity: 0;
        transition: all .25s ease;
    }

    .card-link:hover::after,
    .card-link:hover::before {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .table-rekap {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .table-rekap th {
        background: #f1f5f9;
        padding: 12px;
    }

    .table-rekap td {
        padding: 12px;
        border-top: 1px solid #e5e7eb;
    }

    /* ===== SEARCH BOX ===== */
    .search-wrapper {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 16px;
    }

    .search-wrapper input {
        width: 280px;
        padding: 10px 16px 10px 38px;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        background: #fff url("data:image/svg+xml,%3Csvg fill='none' stroke='%239ca3af' stroke-width='2' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M21 21l-4.35-4.35M16 11a5 5 0 11-10 0 5 5 0 0110 0z'%3E%3C/path%3E%3C/svg%3E") no-repeat 14px center;
        background-size: 18px;
        font-size: 14px;
        transition: .25s ease;
        outline: none;
    }

    .search-wrapper input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(47, 91, 234, .15);
    }

    .hide-row {
        display: none !important;
    }

    /* ===== BADGE STATUS INVOICE ===== */
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        min-width: 70px;
    }

    .status-lunas {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .status-dp {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .status-belum {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
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


    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="container">

            <!-- HEADER -->
            <div class="header">
                <div>
                    <h2>SISTEM INVOICE</h2>
                </div>
                <div>
                    <p style="margin-top:6px; font-size:14px;">
                        <?php if ($role === 'admin'): ?>
                        Selamat Datang <b>Admin <?= htmlspecialchars($nama) ?></b> 👑
                        <?php else: ?>
                        Selamat Datang <b><?= htmlspecialchars($nama) ?></b> 👋
                        <?php endif; ?>
                    </p>
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

            <!-- STATS -->

            <div class="stats">

                <div class="card card-link" onclick="showTable('all')">
                    <h1><?= $total_invoice ?></h1>
                    <p>Total Invoice</p>
                </div>

                <div class="card card-link" onclick="showTable('lunas')">
                    <h1><?= $invoice_lunas ?></h1>
                    <p>Invoice Lunas</p>
                </div>

                <div class="card card-link" onclick="showTable('dp')">
                    <h1><?= $invoice_dp ?></h1>
                    <p>Invoice DP</p>
                </div>

                <div class="card card-link" onclick="showTable('vendor')">
                    <h1><?= $vendor ?></h1>
                    <p>Jumlah Vendor</p>
                </div>
            </div>

            <!-- ================= TABEL DASHBOARD ================= -->
            <div id="table-area" style="display:none; margin-top:30px">

                <div class="chart-card">
                    <h3 id="table-title">Tabel Data</h3>

                    <!-- TABEL INVOICE -->
                    <!-- SEARCH -->
                    <div style="margin-bottom:12px; display:flex; justify-content:flex-end;">
                        <input type="text" id="searchInput" placeholder="🔍 Cari invoice / vendor..." style="
                    padding:8px 14px;
                    width:260px;
                    border-radius:10px;
                    border:1px solid #d1d5db;
                    outline:none;
                " onkeyup="searchTable()">
                    </div>

                    <table class="table-rekap" id="table-invoice" style="display:none">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Invoice</th>
                                <th>Vendor</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-body"></tbody>
                    </table>

                    <!-- TABEL VENDOR -->
                    <table class="table-rekap" id="table-vendor" style="display:none">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Vendor</th>
                                <th>Alamat</th>
                                <th>No Telp</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; foreach($data_vendor as $v): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($v['nama_vendor']) ?></td>
                                <td><?= htmlspecialchars($v['alamat_vendor']) ?></td>
                                <td><?= htmlspecialchars($v['no_telp']) ?></td>
                                <td><?= htmlspecialchars($v['email']) ?></td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>

                </div>
            </div><br>
            <form method="GET" style="margin-bottom:15px; display:flex; gap:10px; align-items:center;">
                <label><b>Tahun:</b></label>
                <select name="tahun" onchange="this.form.submit()" style="
            padding:8px 12px;
            border-radius:8px;
            border:1px solid #d1d5db;
            outline:none;">
                    <?php
                for ($i = date('Y'); $i >= 2020; $i--) {
                    $selected = ($i == $tahun) ? 'selected' : '';
                    echo "<option value='$i' $selected>$i</option>";
                }
            ?>
                </select>
            </form>

            <div class="chart-card">
                <h3>Grafik Invoice Per Tahun (<?= $tahun ?>)</h3>
                <canvas id="invoiceChart" height="90"></canvas>
            </div>

            <div class="chart-card" id="table-area" style="display:none">
                <h3 id="table-title"></h3>

                <!-- TABEL INVOICE -->
                <table class="table-rekap" id="table-invoice" style="display:none">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Invoice</th>
                            <th>Vendor</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-body"></tbody>
                </table>

                <!-- TABEL VENDOR -->
                <table class="table-rekap" id="table-vendor" style="display:none">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Vendor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while($v=mysqli_fetch_assoc($data_vendor)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $v['nama_vendor'] ?></td>
                        </tr>
                        <?php endwhile; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('invoiceChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($bulan) ?>,
            datasets: [{
                label: 'Total Invoice',
                data: <?= json_encode($total_invoice_bulan) ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,.15)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    </script>

    <script>
    function searchTable() {
        const keyword = document.getElementById("searchInput").value.toLowerCase();

        // Cek tabel mana yang sedang tampil
        const tableInvoice = document.getElementById("table-invoice");
        const tableVendor = document.getElementById("table-vendor");

        let rows = [];

        if (tableInvoice.style.display === "table") {
            rows = tableInvoice.querySelectorAll("tbody tr");
        } else if (tableVendor.style.display === "table") {
            rows = tableVendor.querySelectorAll("tbody tr");
        }

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(keyword) ? "" : "none";
        });
    }
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
<?php
$bulan = [];
$total_invoice_bulan = [];

$queryGrafik = mysqli_query($conn, "
    SELECT MONTH(tanggal_invoice) AS bulan, COUNT(*) AS total
    FROM invoice
    WHERE YEAR(tanggal_invoice) = '$tahun'
    GROUP BY MONTH(tanggal_invoice)
    ORDER BY MONTH(tanggal_invoice)
");

$namaBulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

while ($row = mysqli_fetch_assoc($queryGrafik)) {
    $bulan[] = $namaBulan[$row['bulan']];
    $total_invoice_bulan[] = $row['total'];
}
?>

<script>
const allData = <?= json_encode(mysqli_fetch_all($data_all, MYSQLI_ASSOC)) ?>;
const lunasData = <?= json_encode(mysqli_fetch_all($data_lunas, MYSQLI_ASSOC)) ?>;
const dpData = <?= json_encode(mysqli_fetch_all($data_dp, MYSQLI_ASSOC)) ?>;

function showTable(type) {
    document.getElementById('table-area').style.display = 'block';
    document.getElementById('table-invoice').style.display = 'none';
    document.getElementById('table-vendor').style.display = 'none';

    let title = '';
    let data = [];

    if (type === 'all') {
        title = 'Tabel Semua Invoice';
        data = allData;
    } else if (type === 'lunas') {
        title = 'Tabel Invoice Lunas';
        data = lunasData;
    } else if (type === 'dp') {
        title = 'Tabel Invoice DP';
        data = dpData;
    } else if (type === 'vendor') {
        title = 'Tabel Vendor';
        document.getElementById('table-title').innerText = title;
        document.getElementById('table-vendor').style.display = 'table';
        return;
    }

    document.getElementById('table-title').innerText = title;
    document.getElementById('table-invoice').style.display = 'table';

    let html = '';
    data.forEach((row, i) => {
        html += `
            <tr>
                <td>${i+1}</td>
                <td>${row.nomor_invoice}</td>
                <td>${row.nama_vendor}</td>
                <td>${row.tanggal_invoice}</td>
                <td>Rp ${Number(row.total).toLocaleString('id-ID')}</td>
                <td>${row.status}</td>
            </tr>
        `;
    });

    document.getElementById('invoice-body').innerHTML = html;
}
</script>

</html>