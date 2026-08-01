<?php
/**
 * ClubHub v1.0.0 - /api/inscriptions.php
 * POST   /api/inscriptions.php   body: {club_id}  => rejoindre
 * DELETE /api/inscriptions.php   body: {club_id}  => quitter
 * GET    /api/inscriptions.php                    => mes inscriptions (clubs)
 * GET    /api/inscriptions.php?type=events       => mes events inscrits
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();

$u = require_auth();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET')    return list_inscriptions($u);
    if ($method === 'POST')   return join_club($u);
    if ($method === 'DELETE') return leave_club($u);
    json_err('Method not allowed', 405);
} catch (Throwable $e) {
    json_err('Server error: ' . $e->getMessage(), 500);
}

function list_inscriptions(array $u): void {
    $type = get_str('type');
    if ($type === 'events') {
        $stmt = db()->prepare('SELECT e.*, c.name AS club_name, c.color AS club_color, p.status AS presence_status
                               FROM presences p
                               JOIN events e ON e.id = p.event_id
                               JOIN clubs c ON c.id = e.club_id
                               WHERE p.user_id = ?
                               ORDER BY e.date ASC, e.time ASC');
        $stmt->execute([(int)$u['id']]);
        json_ok($stmt->fetchAll());
    }
    $stmt = db()->prepare('SELECT c.*, i.role_in_club, i.status, i.joined_at
                           FROM inscriptions i
                           JOIN clubs c ON c.id = i.club_id
                           WHERE i.user_id = ? AND i.status = "active"
                           ORDER BY i.joined_at DESC');
    $stmt->execute([(int)$u['id']]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) $r['tags'] = json_decode($r['tags'] ?? '[]', true);
    json_ok($rows);
}

function join_club(array $u): void {
    $in = input_json();
    $club_id = (int)($in['club_id'] ?? 0);
    if ($club_id <= 0) json_err('club_id requis', 422);

    $exists = db()->prepare('SELECT id, status FROM clubs WHERE id = ?');
    $exists->execute([$club_id]);
    $club = $exists->fetch();
    if (!$club || $club['status'] !== 'active') json_err('Club indisponible', 404);

    $year = (int)date('Y');
    $stmt = db()->prepare('INSERT INTO inscriptions (user_id, club_id, year, role_in_club, status) VALUES (?,?,?,"member","active")
                           ON DUPLICATE KEY UPDATE status = "active", left_at = NULL');
    $stmt->execute([(int)$u['id'], $club_id, $year]);

    // notif
    $msg = 'Vous etes maintenant membre de ' . ($club['name'] ?? 'ce club');
    db()->prepare('INSERT INTO notifications (user_id, kind, icon, title, message) VALUES (?,?,?,?,?)')
        ->execute([(int)$u['id'], 'inscription', '✓', 'Inscription confirmee', $msg]);

    // points
    db()->prepare('UPDATE users SET points = points + 10 WHERE id = ?')->execute([(int)$u['id']]);

    audit((int)$u['id'], 'JOIN_CLUB', 'club', $club_id);
    json_ok(['joined' => true, 'points' => 10]);
}

function leave_club(array $u): void {
    $in = input_json();
    $club_id = (int)($in['club_id'] ?? 0);
    if ($club_id <= 0) json_err('club_id requis', 422);

    db()->prepare('UPDATE inscriptions SET status = "inactive", left_at = NOW() WHERE user_id = ? AND club_id = ? AND status = "active"')
        ->execute([(int)$u['id'], $club_id]);
    audit((int)$u['id'], 'LEAVE_CLUB', 'club', $club_id);
    json_ok(['left' => true]);
}
