<?php
/**
 * ClubHub v1.0.0 - /api/presences.php
 * GET    /api/presences.php?event_id=X   => liste des presents pour un event
 * GET    /api/presences.php?my=1         => mes events inscrits
 * POST   /api/presences.php              => s'inscrire a un event (body: event_id)
 * DELETE /api/presences.php?id=X         => se desinscrire d'un event
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();

$u = require_auth();
$method = $_SERVER['REQUEST_METHOD'];
$id = get_int('id');

try {
    switch ($method) {
        case 'GET':    return presences_read($u);
        case 'POST':   return presences_create($u);
        case 'DELETE': return presences_delete($u, $id);
        default:        json_err('Method not allowed', 405);
    }
} catch (Throwable $e) {
    json_err('Server error: ' . $e->getMessage(), 500);
}

function presences_read(array $u): void {
    $event_id = get_int('event_id');
    $my = get_str('my');

    if ($my === '1') {
        $stmt = db()->prepare('SELECT e.*, c.name AS club_name, c.color AS club_color, p.status AS presence_status, p.checked_at
                                 FROM presences p
                                 JOIN events e ON e.id = p.event_id
                                 JOIN clubs c ON c.id = e.club_id
                                 WHERE p.user_id = ? AND p.status IN ("registered","present")
                                 ORDER BY e.date ASC, e.time ASC');
        $stmt->execute([(int)$u['id']]);
        json_ok($stmt->fetchAll());
        return;
    }

    if ($event_id > 0) {
        $stmt = db()->prepare('SELECT p.*, u.name, u.matricule, u.avatar
                                 FROM presences p
                                 JOIN users u ON u.id = p.user_id
                                 WHERE p.event_id = ? AND p.status IN ("registered","present")
                                 ORDER BY p.created_at ASC');
        $stmt->execute([$event_id]);
        json_ok($stmt->fetchAll());
        return;
    }

    json_err('event_id ou my=1 requis', 422);
}

function presences_create(array $u): void {
    $in = input_json();
    $event_id = (int)($in['event_id'] ?? 0);
    if ($event_id <= 0) json_err('event_id requis', 422);

    $stmt = db()->prepare('SELECT e.*, c.name AS club_name FROM events e JOIN clubs c ON c.id = e.club_id WHERE e.id = ?');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    if (!$event) json_err('Event introuvable', 404);

    $existing = db()->prepare('SELECT id, status FROM presences WHERE user_id = ? AND event_id = ?');
    $existing->execute([(int)$u['id'], $event_id]);
    $row = $existing->fetch();

    if ($row) {
        if ($row['status'] === 'registered' || $row['status'] === 'present') {
            json_ok(['already_registered' => true, 'id' => $row['id']]);
            return;
        }
        db()->prepare('UPDATE presences SET status = "registered", method = "manual", checked_at = NULL, created_at = NOW() WHERE id = ?')->execute([$row['id']]);
        $presence_id = $row['id'];
    } else {
        $stmt = db()->prepare('INSERT INTO presences (user_id, event_id, status, method) VALUES (?,?,?,?)');
        $stmt->execute([(int)$u['id'], $event_id, 'registered', 'manual']);
        $presence_id = (int)db()->lastInsertId();
    }

    db()->prepare('UPDATE events SET registered = registered + 1 WHERE id = ? AND registered < capacity')->execute([$event_id]);

    db()->prepare('INSERT INTO notifications (user_id, kind, icon, title, message, link) VALUES (?,?,?,?,?,?)')
        ->execute([(int)$u['id'], 'event', '◷', 'Inscription confirmee', "Vous etes inscrit a l'event.", 'agenda.html']);

    db()->prepare('UPDATE users SET points = points + 5 WHERE id = ?')->execute([(int)$u['id']]);

    audit((int)$u['id'], 'REGISTER_EVENT', 'event', $event_id);
    json_ok(['registered' => true, 'id' => $presence_id], [], 201);
}

function presences_delete(array $u, int $id): void {
    if ($id <= 0) json_err('ID requis', 422);

    $stmt = db()->prepare('SELECT p.*, e.title FROM presences p JOIN events e ON e.id = p.event_id WHERE p.id = ? AND p.user_id = ?');
    $stmt->execute([$id, (int)$u['id']]);
    $row = $stmt->fetch();
    if (!$row) json_err('Presence introuvable', 404);

    db()->prepare('DELETE FROM presences WHERE id = ?')->execute([$id]);
    db()->prepare('UPDATE events SET registered = GREATEST(0, registered - 1) WHERE id = ?')->execute([$row['event_id']]);

    audit((int)$u['id'], 'UNREGISTER_EVENT', 'event', $row['event_id']);
    json_ok(['unregistered' => true]);
}