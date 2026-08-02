<?php
/**
 * ClubHub v1.0.0 - One-time setup script
 * Creates database and imports schema + seed
 */

$start = microtime(true);
$errors = [];

function info(string $msg): void {
    echo "[INFO] $msg\n";
}

function ok(string $msg): void {
    echo "[OK]   $msg\n";
}

function fail(string $msg): void {
    echo "[FAIL] $msg\n";
}

function load_env(string $path): array {
    if (!file_exists($path)) {
        throw new RuntimeException(".env file not found at $path");
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $env[trim($parts[0])] = trim($parts[1]);
        }
    }
    return $env;
}

try {
    $root = __DIR__ . '/..';
    $env = load_env($root . '/.env');

    $host = $env['CLUBHUB_DB_HOST'] ?? 'localhost';
    $name = $env['CLUBHUB_DB_NAME'] ?? 'clubhub';
    $user = $env['CLUBHUB_DB_USER'] ?? 'root';
    $pass = $env['CLUBHUB_DB_PASS'] ?? '';

    info("Connecting to MySQL at $host...");
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    ok("Connected to MySQL");

    info("Creating database `$name` if not exists...");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    ok("Database `$name` ready");

    info("Importing schema...");
    $schemaPath = $root . '/database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new RuntimeException("Missing schema.sql at $schemaPath");
    }
    $schema = file_get_contents($schemaPath);
    $pdo->exec("USE `$name`");
    $pdo->exec($schema);
    ok("Schema imported");

    info("Importing seed data...");
    $seedPath = $root . '/database/seed.sql';
    if (!file_exists($seedPath)) {
        throw new RuntimeException("Missing seed.sql at $seedPath");
    }
    $seed = file_get_contents($seedPath);
    $pdo->exec($seed);
    ok("Seed data imported");

    $elapsed = round(microtime(true) - $start, 2);
    echo "\n";
    ok("Setup completed in {$elapsed}s");
    exit(0);

} catch (Throwable $e) {
    fail($e->getMessage());
    exit(1);
}
