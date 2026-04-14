<?php

function uploadDokumen($file, $folder, $namaFileFix)
{
    $maxSize = 5 * 1024 * 1024; // 5 MB
    $allowed = ['pdf'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'msg' => 'Upload gagal'];
    }

    if ($file['size'] > $maxSize) {
        return ['status' => false, 'msg' => 'Ukuran file maksimal 5 MB'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return ['status' => false, 'msg' => 'Format file harus PDF'];
    }

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // ðŸ”¥ NAMA FILE FINAL (TANPA TIMESTAMP)
    $namaFinal = $namaFileFix . '.' . $ext;
    $path = rtrim($folder, '/') . '/' . $namaFinal;

    // OPTIONAL: overwrite file lama
    if (file_exists($path)) {
        unlink($path);
    }

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return ['status' => false, 'msg' => 'Gagal menyimpan file'];
    }

    return [
        'status' => true,
        'file'   => $namaFinal
    ];
}