<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id_invoice = (int) $_GET['id'];

// DATA INVOICE
$qInvoice = mysqli_query($conn, "
    SELECT * FROM invoice WHERE id_invoice = $id_invoice LIMIT 1
");

if (!$qInvoice || mysqli_num_rows($qInvoice) == 0) {
    die("Invoice tidak ditemukan");
}

$invoice = mysqli_fetch_assoc($qInvoice);

// DATA VENDOR
$qVendor = mysqli_query($conn, "SELECT * FROM vendor ORDER BY nama_vendor ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Invoice</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
    /* RESET */
    * {
        box-sizing: border-box;
        font-family: 'Segoe UI', sans-serif;
    }

    /* PAGE */
    body {
        background: #f4f6fb;
        margin: 0;
        padding: 30px;
    }

    /* CARD */
    .form-card {
        max-width: 900px;
        margin: auto;
        background: #ffffff;
        border-radius: 14px;
        padding: 28px 32px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
    }

    /* TITLE */
    .form-card h2 {
        margin: 0 0 25px;
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* LABEL */
    label {
        font-size: 14px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
        display: block;
    }

    /* INPUT, SELECT */
    input[type="text"],
    input[type="number"],
    input[type="date"],
    select,
    input[type="file"] {
        width: 100%;
        padding: 11px 14px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 14px;
        transition: .2s;
    }

    input:focus,
    select:focus {
        outline: none;
        border-color: #6366f1;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
    }

    /* FORM SPACING */
    form>label {
        margin-top: 18px;
    }

    /* SMALL NOTE */
    small {
        display: block;
        margin-top: 5px;
        color: #64748b;
        font-size: 12px;
    }

    /* ACTION */
    form div:last-child {
        margin-top: 28px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    /* BUTTON */
    button {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: none;
        color: white;
        padding: 12px 22px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: .25s;
    }

    button:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(99, 102, 241, .35);
    }

    /* LINK BACK */
    a {
        color: #6366f1;
        text-decoration: none;
        font-weight: 600;
    }

    a:hover {
        text-decoration: underline;
    }


    /* ACTION BUTTONS */
    .form-action {
        margin-top: 28px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    /* BACK BUTTON */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 12px 20px;
        border-radius: 10px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 600;
        font-size: 14px;
        border: 1px solid #e2e8f0;
        transition: .25s;
    }

    .btn-back:hover {
        background: #e2e8f0;
        color: #1e293b;
        transform: translateY(-1px);
    }

    /* PRIMARY BUTTON */
    .btn-primary {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: none;
        color: white;
        padding: 12px 22px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: .25s;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(99, 102, 241, .35);
    }
    </style>
</head>

<body>

    <div class="form-card">
        <h2>✏️ Edit Invoice</h2>

        <form action="update.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="id_invoice" value="<?= $invoice['id_invoice'] ?>">

            <label>Nomor Invoice / PO</label>
            <input type="text" name="nomor_invoice" value="<?= htmlspecialchars($invoice['nomor_invoice']) ?>" required>

            <label>Vendor</label>
            <select name="id_vendor" required>
                <option value="">-- Pilih Vendor --</option>
                <?php while($v = mysqli_fetch_assoc($qVendor)): ?>
                <option value="<?= $v['id_vendor'] ?>"
                    <?= $v['id_vendor'] == $invoice['id_vendor'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['nama_vendor']) ?>
                </option>
                <?php endwhile; ?>
            </select>

            <label>Tanggal Invoice</label>
            <input type="date" name="tanggal_invoice"
                value="<?= date('Y-m-d', strtotime($invoice['tanggal_invoice'])) ?>" required>

            <label>Total</label>
            <input type="number" name="total" value="<?= $invoice['total'] ?>" required>

            <label>Persentase DP (%)</label>
            <input type="number" name="dp_persen" value="<?= $invoice['dp_persen'] ?>"
                placeholder="Kosongkan jika lunas">

            <small>Isi jika invoice dibayar DP. Kosongkan jika lunas.</small>

            <label>Status</label>
            <select name="status" required>
                <option value="Belum Bayar" <?= $invoice['status']=='Belum Bayar'?'selected':'' ?>>Belum Bayar</option>
                <option value="DP" <?= $invoice['status']=='DP'?'selected':'' ?>>DP</option>
                <option value="Lunas" <?= $invoice['status']=='Lunas'?'selected':'' ?>>Lunas</option>
            </select><br>

            <div class="form-action">
                <a href="index.php" class="btn-back">← Kembali</a>
                <button type="submit" class="btn-primary">Update Invoice</button>
            </div>


        </form>
    </div>
</body>

<script>
const status = document.querySelector('[name="status"]');
const dp = document.querySelector('[name="dp_persen"]');

function syncDP() {
    if (status.value === 'DP') {
        dp.readOnly = false;
    } else {
        dp.value = 0;
        dp.readOnly = true;
    }
}

status.addEventListener('change', syncDP);
syncDP();
</script>

</html>