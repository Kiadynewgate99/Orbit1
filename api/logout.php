<?php
/**
 * ClubHub v1.0.0 - POST /api/logout.php
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method not allowed', 405);

$u = current_user();
if ($u) {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (stripos($auth, 'Bearer ') === 0) {
        token_revoke(substr($auth, 7));
    } elseif (!empty($_COOKIE['clubhub_token'])) {
        token_revoke($_COOKIE['clubhub_token']);
    }
    audit((int)$u['id'], 'LOGOUT', 'user', (int)$u['id']);
}
clear_token_cookie();
json_ok(['logged_out' => true]);
