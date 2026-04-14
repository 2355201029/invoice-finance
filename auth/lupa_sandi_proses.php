<?php
require_once '../config/database.php';

if (isset($_POST['reset'])) {

    $username       = $_POST['username'];
    $password_baru  = $_POST['password_baru'];
    $konfirmasi     = $_POST['konfirmasi'];

    // cek password sama
    if ($password_baru !== $konfirmasi) {
        echo "<script>alert('Konfirmasi password tidak sama');history.back();</script>";
        exit;
    }

    // cek username
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) == 0) {
        echo "<script>alert('Username tidak ditemukan');history.back();</script>";
        exit;
    }

    // hash password
    $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

    // update password
    $update = mysqli_query($conn, "
        UPDATE users 
        SET password='$password_hash' 
        WHERE username='$username'
    ");

    if ($update) {
        echo "<script>
                alert('Password berhasil direset');
                window.location='login.php';
                </script>";
    } else {
        echo "<script>alert('Reset password gagal');history.back();</script>";
    }
}
