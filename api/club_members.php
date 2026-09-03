<?php
/**
 * Orbit v1.0.0 - /api/club_members.php
 * GET /api/club_members.php?club_id=X  -> liste des membres d'un club avec stats de presence
 *
 * Renvoie pour chaque membre : id, name, matricule, avatar, filiere, role_in_club,
 * events_total, events_present, events_absent, presence_rate
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();
$u = require_auth();

$club_id = get_int('club_id');
if ($club_id <= 0) json_err('club_id requis', 422);

$club = db()->prepare('SELECT * FROM clubs WHERE id = ?');
$club->execute([$club_id]);
$club = $club->fetch();
if (!$club) json_err('Club introuvable', 404);

if ($u['role'] === 'manager' && (int)$u['managed_club_id'] !== $club_id) {
    json_err('Forbidden', 403);
}

$members = db()->prepare('SELECT u.id, u.name, u.matricule, u.avatar, u.filiere, u.role,
                                 i.role_in_club, i.joined_at
                          FROM inscriptions i JOIN users u ON u.id = i.user_id
                          WHERE i.club_id = ? AND i.status = "active"
                          ORDER BY i.role_in_club DESC, u.name');
$members->execute([$club_id]);
$members = $members->fetchAll();

$event_ids = db()->prepare('SELECT id FROM events WHERE club_id = ?');
$event_ids->execute([$club_id]);
$event_ids = array_column($event_ids->fetchAll(), 'id');

$result = [];
foreach ($members as $m) {
    $presence_rows = [];
    if (!empty($event_ids)) {
        $placeholders = implode(',', array_fill(0, count($event_ids), '?'));
        $pres = db()->prepare("SELECT status FROM presences WHERE user_id = ? AND event_id IN ($placeholders)");
        $pres->execute(array_merge([(int)$m['id']], $event_ids));
        $presence_rows = $pres->fetchAll();
    }

    $total = count($presence_rows);
    $present = count(array_filter($presence_rows, fn($p) => $p['status'] === 'present'));
    $registered = count(array_filter($presence_rows, fn($p) => $p['status'] === 'registered'));
    $absent = count(array_filter($presence_rows, fn($p) => $p['status'] === 'absent'));
    $late = count(array_filter($presence_rows, fn($p) => $p['status'] === 'late'));
    $rate = $total > 0 ? round(($present / $total) * 100, 1) : null;

    $result[] = [
        'id'            => (int)$m['id'],
        'name'          => $m['name'],
        'matricule'     => $m['matricule'],
        'avatar'        => $m['avatar'],
        'filiere'       => $m['filiere'],
        'role'          => $m['role'],
        'role_in_club'  => $m['role_in_club'],
        'joined_at'     => $m['joined_at'],
        'events_total'  => $total,
        'events_present' => $present,
        'events_registered' => $registered,
        'events_absent' => $absent,
        'events_late'   => $late,
        'presence_rate' => $rate,
    ];
}

json_ok([
    'club_id'  => $club_id,
    'club_name' => $club['name'],
    'total'    => count($result),
    'members'  => $result,
]);