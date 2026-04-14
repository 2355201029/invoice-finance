<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function simpanLog($conn, $aktivitas, $keterangan = '') {

    if (!isset($_SESSION['id_user'])) return;

    $id_user = $_SESSION['id_user'];
    $halaman = $_SERVER['REQUEST_URI'] ?? '-';
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '-';
    $waktu   = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("
        INSERT INTO log_aktivitas
        (id_user, aktivitas, keterangan, halaman, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isssss",
        $id_user,
        $aktivitas,
        $keterangan,
        $halaman,
        $ip,
        $waktu
    );

    $stmt->execute();
}