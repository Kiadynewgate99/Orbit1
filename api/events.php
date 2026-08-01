<?php
/**
 * ClubHub v1.0.0 - /api/events.php
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();

$method = $_SERVER['REQUEST_METHOD'];
$id     = get_int('id');

try {
    switch ($method) {
        case 'GET':    return events_read($id);
        case 'POST':   return events_create();
        case 'PUT':    return events_update($id);
        case 'DELETE': return events_delete($id);
        default:       json_err('Method not allowed', 405);
    }
} catch (Throwable $e) {
    json_err('Server error: ' . $e->getMessage(), 500);
}

function events_read(int $id): void {
    if ($id > 0) {
        $stmt = db()->prepare('SELECT e.*, c.name AS club_name, c.color AS club_color FROM events e JOIN clubs c ON c.id = e.club_id WHERE e.id = ?');
        $stmt->execute([$id]);
        json_ok($stmt->fetch() ?: null);
    }

    $from   = get_str('from');
    $to     = get_str('to');
    $club   = get_int('club_id');
    $type   = get_str('type');
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $per    = min(50, max(1, (int)($_GET['per_page'] ?? 24)));
    $offset = ($page - 1) * $per;

    $where = ['1=1']; $params = [];
    if ($from)   { $where[] = 'e.date >= ?'; $params[] = $from; }
    if ($to)     { $where[] = 'e.date <= ?'; $params[] = $to; }
    if ($club)   { $where[] = 'e.club_id = ?'; $params[] = $club; }
    if ($type)   { $where[] = 'e.type = ?'; $params[] = $type; }

    $sql = 'SELECT e.*, c.name AS club_name, c.color AS club_color
            FROM events e JOIN clubs c ON c.id = e.club_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY e.date ASC, e.time ASC
            LIMIT ' . $per . ' OFFSET ' . $offset;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    // detection conflit pour l'user courant
    $u = current_user();
    $conflicts = [];
    if ($u) {
        $rows = $stmt->fetchAll();
        $mine = db()->prepare('SELECT e.id, e.date, e.time, e.duration_min, e.title
                               FROM presences p JOIN events e ON e.id = p.event_id
                               WHERE p.user_id = ? AND p.status IN ("registered","present")
                                 AND e.date BETWEEN ? AND ?');
        $mine->execute([(int)$u['id'], $from ?: '2000-01-01', $to ?: '2099-12-31']);
        $mine = $mine->fetchAll();
        foreach ($rows as $e) {
            foreach ($mine as $m) {
                if ($m['id'] != $e['id'] && $m['date'] == $e['date']) {
                    $a1 = strtotime($m['time']);
                    $a2 = $a1 + ((int)$m['duration_min'] * 60);
                    $b1 = strtotime($e['time']);
                    $b2 = $b1 + ((int)$e['duration_min'] * 60);
                    if ($a1 < $b2 && $b1 < $a2) { $conflicts[] = $e['id']; break; }
                }
            }
        }
        json_ok($rows, ['total' => count($rows), 'conflicts' => array_values(array_unique($conflicts))]);
    }
    json_ok($stmt->fetchAll(), ['total' => count($rows ?? [])]);
}

function events_create(): void {
    $u = require_role('manager', 'admin');
    $in = input_json();
    $required = ['club_id', 'title', 'type', 'category', 'date', 'time'];
    foreach ($required as $k) if (empty($in[$k])) json_err("$k requis", 422);

    $club_id = (int)$in['club_id'];
    if ($u['role'] === 'manager' && (int)$u['managed_club_id'] !== $club_id) {
        json_err('Vous ne pouvez creer que des events pour votre club', 403);
    }

    $stmt = db()->prepare('INSERT INTO events (club_id, title, description, type, category, date, time, duration_min, room, capacity, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $club_id,
        $in['title'],
        $in['description'] ?? null,
        $in['type'],
        $in['category'],
        $in['date'],
        $in['time'],
        (int)($in['duration_min'] ?? 120),
        $in['room']   ?? null,
        (int)($in['capacity'] ?? 50),
        (int)$u['id'],
    ]);
    $id = (int)db()->lastInsertId();
    audit((int)$u['id'], 'CREATE', 'event', $id);
    json_ok(['id' => $id], [], 201);
}

function events_update(int $id): void { json_err('Not implemented', 501); }
function events_delete(int $id): void {
    $u = require_role('manager', 'admin');
    if ($id <= 0) json_err('ID requis', 422);
    $row = db()->query('SELECT club_id FROM events WHERE id = ' . (int)$id)->fetch();
    if (!$row) json_err('Introuvable', 404);
    if ($u['role'] === 'manager' && (int)$u['managed_club_id'] !== (int)$row['club_id']) {
        json_err('Forbidden', 403);
    }
    db()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
    audit((int)$u['id'], 'DELETE', 'event', $id);
    json_ok(['deleted' => true]);
}
