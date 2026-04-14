<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* ===============================
QUERY DATA ARSIP INVOICE
================================ */
$sql = "
SELECT 
    i.id_invoice,
    i.nomor_invoice,
    v.nama_vendor,
    i.total,
    i.status,
    i.tanggal_arsip
FROM invoice i
JOIN vendor v ON i.id_vendor = v.id_vendor
WHERE i.status_arsip = 'Arsip'
ORDER BY i.tanggal_arsip DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
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

    .btn-add {
        background: linear-gradient(135deg, #4f46e5, #2f5bea);
        color: #fff;
        padding: 10px 18px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px
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

    /* ===== CARD ===== */
    .card-arsip {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        max-width: 100%;
    }

    .card-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
    }

    .card-header p {
        margin-top: 4px;
        color: #64748b;
        font-size: 14px;
    }

    /* ===== TABLE ===== */
    .table-responsive {
        overflow-x: auto;
        margin-top: 20px;
    }

    .table-arsip {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .table-arsip thead th {
        text-align: left;
        font-size: 13px;
        color: #475569;
        padding: 12px;
    }

    .table-arsip tbody tr {
        background: #f8fafc;
        transition: all 0.3s ease;
    }

    .table-arsip tbody tr:hover {
        background: #eef2ff;
        transform: scale(1.005);
    }

    .table-arsip td {
        padding: 14px 12px;
        font-size: 14px;
        color: #334155;
    }

    .table-arsip tbody tr td:first-child {
        border-radius: 12px 0 0 12px;
    }

    .table-arsip tbody tr td:last-child {
        border-radius: 0 12px 12px 0;
    }

    /* ===== BADGE ===== */
    .badge-success {
        background: #dcfce7;
        color: #166534;
        padding: 5px 14px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 20px;
    }

    /* ===== TEXT ===== */
    .invoice-number {
        font-weight: 600;
        color: #2563eb;
    }

    .text-right {
        text-align: right;
    }

    .empty-data {
        text-align: center;
        padding: 30px;
        color: #94a3b8;
        font-size: 14px;
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

    /* ===== SEARCH ARSIP ===== */
    .table-action {
        margin: 18px 0;
        display: flex;
        justify-content: flex-end;
    }

    #searchArsip {
        width: 280px;
        padding: 12px 18px 12px 44px;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        font-size: 14px;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.868-3.834zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 16px center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        transition: all .3s ease;
    }

    #searchArsip::placeholder {
        color: #9ca3af;
    }

    #searchArsip:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, .18),
            0 12px 30px rgba(0, 0, 0, .12);
        transform: translateY(-1px);
    }

    #searchArsip:hover {
        border-color: #c7d2fe;
    }

    @media(max-width:768px) {
        .table-action {
            justify-content: center;
        }

        #searchArsip {
            width: 100%;
        }
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

    /* Base button */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;

        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;

        border-radius: 8px;
        text-decoration: none;
        border: none;

        transition: all 0.25s ease;
    }

    /* DETAIL – biru informatif */
    .btn-info {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
    }

    .btn-info:hover {
        background: #2563eb;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.35);
    }

    /* Klik */
    .btn:active {
        transform: scale(0.96);
    }

    /* Badge Base */
    .badge {
        display: inline-block;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 999px;
        text-align: center;
    }

    /* LUNAS */
    .badge-success {
        color: #166534;
        background: rgba(34, 197, 94, 0.18);
        border: 1px solid rgba(34, 197, 94, 0.4);
    }

    /* BELUM / LAINNYA */
    .badge-warning {
        color: #92400e;
        background: rgba(251, 191, 36, 0.18);
        border: 1px solid rgba(251, 191, 36, 0.4);
    }

    /* ================= AKSI ================= */
    .aksi {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    /* FORM INLINE */
    .inline-form {
        display: inline-block;
        margin: 0;
    }

    /* BASE SMALL BUTTON */
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 8px;
    }

    /* UPLOAD BUTTON */
    .btn-upload {
        background: rgba(16, 185, 129, .12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, .35);
        cursor: pointer;
    }

    .btn-upload:hover {
        background: #059669;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(16, 185, 129, .35);
    }

    .btn-upload input {
        display: none;
    }

    .btn-upload .icon {
        font-size: 14px;
        margin-right: 4px;
    }

    /* ACTIVE */
    .btn-upload:active {
        transform: scale(.96);
    }

    /* ===== KHUSUS TABEL ARSIP ===== */
    .table-arsip thead th {
        background: linear-gradient(135deg, #e0f2fe, #dbeafe);
        color: #1e3a8a;
        font-weight: 600;
        border-bottom: 2px solid #042043;
    }

    /* Garis atas sebagai penanda arsip */
    .table-arsip {
        border-top: 5px solid #2563eb;
    }

    /* Hover arsip lebih halus */
    .table-arsip tbody tr:hover {
        background: #eff6ff;
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

        <!-- HEADER -->
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

        <div class="card-arsip">
            <div class="card-header">
                <h3>📁 Arsip Invoice</h3>
                <p>Daftar invoice yang telah selesai dan diarsipkan</p>
            </div>

            <div class="table-action">
                <input type="text" id="searchArsip" placeholder="Cari data arsip..." autocomplete="off">
            </div>

            <div class="table-responsive">
                <table class="table-arsip">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Invoice</th>
                            <th>Vendor</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal Arsip</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) :
            ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nomor_invoice']) ?></td>
                            <td><?= htmlspecialchars($row['nama_vendor']) ?></td>
                            <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($row['status'] == 'Lunas'): ?>
                                <span class="badge badge-success"><i class="fa fa-check-circle"></i> Lunas </span>
                                <?php else: ?>
                                <span class="badge badge-warning"><?= $row['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($row['tanggal_arsip'])) ?></td>

                            <!-- AKSI -->
                            <td class="aksi">
                                <!-- DETAIL -->
                                <a href="detail.php?id=<?= $row['id_invoice'] ?>" class="btn btn-info btn-sm">
                                    Detail
                                </a>

                                <!-- UPLOAD -->
                                <form action="../invoice/upload_dokumen.php" method="post" enctype="multipart/form-data"
                                    class="inline-form">

                                    <input type="hidden" name="id_invoice" value="<?= $row['id_invoice'] ?>">
                                </form>
                            </td>

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

    <script>
    document.getElementById('searchArsip').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('table tr');

        rows.forEach((row, index) => {
            if (index === 0) return; // skip header

            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
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

</body>

</html>