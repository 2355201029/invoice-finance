<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

function onlyAdmin() {
    if ($_SESSION['role'] !== 'admin') {
        echo "<script>
            alert('Akses ditolak! Halaman khusus admin.');
            window.location='../dashboard/index.php';
        </script>";
        exit;
    }
}

function adminOrUser() {
    if (!in_array($_SESSION['role'], ['admin','user'])) {
        header("Location: ../auth/login.php");
        exit;
    }
}
