<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method not allowed', 405);

$user = require_auth();

$type = isset($_POST['type']) ? trim($_POST['type']) : 'users';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (empty($_FILES['file'])) json_err('No file uploaded', 400);

$uploadDir = __DIR__ . '/../uploads/' . $type . '/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$filename = $id . '_' . time() . '.' . $ext;
$destination = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
    json_err('Upload failed', 500);
}

audit($user['id'], 'UPLOAD', $type, $id, ['file' => $filename]);

json_ok(['path' => $destination, 'url' => '/uploads/' . $type . '/' . $filename]);
