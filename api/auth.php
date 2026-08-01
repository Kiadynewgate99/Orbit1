<?php
/**
 * ClubHub v1.0.0 - Auth helpers
 * - Token opaque (64 chars hex) stocke en BDD
 * - Header : Authorization: Bearer <token>
 * - Ou cookie httpOnly clubhub_token
 */
require_once __DIR__ . '/config/db.php';

function token_generate(int $user_id): string {
    $token = bin2hex(random_bytes(32)); // 64 chars
    $expires = date('Y-m-d H:i:s', time() + TOKEN_TTL);
    db()->prepare('INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?,?,?)')
        ->execute([$user_id, $token, $expires]);
    return $token;
}

function token_revoke(string $token): void {
    db()->prepare('DELETE FROM auth_tokens WHERE token = ?')->execute([$token]);
}

function current_user(): ?array {
    static $cached = null;
    if ($cached !== null) return $cached;

    $token = null;
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (stripos($auth, 'Bearer ') === 0) {
        $token = substr($auth, 7);
    } elseif (!empty($_COOKIE['clubhub_token'])) {
        $token = $_COOKIE['clubhub_token'];
    }
    if (!$token) return null;

    $stmt = db()->prepare('SELECT u.*, t.expires_at FROM users u JOIN auth_tokens t ON t.user_id = u.id WHERE t.token = ? AND t.expires_at > NOW() AND u.is_active = 1');
    $stmt->execute([$token]);
    $u = $stmt->fetch();
    if (!$u) return null;

    // ne pas renvoyer le hash
    unset($u['password_hash']);
    $cached = $u;
    return $u;
}

function require_auth(): array {
    $u = current_user();
    if (!$u) json_err('Unauthorized', 401);
    return $u;
}

function require_role(string ...$roles): array {
    $u = require_auth();
    if (!in_array($u['role'], $roles, true)) json_err('Forbidden', 403);
    return $u;
}

function set_token_cookie(string $token): void {
    setcookie('clubhub_token', $token, [
        'expires'  => time() + TOKEN_TTL,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function clear_token_cookie(): void {
    setcookie('clubhub_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
    ]);
}
