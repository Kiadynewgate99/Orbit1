<?php
/**
 * ClubHub v1.0.0 - /api/search.php
 * Recherche full-text dans clubs et events, avec autocompletion
 */
require_once __DIR__ . '/config/db.php';

send_headers();

$q = get_str('q');
if (mb_strlen($q) < 2) json_ok(['clubs' => [], 'events' => []]);

$like = '%' . $q . '%';

$clubs = db()->prepare('SELECT id, slug, name, short_desc, category, color FROM clubs
                        WHERE status = "active" AND (name LIKE ? OR short_desc LIKE ? OR long_desc LIKE ?)
                        LIMIT 6');
$clubs->execute([$like, $like, $like]);

$events = db()->prepare('SELECT e.id, e.title, e.date, e.time, e.room, c.name AS club_name, c.color AS club_color
                         FROM events e JOIN clubs c ON c.id = e.club_id
                         WHERE e.title LIKE ? OR e.description LIKE ? OR c.name LIKE ?
                         ORDER BY e.date ASC LIMIT 6');
$events->execute([$like, $like, $like]);

json_ok([
    'clubs'  => $clubs->fetchAll(),
    'events' => $events->fetchAll(),
    'q'      => $q,
]);
