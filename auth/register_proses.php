<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$errors = [];
$success = '';

if (isset($_POST['register'])) {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // VALIDASI
    if ($nama == '' || $username == '' || $email == '' || $password == '') {
        $errors[] = "Semua field wajib diisi!";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter!";
    }

    // CEK USERNAME / EMAIL
    $cek = $conn->prepare("SELECT id_user FROM users WHERE username = ? OR email = ?");
    $cek->bind_param("ss", $username, $email);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $errors[] = "Username atau Email sudah digunakan!";
    }

    // SIMPAN
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (nama, username, email, password, role, status)
            VALUES (?, ?, ?, ?, 'user', 'pending')
        ");
        $stmt->bind_param("ssss", $nama, $username, $email, $password_hash);

        if ($stmt->execute()) {
            $success = "Registrasi berhasil! Menunggu persetujuan admin.";
        } else {
            $errors[] = "Gagal menyimpan data!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-container">
    <h2>Register Akun</h2>

    <?php if ($errors): ?>
        <div class="alert error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nama" placeholder="Nama Lengkap" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="register">Daftar</button>
    </form>

    <p class="link">
        Sudah punya akun? <a href="login.php">Login</a>
    </p>
</div>

</body>
</html>
