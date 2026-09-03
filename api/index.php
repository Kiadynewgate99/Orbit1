<?php
/**
 * ClubHub v1.0.0 - /api/index.php
 * Routeur principal si on ne veut pas s'embeter avec .htaccess
 * ou pour les serveurs qui ne supportent pas PATH_INFO.
 *
 * A la racine du projet, le fichier index.php delegue ici.
 */
require_once __DIR__ . '/config/db.php';

send_headers();

$path = $_GET['route'] ?? '';
$path = preg_replace('#^/+|/+$#', '', $path);

$map = [
    'login'          => 'login.php',
    'logout'         => 'logout.php',
    'register'       => 'register.php',
    'me'             => 'me.php',
    'clubs'          => 'clubs.php',
    'events'         => 'events.php',
    'inscriptions'   => 'inscriptions.php',
    'notifications'  => 'notifications.php',
    'stats'          => 'stats.php',
    'search'         => 'search.php',
    'export'         => 'export.php',
    'audit'          => 'audit.php',
    'upload'         => 'upload.php',
    'import'         => 'import.php',
    'recommend'      => 'recommend.php',
    'club_members'   => 'club_members.php',
];

if ($path === '' || $path === 'health') {
    json_ok([
        'service' => 'ClubHub API',
        'version' => '1.0.0',
        'status'  => 'ok',
        'endpoints' => array_keys($map),
    ]);
}

if (!isset($map[$path])) {
    json_err('Endpoint inconnu: /' . $path, 404);
}

require __DIR__ . '/' . $map[$path];
