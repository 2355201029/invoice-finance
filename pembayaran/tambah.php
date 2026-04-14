<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

// ambil invoice
$invoice = mysqli_query($conn, "
    SELECT id_invoice, nomor_invoice, total
    FROM invoice
    ORDER BY created_at DESC
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

    .empty {
        text-align: center;
        color: #6b7280;
        padding: 20px
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
        background: #f4f7fe;
        font-family: 'Segoe UI', sans-serif;
    }

    .card {
        width: 480px;
        background: #fff;
        margin: 80px auto;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, .08);
    }

    .card h2 {
        margin-bottom: 20px;
        font-size: 22px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
    }

    input,
    select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .btn {
        padding: 10px 18px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-primary {
        background: #4f46e5;
        color: #fff;
    }

    .btn-back {
        background: #eef2ff;
    }

    .actions {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
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

        <div class="container">
            <div class="card">
                <h2>➕ Tambah Pembayaran</h2>

                <form action="simpan.php" method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Invoice</label>
                        <select name="id_invoice" required>
                            <option value="">-- Pilih Invoice --</option>
                            <?php while($row = mysqli_fetch_assoc($invoice)): ?>
                            <option value="<?= $row['id_invoice'] ?>">
                                <?= $row['nomor_invoice'] ?> | Rp <?= number_format($row['total'],0,',','.') ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Bayar</label>
                        <input type="date" name="tanggal_bayar" required>
                    </div>

                    <div class="form-group">
                        <label>Status Pembayaran</label>
                        <select name="status_pembayaran" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="DP">DP</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <select name="metode" required>
                            <option value="">-- Pilih Metode --</option>
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Cek">Cek</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Upload Bukti (PDF/JPG, Maks 5 MB)</label>
                        <input type="file" name="bukti" id="bukti" accept=".pdf,.jpg,.jpeg" data-max-size="5242880">
                        <small style="color:#6b7280;font-size:13px;">
                            Maksimal ukuran file 5 MB
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" placeholder="Opsional">
                    </div>

                    <div class="actions">
                        <a href="index.php"><button type="button" class="btn btn-back">← Kembali</button></a>
                        <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                    </div>
                </form>
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
    const dpInput = document.querySelector('input[name="dp_persen"]');
    const statusSelect = document.querySelector('select[name="status"]');

    function toggleDP() {
        if (statusSelect.value === 'DP') {
            dpInput.disabled = false;
            dpInput.required = true;
        } else {
            dpInput.value = '';
            dpInput.disabled = true;
            dpInput.required = false;
        }
    }

    statusSelect.addEventListener('change', toggleDP);
    toggleDP();

    dpInput.addEventListener('input', function() {
        if (this.value > 100) this.value = 100;
        if (this.value < 0) this.value = '';
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
    document.getElementById('bukti').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const maxSize = 5 * 1024 * 1024; // 5 MB

        if (file.size > maxSize) {
            alert('Ukuran file terlalu besar! Maksimal 5 MB.');
            this.value = ''; // reset input
        }
    });
    </script>

</body>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLE JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

</html>