<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$vendor = mysqli_query($conn, "SELECT * FROM vendor ORDER BY nama_vendor ASC");
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
            background: #f4f7ff;
            font-family: 'Segoe UI', sans-serif
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .08)
        }

        h2 {
            margin-bottom: 20px
        }

        .form-group {
            margin-bottom: 15px
        }

        label {
            font-weight: 600;
            font-size: 14px
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            font-size: 14px
        }

        button {
            background: #4f46e5;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer
        }

        .back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 28px;
            padding: 10px 16px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            color: #374151;
            background: #f3f4f6;
            transition: all .25s ease;
        }

        .back:hover {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            transform: translateX(-4px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, .35);
        }

        .back::before {
            content: "←";
            font-size: 15px;
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
                <h2>➕ Tambah Invoice</h2>

                <form action="simpan.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nomor Invoice / PO</label>
                        <input type="text" name="nomor_invoice" required>
                    </div>

                    <div class="form-group">
                        <label>Vendor</label>
                        <select name="id_vendor" required>
                            <option value="">-- Pilih Vendor --</option>
                            <?php while ($v = mysqli_fetch_assoc($vendor)): ?>
                                <option value="<?= $v['id_vendor'] ?>"><?= $v['nama_vendor'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tanggal DP</label>
                        <input type="date" name="tanggal_dp" id="tanggal_dp">
                    </div>

                    <div class="form-group">
                        <label>Tanggal Lunas</label>
                        <input type="date" name="tanggal_lunas" id="tanggal_lunas">
                    </div>

                    <div class="form-group">
                        <label>Total</label>
                        <input type="number" name="total" required>
                    </div>

                    <div class="form-group">
                        <label>Persentase DP (%)</label>
                        <input type="number" name="dp_persen" class="form-control" min="1" max="100"
                            placeholder="Contoh: 30 (kosongkan jika langsung lunas)">
                        <small style="color:#6b7280">
                            Isi jika invoice dibayar DP. Kosongkan jika langsung lunas.
                        </small>
                    </div>

                    <!-- <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Belum Bayar">Belum Bayar</option>
                            <option value="DP">DP</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div> -->

                    <div class="form-group">
                        <label>Dokumen Invoice (PDF)</label>
                        <input type="file" name="file_invoice" accept="application/pdf">
                        <small style="color:#6b7280">
                            Upload dokumen invoice (PDF). Opsional.
                        </small>
                    </div>

                    <div class="btn-group">
                        <a href="index.php" class="back">Kembali</a>
                        <button type="submit" name="simpan" class="btn-save">Simpan Invoice</button>
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
        const totalInput = document.getElementById('total');
        const dpInput = document.getElementById('dp_persen');

        const dpNominal = document.getElementById('dp_nominal');
        const sisaNominal = document.getElementById('sisa_nominal');

        const statusSelect = document.querySelector('select[name="status"]');
        const tglDP = document.getElementById('tanggal_dp');
        const tglLunas = document.getElementById('tanggal_lunas');

        /* =========================
           FORMAT RUPIAH
        ========================= */
        function rupiah(angka) {
            return "Rp " + Number(angka).toLocaleString('id-ID');
        }

        /* =========================
           HITUNG OTOMATIS
        ========================= */
        function hitung() {
            let total = parseFloat(totalInput.value) || 0;
            let dp = parseFloat(dpInput.value) || 0;

            if (dp > 100) dp = 100;
            if (dp < 0) dp = 0;

            let dpNom = (dp / 100) * total;
            let sisa = total - dpNom;

            dpNominal.value = rupiah(Math.round(dpNom));
            sisaNominal.value = rupiah(Math.round(sisa));

            /* =========================
               AUTO STATUS
            ========================= */
            if (dp === 0) {
                statusSelect.value = 'Belum Bayar';
            } else if (dp > 0 && dp < 100) {
                statusSelect.value = 'DP';
            } else {
                statusSelect.value = 'Lunas';
            }

            toggleTanggal();
        }

        /* =========================
           AKTIFKAN TANGGAL
        ========================= */
        function toggleTanggal() {

            if (statusSelect.value === 'DP') {
                tglDP.disabled = false;
                tglLunas.disabled = true;
                tglLunas.value = '';
            } else if (statusSelect.value === 'Lunas') {
                tglDP.disabled = true;
                tglDP.value = '';
                tglLunas.disabled = false;
            } else {
                tglDP.disabled = true;
                tglLunas.disabled = true;
                tglDP.value = '';
                tglLunas.value = '';
            }
        }

        /* =========================
           VALIDASI TANGGAL
        ========================= */
        tglLunas.addEventListener('change', function() {
            if (tglDP.value && tglLunas.value < tglDP.value) {
                alert("Tanggal Lunas tidak boleh lebih kecil dari Tanggal DP!");
                tglLunas.value = '';
            }
        });

        /* =========================
           EVENT
        ========================= */
        totalInput.addEventListener('input', hitung);
        dpInput.addEventListener('input', hitung);
        statusSelect.addEventListener('change', toggleTanggal);

        /* =========================
           INIT
        ========================= */
        toggleTanggal();
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