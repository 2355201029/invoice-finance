<?php
function logAktivitas($conn, $id_user, $aktivitas, $keterangan = '')
{
    if (!$id_user) {
        return false;
    }

    $halaman = $_SERVER['REQUEST_URI'] ?? '-';
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '-';

    $stmt = mysqli_prepare($conn, "
        INSERT INTO log_aktivitas 
        (id_user, aktivitas, keterangan, halaman, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "issss",
        $id_user,
        $aktivitas,
        $keterangan,
        $halaman,
        $ip
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}