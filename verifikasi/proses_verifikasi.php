<?php
session_start();
require_once __DIR__ . '/../config/database.php';

/* CEK LOGIN */
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard/index.php");
    exit;
}

/* VALIDASI PARAMETER */
if (!isset($_GET['id'], $_GET['aksi'])) {
    header("Location: index.php");
    exit;
}

$id   = (int) $_GET['id'];     // id_user
$aksi = $_GET['aksi'];

switch ($aksi) {

    case 'acc':
        $sql = "UPDATE users 
                SET status='active' 
                WHERE id_user=$id";
        break;

    case 'admin':
        $sql = "UPDATE users 
                SET role='admin', status='active' 
                WHERE id_user=$id";
        break;

    default:
        header("Location: index.php");
        exit;
}

/* EKSEKUSI */
if (mysqli_query($conn, $sql)) {
    header("Location: index.php?success=1");
    exit;
} else {
    die("Gagal update: " . mysqli_error($conn));
}