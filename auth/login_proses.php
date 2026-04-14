<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/log.php';

$username = $_POST['username'] ?? '';
$password = md5($_POST['password'] ?? '');

$stmt = $conn->prepare("
    SELECT id_user, username, role
    FROM users
    WHERE username=? AND password=?
");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if ($user) {

    $_SESSION['id_user']  = $user['id_user'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    simpanLog($conn, 'Login', 'User berhasil login');

    header("Location: ../dashboard/index.php");
    exit;
}

header("Location: login.php?error=1");
exit;