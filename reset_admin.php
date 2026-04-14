<?php
require_once __DIR__ . '/config/database.php';

$password = password_hash('admin123', PASSWORD_DEFAULT);

mysqli_query($conn, "
    UPDATE users SET
        password = '$password',
        role     = 'admin',
        status   = 'active'
    WHERE username = 'admin'
");

if (mysqli_affected_rows($conn) > 0) {
    echo "ADMIN SIAP LOGIN";
} else {
    echo "USERNAME admin TIDAK DITEMUKAN";
}
