<?php
/**
 * ClubHub v1.0.0 - POST /api/login.php
 * Body: { matricule, password }
 * Renvoie token + user
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method not allowed', 405);

$in = input_json();
$matricule = strtoupper(trim($in['matricule'] ?? ''));
$password  = $in['password'] ?? '';

if (!$matricule || !$password) json_err('Matricule et mot de passe requis', 422);

$stmt = db()->prepare('SELECT * FROM users WHERE matricule = ? AND is_active = 1');
$stmt->execute([$matricule]);
$u = $stmt->fetch();

if (!$u || !password_verify($password, $u['password_hash'] ?? '')) {
    json_err('Identifiants invalides', 401);
}

$token = token_generate((int)$u['id']);
db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$u['id']]);
set_token_cookie($token);
audit((int)$u['id'], 'LOGIN', 'user', (int)$u['id']);

unset($u['password_hash']);
json_ok(['token' => $token, 'user' => $u]);
