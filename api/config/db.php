<?php
/**
 * ClubHub v1.0.0 - Configuration BDD
 * Copier en db.local.php et remplir les constantes.
 */

// =====================================================
// Production : utiliser variables d'environnement
// Dev local : remplir ci-dessous
// =====================================================
define('DB_HOST', getenv('CLUBHUB_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('CLUBHUB_DB_NAME') ?: 'clubhub');
define('DB_USER', getenv('CLUBHUB_DB_USER') ?: 'root');
define('DB_PASS', getenv('CLUBHUB_DB_PASS') ?: '');

// =====================================================
// JWT / Auth
// =====================================================
define('JWT_SECRET', getenv('CLUBHUB_JWT_SECRET') ?: 'change-me-in-prod-this-is-a-demo-secret-key');
define('TOKEN_TTL', 60 * 60 * 24); // 24h

// =====================================================
// CORS
// =====================================================
define('ALLOWED_ORIGIN', getenv('CLUBHUB_ALLOWED_ORIGIN') ?: '*');

// =====================================================
// Connexion PDO (singleton)
// =====================================================
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// =====================================================
// Headers CORS + JSON
// =====================================================
function send_headers(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// =====================================================
// Helpers
// =====================================================
function json_out($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_err(string $message, int $code = 400, array $extra = []): void {
    json_out(['ok' => false, 'error' => $message] + $extra, $code);
}

function json_ok($data = null, array $meta = []): void {
    json_out(['ok' => true, 'data' => $data] + $meta);
}

function input_json(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function get_int(string $key, int $default = 0): int {
    return isset($_GET[$key]) ? max(0, (int)$_GET[$key]) : $default;
}

function get_str(string $key, string $default = ''): string {
    return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default;
}

function audit(int $user_id, string $action, ?string $target_type = null, ?int $target_id = null, array $metadata = []): void {
    try {
        $stmt = db()->prepare('INSERT INTO audit_log (user_id, action, target_type, target_id, ip, user_agent, metadata) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([
            $user_id,
            $action,
            $target_type,
            $target_id,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            $metadata ? json_encode($metadata) : null,
        ]);
    } catch (Exception $e) {
        // ne pas casser la requete si l'audit echoue
        error_log('audit_log error: ' . $e->getMessage());
    }
}
