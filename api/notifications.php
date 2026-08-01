<?php
/**
 * ClubHub v1.0.0 - /api/notifications.php
 * GET    /api/notifications.php
 * POST   /api/notifications.php?read_all=1  => tout marquer comme lu
 * POST   /api/notifications.php?id=X&read=1 => marquer une comme lue
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();
$u = require_auth();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
    $stmt->execute([(int)$u['id']]);
    json_ok($stmt->fetchAll());
}

if ($method === 'POST') {
    if (get_str('read_all')) {
        db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([(int)$u['id']]);
        json_ok(['updated' => true]);
    }
    $id = get_int('id');
    if ($id > 0) {
        db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$id, (int)$u['id']]);
        json_ok(['updated' => true]);
    }
    json_err('Param manquant', 422);
}
json_err('Method not allowed', 405);
