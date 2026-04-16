<?php
$host = "mysql";        // NAMA SERVICE MYSQL (BUKAN localhost)
$user = "root";
$pass = "";         // SESUAI MYSQL_ROOT_PASSWORD
$db   = "db_invoice_wita";

$conn = mysqli_connect("localhost", $user, "", $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
