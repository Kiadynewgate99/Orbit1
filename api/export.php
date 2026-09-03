<?php
/**
 * Orbit v1.0.0 - /api/export.php
 * GET /api/export.php?type=club&id=2&format=pdf  -> rapport HTML imprimable
 * GET /api/export.php?type=club&id=2&format=csv  -> liste des membres en CSV
 * GET /api/export.php?type=events&format=csv    -> liste de tous les events en CSV
 * GET /api/export.php?type=presences&event_id=5 -> presences d'un event en CSV
 * GET /api/export.php?type=users&format=csv     -> liste des users en CSV (admin)
 * GET /api/export.php?type=stats                -> stats globales JSON
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();
$u = require_auth();

$type   = get_str('type', 'club');
$id     = get_int('id');
$format = get_str('format', 'pdf');

function csv_out(string $filename, array $headers, array $rows): void {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, $headers);
    foreach ($rows as $r) fputcsv($out, $r);
    fclose($out);
    exit;
}

if ($format === 'csv') {
    if ($type === 'club' && $id > 0) {
        $stmt = db()->prepare('SELECT u.matricule, u.name, u.email, u.filiere, u.niveau, i.role_in_club, i.joined_at
                               FROM inscriptions i JOIN users u ON u.id = i.user_id
                               WHERE i.club_id = ? AND i.status = "active" ORDER BY u.name');
        $stmt->execute([$id]);
        $rows = array_map(fn($r) => [
            $r['matricule'], $r['name'], $r['email'], $r['filiere'], $r['niveau'], $r['role_in_club'], $r['joined_at']
        ], $stmt->fetchAll());
        csv_out('club-' . $id . '-membres-' . date('Ymd') . '.csv',
                ['Matricule', 'Nom', 'Email', 'Filiere', 'Niveau', 'Role', 'Inscription'],
                $rows);
    }

    if ($type === 'events') {
        if ($u['role'] === 'manager') {
            $stmt = db()->prepare('SELECT e.id, e.title, e.date, e.time, e.duration_min, e.room, e.type, e.registered, e.capacity, c.name AS club
                                   FROM events e JOIN clubs c ON c.id = e.club_id
                                   WHERE e.club_id = ? ORDER BY e.date');
            $stmt->execute([(int)$u['managed_club_id']]);
        } else {
            $stmt = db()->query('SELECT e.id, e.title, e.date, e.time, e.duration_min, e.room, e.type, e.registered, e.capacity, c.name AS club
                                  FROM events e JOIN clubs c ON c.id = e.club_id ORDER BY e.date');
        }
        $rows = array_map(fn($r) => [
            $r['id'], $r['title'], $r['date'], $r['time'], $r['duration_min'], $r['room'], $r['type'], $r['registered'], $r['capacity'], $r['club']
        ], $stmt->fetchAll());
        csv_out('events-' . date('Ymd') . '.csv',
                ['ID', 'Titre', 'Date', 'Heure', 'Duree_min', 'Salle', 'Type', 'Inscrits', 'Capacite', 'Club'],
                $rows);
    }

    if ($type === 'presences') {
        $event_id = get_int('event_id');
        if ($event_id <= 0) json_err('event_id requis', 422);
        $stmt = db()->prepare('SELECT u.matricule, u.name, u.email, p.status, p.method, p.checked_at
                               FROM presences p JOIN users u ON u.id = p.user_id
                               WHERE p.event_id = ? ORDER BY p.checked_at DESC');
        $stmt->execute([$event_id]);
        $rows = array_map(fn($r) => [
            $r['matricule'], $r['name'], $r['email'], $r['status'], $r['method'], $r['checked_at']
        ], $stmt->fetchAll());
        csv_out('presences-event-' . $event_id . '-' . date('Ymd') . '.csv',
                ['Matricule', 'Nom', 'Email', 'Statut', 'Methode', 'Checked_at'],
                $rows);
    }

    if ($type === 'users' && $u['role'] === 'admin') {
        $rows = db()->query('SELECT matricule, name, email, role, filiere, niveau, points, is_active, last_login_at FROM users ORDER BY name')->fetchAll();
        $out = array_map(fn($r) => [
            $r['matricule'], $r['name'], $r['email'], $r['role'], $r['filiere'], $r['niveau'], $r['points'], $r['is_active'], $r['last_login_at']
        ], $rows);
        csv_out('users-' . date('Ymd') . '.csv',
                ['Matricule', 'Nom', 'Email', 'Role', 'Filiere', 'Niveau', 'Points', 'Actif', 'Derniere_connexion'],
                $out);
    }

    json_err('Type CSV inconnu ou non autorise', 422);
}

if ($type === 'club' && $id > 0) {
    $stmt = db()->prepare('SELECT * FROM clubs WHERE id = ?');
    $stmt->execute([$id]);
    $club = $stmt->fetch();
    if (!$club) json_err('Club introuvable', 404);
    if ($u['role'] === 'manager' && (int)$u['managed_club_id'] !== $id) {
        json_err('Forbidden', 403);
    }

    $members = db()->prepare('SELECT u.name, u.matricule, u.filiere, u.avatar, i.role_in_club, i.joined_at
                              FROM inscriptions i JOIN users u ON u.id = i.user_id
                              WHERE i.club_id = ? AND i.status = "active" ORDER BY u.name');
    $members->execute([$id]);

    $events = db()->prepare('SELECT * FROM events WHERE club_id = ? ORDER BY date DESC');
    $events->execute([$id]);

    $pres_count = (int)db()->query('SELECT COUNT(*) FROM presences p JOIN events e ON e.id = p.event_id WHERE e.club_id = ' . (int)$id)->fetchColumn();
    $mem_count = (int)$members->rowCount();

    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport <?= htmlspecialchars($club['name']) ?> - Orbit</title>
<style>
@page { size: A4; margin: 20mm; }
body { font-family: 'Helvetica', sans-serif; color: #000; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { font-size: 28px; margin: 0 0 8px; }
h2 { font-size: 18px; margin: 24px 0 8px; border-bottom: 2px solid #000; padding-bottom: 4px; }
table { width: 100%; border-collapse: collapse; font-size: 11px; margin: 8px 0; }
th, td { border: 1px solid #000; padding: 6px 10px; text-align: left; }
th { background: #000; color: #fff; }
.header { border-bottom: 4px solid #FF4502; padding-bottom: 12px; margin-bottom: 16px; }
.brand { display: flex; align-items: center; gap: 12px; }
.brand-mark { width: 40px; height: 40px; background: #FF4502; border-radius: 6px; display: inline-block; }
.brand-text { font-size: 24px; font-weight: 900; }
.meta { display: flex; gap: 24px; margin-top: 12px; font-size: 12px; }
.meta div { padding: 8px 12px; border: 1px solid #000; border-radius: 4px; }
.meta strong { display: block; font-size: 10px; color: #666; text-transform: uppercase; }
.kpi { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 16px 0; }
.kpi div { padding: 12px; border: 2px solid #000; border-radius: 4px; }
.kpi .v { font-size: 24px; font-weight: 900; }
.footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #ccc; font-size: 10px; color: #666; text-align: center; }
</style>
</head>
<body>
<div class="header">
  <div class="brand">
    <div class="brand-mark"></div>
    <div>
      <div class="brand-text">ORBIT</div>
      <div style="font-size: 11px; color: #666;">Rapport club · <?= date('d/m/Y') ?></div>
    </div>
  </div>
  <h1><?= htmlspecialchars($club['name']) ?></h1>
  <div class="meta">
    <div><strong>Categorie</strong><?= htmlspecialchars($club['category']) ?></div>
    <div><strong>Salle</strong><?= htmlspecialchars($club['room'] ?: 'N/A') ?></div>
    <div><strong>President</strong><?= htmlspecialchars($club['president'] ?: 'N/A') ?></div>
  </div>
</div>

<div class="kpi">
  <div><div class="v"><?= $mem_count ?></div><div style="font-size:11px; color:#666;">Membres actifs</div></div>
  <div><div class="v"><?= $events->rowCount() ?></div><div style="font-size:11px; color:#666;">Evenements organises</div></div>
  <div><div class="v"><?= $pres_count ?></div><div style="font-size:11px; color:#666;">Presences enregistrees</div></div>
</div>

<h2>Description</h2>
<p style="font-size: 12px; line-height: 1.5;"><?= htmlspecialchars($club['long_desc'] ?? $club['short_desc']) ?></p>

<h2>Membres (<?= $mem_count ?>)</h2>
<table>
  <thead><tr><th>Matricule</th><th>Nom</th><th>Filiere</th><th>Role</th><th>Inscription</th></tr></thead>
  <tbody>
  <?php while ($m = $members->fetch()): ?>
    <tr>
      <td style="font-family: monospace;"><?= htmlspecialchars($m['matricule']) ?></td>
      <td><?= htmlspecialchars($m['name']) ?></td>
      <td><?= htmlspecialchars($m['filiere'] ?? '-') ?></td>
      <td><?= htmlspecialchars($m['role_in_club']) ?></td>
      <td><?= htmlspecialchars(substr($m['joined_at'], 0, 10)) ?></td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>

<h2>Evenements (<?= $events->rowCount() ?>)</h2>
<table>
  <thead><tr><th>Date</th><th>Titre</th><th>Type</th><th>Lieu</th><th>Inscrits</th></tr></thead>
  <tbody>
  <?php while ($e = $events->fetch()): ?>
    <tr>
      <td style="font-family: monospace;"><?= htmlspecialchars($e['date']) ?></td>
      <td><?= htmlspecialchars($e['title']) ?></td>
      <td><?= htmlspecialchars($e['type']) ?></td>
      <td><?= htmlspecialchars($e['room'] ?? '-') ?></td>
      <td style="text-align:right;"><?= (int)$e['registered'] ?> / <?= (int)$e['capacity'] ?></td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>

<div class="footer">
  Document genere par Orbit le <?= date('d/m/Y a H:i') ?> · Confidentiel · Usage interne<br>
  ID rapport: EXP-<?= $id ?>-<?= date('Ymd-His') ?> · Conforme RGPD
</div>
</body>
</html>
<?php
    exit;
}

if ($type === 'stats') {
    $out = [
        'clubs_total' => (int)db()->query('SELECT COUNT(*) FROM clubs WHERE status = "active"')->fetchColumn(),
        'users_total' => (int)db()->query('SELECT COUNT(*) FROM users WHERE is_active = 1')->fetchColumn(),
        'events_total' => (int)db()->query('SELECT COUNT(*) FROM events')->fetchColumn(),
        'inscriptions_total' => (int)db()->query('SELECT COUNT(*) FROM inscriptions WHERE status = "active"')->fetchColumn(),
    ];
    json_ok($out);
}

json_err('Type inconnu', 422);