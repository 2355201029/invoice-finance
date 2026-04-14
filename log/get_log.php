<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$search = $_GET['search'] ?? '';

$where = '';
if ($search != '') {
    $search = "%$search%";
    $where = "WHERE u.username LIKE ? 
                OR l.aktivitas LIKE ?
                OR l.keterangan LIKE ?";
}

$sql = "
    SELECT 
        DATE_FORMAT(
            CONVERT_TZ(l.created_at, @@session.time_zone, '+07:00'),
            '%Y-%m-%d %H:%i:%s'
        ) AS tanggal,
        u.username AS nama,
        l.aktivitas,
        l.keterangan,
        l.halaman,
        l.ip_address AS ip
    FROM log_aktivitas l
    LEFT JOIN users u ON l.id_user = u.id_user
    $where
    ORDER BY l.created_at DESC
";

$stmt = $conn->prepare($sql);

if ($where != '') {
    $stmt->bind_param("sss", $search, $search, $search);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);