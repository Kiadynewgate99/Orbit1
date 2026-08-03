<?php
/**
 * ClubHub v1.0.0 - /api/upload.php
 * POST: upload profile picture (club logo or user avatar)
 * Body: multipart/form-data with 'file' and 'type' (club|user) and 'id' (club_id or user_id)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method not allowed', 405);

$u = require_role('admin', 'manager');

$type = get_str('type');
$id = get_int('id');

if (!in_array($type, ['club', 'user'])) json_err('Type must be club or user', 422);
if ($id <= 0) json_err('ID requis', 422);

if (!isset($_FILES['file'])) json_err('Fichier requis', 422);

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) json_err('Erreur upload: ' . $file['error'], 422);

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
if (!in_array($ext, $allowed)) json_err('Format non autorise. Accepte: ' . implode(', ', $allowed), 422);

$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) json_err('Fichier trop grand (max 5MB)', 422);

$uploadDir = __DIR__ . '/../uploads/' . ($type === 'club' ? 'clubs' : 'users') . '/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$safeName = $type . '-' . $id . '-' . time() . '.' . $ext;
$dest = $uploadDir . $safeName;

if (!move_uploaded_file($file['tmp_name'], $dest)) json_err('Erreur de deplacement du fichier', 500);

$relativePath = 'uploads/' . ($type === 'club' ? 'clubs' : 'users') . '/' . $safeName;

if ($type === 'club') {
    $stmt = db()->prepare('UPDATE clubs SET logo = ? WHERE id = ?');
    $stmt->execute([$relativePath, $id]);
    audit((int)$u['id'], 'UPDATE', 'club', $id, ['logo' => $relativePath]);
} else {
    $stmt = db()->prepare('UPDATE users SET avatar_url = ? WHERE id = ?');
    $stmt->execute([$relativePath, $id]);
    audit((int)$u['id'], 'UPDATE', 'user', $id, ['avatar_url' => $relativePath]);
}

json_ok(['path' => $relativePath, 'url' => '/' . $relativePath]);