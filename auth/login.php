<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/log.php';

$error = null; // ✅ FIX WARNING Undefined variable $error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("
        SELECT id_user, nama, role, password 
        FROM users 
        WHERE username = ? AND status = 'active'
        LIMIT 1
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // ================= SESSION =================
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role']; // admin / user
            // ===========================================

            header("Location: ../dashboard/index.php");
            exit;
        }
    }

    $error = "Username atau password salah";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login Sistem Invoice</title>

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
        box-shadow: 0 25px 50px rgba(0, 0, 0, .25);
        padding: 28px;
        text-align: center;
    }

    .form-control {
        border-radius: 8px;
        margin-bottom: 12px;
    }

    .btn-auth {
        border-radius: 8px;
        font-weight: 600;
    }

    .logos img {
        height: 50px;
        margin-left: 14px;
        background: #fff;
        padding: 6px 10px;
        border-radius: 12px;
    }
    </style>
</head>

<body>

    <div class="auth-card">

        <div class="logos mb-3">
            <img src="../assets/img/astra.jpeg">
            <img src="../assets/img/klk.jpeg">
        </div>

        <h4 class="fw-bold mb-1">Sistem Invoice Finance</h4>
        <small class="text-muted">PT KREASIJAYA ADHIKARYA</small>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger mt-3">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="mt-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button class="btn btn-primary w-100 btn-auth">Login</button>
        </form>

        <div class="mt-3">
            <a href="lupa_sandi.php">Lupa kata sandi?</a><br>
            Belum punya akun? <a href="register.php">Daftar</a>
        </div>

        <small class="text-muted d-block mt-3">© 2026 Sistem Invoice</small>
    </div>

</body>

</html>