<?php
/**
 * ClubHub v1.0.0 - POST /api/register.php
 * Inscription libre (etudiant uniquement)
 * Body: { matricule, name, email, password, filiere, niveau }
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method not allowed', 405);

$in = input_json();
$matricule = strtoupper(trim($in['matricule'] ?? ''));
$name      = trim($in['name'] ?? '');
$email     = strtolower(trim($in['email'] ?? ''));
$password  = $in['password'] ?? '';
$filiere   = trim($in['filiere'] ?? 'L1');
$niveau    = trim($in['niveau'] ?? 'L1');

$errors = [];
if (!preg_match('/^[A-Z0-9]{2,10}$/', $matricule)) $errors[] = 'Matricule invalide (2-10 caracteres alphanumeriques)';
if (mb_strlen($name) < 3)                          $errors[] = 'Nom trop court';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = 'Email invalide';
if (strlen($password) < 6)                         $errors[] = 'Mot de passe : 6 caracteres minimum';
if ($errors) json_err('Validation', 422, ['fields' => $errors]);

// unicite
$chk = db()->prepare('SELECT id FROM users WHERE matricule = ? OR email = ?');
$chk->execute([$matricule, $email]);
if ($chk->fetch()) json_err('Matricule ou email deja utilise', 409);

$avatar = strtoupper(substr(preg_split('/\s+/', $name)[0], 0, 2));
$hash = password_hash($password, PASSWORD_BCRYPT);

$ins = db()->prepare('INSERT INTO users (matricule, email, password_hash, name, avatar, role, filiere, niveau) VALUES (?,?,?,?,?,?,?,?)');
$ins->execute([$matricule, $email, $hash, $name, $avatar, 'student', $filiere, $niveau]);
$id = (int)db()->lastInsertId();

audit($id, 'REGISTER', 'user', $id);

// notif de bienvenue
db()->prepare('INSERT INTO notifications (user_id, kind, icon, title, message) VALUES (?,?,?,?,?)')
    ->execute([$id, 'system', '★', 'Bienvenue sur Orbit', 'Votre compte a ete cree. Decouvrez les 12 clubs actifs.']);

// token
$token = token_generate($id);
db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$id]);
set_token_cookie($token);

$user = db()->prepare('SELECT * FROM users WHERE id = ?');
$user->execute([$id]);
$u = $user->fetch();
unset($u['password_hash']);

json_ok(['token' => $token, 'user' => $u], [], 201);
