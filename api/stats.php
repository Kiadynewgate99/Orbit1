<?php
/**
 * ClubHub v1.0.0 - /api/stats.php
 * Statistiques globales (admin) ou par club (manager)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();
$u = require_auth();

$out = [];

$out['clubs_total']    = (int)db()->query('SELECT COUNT(*) FROM clubs WHERE status = "active"')->fetchColumn();
$out['clubs_incubation'] = (int)db()->query('SELECT COUNT(*) FROM clubs WHERE status = "incubation"')->fetchColumn();
$out['users_total']    = (int)db()->query('SELECT COUNT(*) FROM users WHERE is_active = 1')->fetchColumn();
$out['inscriptions_total'] = (int)db()->query('SELECT COUNT(*) FROM inscriptions WHERE status = "active"')->fetchColumn();
$out['events_upcoming'] = (int)db()->query('SELECT COUNT(*) FROM events WHERE date >= CURDATE()')->fetchColumn();
$out['events_total']    = (int)db()->query('SELECT COUNT(*) FROM events')->fetchColumn();

// top 5 clubs par membres
$out['top_clubs'] = db()->query('
    SELECT c.id, c.name, c.color, c.category,
           (SELECT COUNT(*) FROM inscriptions i WHERE i.club_id = c.id AND i.status = "active") AS members
    FROM clubs c
    WHERE c.status = "active"
    ORDER BY members DESC
    LIMIT 5
')->fetchAll();

// repartition par filiere
$out['by_filiere'] = db()->query('
    SELECT COALESCE(filiere, "Non renseigne") AS filiere, COUNT(*) AS n
    FROM users WHERE is_active = 1
    GROUP BY filiere
    ORDER BY n DESC
')->fetchAll();

audit((int)$u['id'], 'VIEW_STATS');

$out['users_ranking'] = db()->query('
    SELECT u.matricule, u.name, u.avatar, u.filiere, u.points,
           (SELECT COUNT(*) FROM inscriptions i WHERE i.user_id = u.id AND i.status = "active") AS clubs_count
    FROM users u
    WHERE u.is_active = 1
    ORDER BY u.points DESC
    LIMIT 20
')->fetchAll();

json_ok($out);
