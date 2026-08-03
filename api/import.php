<?php
/**
 * ClubHub v1.0.0 - /api/import.php
 * POST: import CSV data for clubs or users
 * Body: multipart/form-data with 'file' and 'type' (clubs|users)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method not allowed', 405);

$u = require_role('admin', 'manager');

$type = get_str('type');
if (!in_array($type, ['clubs', 'users'])) json_err('Type must be clubs or users', 422);

if (!isset($_FILES['file'])) json_err('Fichier CSV requis', 422);

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) json_err('Erreur upload: ' . $file['error'], 422);

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['csv', 'txt'])) json_err('Format non autorise. Seul CSV est accepte', 422);

$handle = fopen($file['tmp_name'], 'r');
if (!$handle) json_err('Impossible douvrir le fichier', 500);

$header = fgetcsv($handle, 0, ';');
if (!$header) { fclose($handle); json_err('Fichier CSV vide ou invalide', 422); }

$header = array_map('trim', $header);
$header = array_map('strtolower', $header);

$imported = 0;
$errors = [];

if ($type === 'clubs') {
    $required = ['name', 'category'];
    foreach ($required as $r) {
        if (!in_array($r, $header)) { fclose($handle); json_err("Colonne requise manquante: $r", 422); }
    }

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        if (count($row) < count($header)) continue;
        $data = array_combine($header, array_map('trim', $row));
        if (empty($data['name'])) continue;

        $slug = 'club-' . sprintf('%03d', (int)db()->query('SELECT IFNULL(MAX(id),0)+1 FROM clubs')->fetchColumn());
        $stmt = db()->prepare('INSERT INTO clubs (slug, name, category, color, short_desc, long_desc, tags, room, president, responsible_id, capacity, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        try {
            $stmt->execute([
                $slug,
                $data['name'],
                $data['category'] ?? 'culture',
                $data['color'] ?? '#ff4502',
                $data['short_desc'] ?? $data['description'] ?? '',
                $data['long_desc'] ?? null,
                json_encode($data['tags'] ?? []),
                $data['room'] ?? null,
                $data['president'] ?? null,
                null,
                (int)($data['capacity'] ?? 50),
                (int)$u['id'],
            ]);
            $imported++;
        } catch (Exception $e) {
            $errors[] = $data['name'] . ': ' . $e->getMessage();
        }
    }
} else {
    $required = ['name', 'matricule', 'email'];
    foreach ($required as $r) {
        if (!in_array($r, $header)) { fclose($handle); json_err("Colonne requise manquante: $r", 422); }
    }

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        if (count($row) < count($header)) continue;
        $data = array_combine($header, array_map('trim', $row));
        if (empty($data['name']) || empty($data['matricule'])) continue;

        $matricule = strtoupper($data['matricule']);
        $email = strtolower($data['email']);
        $role = $data['role'] ?? 'student';
        if (!in_array($role, ['student', 'manager', 'admin'])) $role = 'student';

        $chk = db()->prepare('SELECT id FROM users WHERE matricule = ? OR email = ?');
        $chk->execute([$matricule, $email]);
        if ($chk->fetch()) {
            $errors[] = "$matricule: deja existant";
            continue;
        }

        $avatar = strtoupper(substr(preg_split('/\s+/', $data['name'])[0], 0, 2));
        $hash = password_hash('resp1234', PASSWORD_BCRYPT);

        $stmt = db()->prepare('INSERT INTO users (matricule, email, password_hash, name, avatar, role, filiere, niveau, managed_club_id) VALUES (?,?,?,?,?,?,?,?,?)');
        try {
            $stmt->execute([
                $matricule,
                $email,
                $hash,
                $data['name'],
                $avatar,
                $role,
                $data['filiere'] ?? null,
                $data['niveau'] ?? null,
                isset($data['managed_club_id']) ? (int)$data['managed_club_id'] : null,
            ]);
            $imported++;
        } catch (Exception $e) {
            $errors[] = $data['name'] . ': ' . $e->getMessage();
        }
    }
}

fclose($handle);
audit((int)$u['id'], 'IMPORT', $type, null, ['imported' => $imported, 'errors' => count($errors)]);

json_ok(['imported' => $imported, 'errors' => $errors]);