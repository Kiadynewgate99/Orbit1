<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method not allowed', 405);

$user = require_role('admin');

$type = isset($_POST['type']) ? trim($_POST['type']) : 'clubs';

if (empty($_FILES['file'])) json_err('No file uploaded', 400);

$uploadDir = __DIR__ . '/../uploads/imports/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename = $type . '_' . time() . '.csv';
$destination = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
    json_err('Upload failed', 500);
}

$imported = 0;
$errors = [];

if ($type === 'clubs') {
    if (($handle = fopen($destination, 'r')) !== FALSE) {
        fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== FALSE) {
            if (count($row) < 4) continue;
            list($name, $category, $short_desc, $president) = $row;
            if (!$name) { $errors[] = 'Row missing name'; continue; }
            $slug = strtolower(trim($name));
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
            $slug = trim($slug, '-');
            $stmt = db()->prepare('INSERT INTO clubs (slug, name, category, short_desc, president, status, created_by) VALUES (?,?,?,?,?,?,?)');
            try {
                $stmt->execute([$slug, $name, $category ?: 'tech', $short_desc, $president, 'active', $user['id']]);
                $imported++;
            } catch (Exception $e) {
                $errors[] = $name . ': ' . $e->getMessage();
            }
        }
        fclose($handle);
    }
}

audit($user['id'], 'IMPORT_CSV', $type, 0, ['imported' => $imported, 'errors' => count($errors)]);

json_ok(['imported' => $imported, 'errors' => $errors]);
