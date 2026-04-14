<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role']; // admin / user
$currentPage = $_SERVER['REQUEST_URI'];
