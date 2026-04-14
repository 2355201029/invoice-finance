<?php
require_once __DIR__ . '/../config/database.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // ======================
    // VALIDASI
    // ======================
    if (!$nama || !$email || !$username || !$password) {
        $error = "Semua field wajib diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter";
    } else {

        // ======================
        // CEK USERNAME / EMAIL
        // ======================
        $cek = $conn->prepare("
            SELECT id_user 
            FROM users 
            WHERE username = ? OR email = ?
        ");
        $cek->bind_param("ss", $username, $email);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = "Username atau email sudah digunakan";
        } else {

            // ======================
            // SIMPAN USER
            // ======================
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users 
                (nama, email, username, password, role, status)
                VALUES (?, ?, ?, ?, 'user', 'pending')
            ");
            $stmt->bind_param("ssss", $nama, $email, $username, $hash);
            $stmt->execute();

            $success = "Registrasi berhasil, menunggu persetujuan admin";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(180deg, #0d6efd, #0b5ed7);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .auth-card {
            width: 360px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 25px 50px rgba(0,0,0,.25);
            padding: 28px;
            text-align: center;
        }

        .auth-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .btn-auth {
            background: #0d6efd;
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
        }

        .btn-auth:hover {
            background: #0b5ed7;
        }

        .auth-footer {
            font-size: 12px;
            color: #6c757d;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <h4 class="auth-title">📝 Register</h4>
    <div class="auth-subtitle">Buat akun baru</div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
        <input type="email" name="email" class="form-control" placeholder="Email Aktif" required>
        <input type="text" name="username" class="form-control" placeholder="Username" required>
        <input type="password" name="password" class="form-control" placeholder="Password (min. 6 karakter)" required>
        <button class="btn btn-primary w-100 btn-auth">Register</button>
    </form>

    <div class="auth-link mt-3">
        <a href="login.php">Kembali ke Login</a>
    </div>

    <div class="auth-footer">© 2026 Sistem Invoice</div>
</div>

</body>
</html>
