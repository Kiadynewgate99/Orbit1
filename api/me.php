<?php
/**
 * ClubHub v1.0.0 - GET /api/me.php
 * Renvoie l'utilisateur courant (ou 401)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();

$u = current_user();
if (!$u) json_err('Unauthorized', 401);
json_ok($u);
