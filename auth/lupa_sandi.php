<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($username == '' || $password == '' || $confirm == '') {
        $error = "Semua field wajib diisi";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter";
    } else {

        $cek = $conn->prepare("SELECT id_user FROM users WHERE username=? AND status='active'");
        $cek->bind_param("s", $username);
        $cek->execute();
        $res = $cek->get_result();

        if ($res->num_rows == 0) {
            $error = "Username tidak ditemukan atau belum aktif";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password=? WHERE username=?");
            $upd->bind_param("ss", $hash, $username);
            $upd->execute();

            $success = "Password berhasil direset. Silakan login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lupa Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    min-height:100vh;
    background:linear-gradient(135deg,#0d6efd,#0a58ca);
    display:flex;
    justify-content:center;
    align-items:center;
}
.card{
    width:380px;
    border:none;
    border-radius:15px;
    box-shadow:0 15px 40px rgba(0,0,0,.2);
}

</style>
</head>
<body>

    <div class="card p-4">
        <h4 class="text-center mb-1">🔑 Reset Password</h4>
        <p class="text-center text-muted mb-3">Masukkan username & password baru</p>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="confirm" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">Reset Password</button>

            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Kembali ke login</a>
            </div>
        </form>
    </div>

</body>
</html>
