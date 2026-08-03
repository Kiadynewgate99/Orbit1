<?php
/**
 * ClubHub v1.0.0 - /api/clubs.php
 * GET          : liste ou detail
 * POST         : creer (admin)
 * PUT ?id=X    : modifier (admin)
 * DELETE ?id=X : archiver (admin)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? '';
$id     = $id !== '' ? trim((string)$id) : '';

try {
    switch ($method) {
        case 'GET':    return clubs_read($id);
        case 'POST':   return clubs_create();
        case 'PUT':    return clubs_update((int)$id);
        case 'DELETE': return clubs_delete((int)$id);
        default:       json_err('Method not allowed', 405);
    }
} catch (Throwable $e) {
    json_err('Server error: ' . $e->getMessage(), 500);
}

// =============================================================
// READ
// =============================================================
function clubs_read($id): void {
    if ($id !== '' && $id !== '0') {
        if (ctype_digit($id) && (int)$id > 0) {
            $where = 'WHERE id = ?';
            $params = [(int)$id];
        } else {
            $where = 'WHERE slug = ?';
            $params = [(string)$id];
        }
        $stmt = db()->prepare('SELECT * FROM clubs ' . $where);
        $stmt->execute($params);
        $c = $stmt->fetch();
        if (!$c) json_err('Club introuvable', 404);
        $c['tags'] = json_decode($c['tags'] ?? '[]', true);
        $c['members_count'] = (int)db()->query('SELECT COUNT(*) FROM inscriptions WHERE club_id = ' . (int)$c['id'] . ' AND status = "active"')->fetchColumn();
        $c['members'] = db()->prepare('SELECT u.id, u.name, u.avatar, u.filiere, i.role_in_club, i.joined_at FROM inscriptions i JOIN users u ON u.id = i.user_id WHERE i.club_id = ? AND i.status = "active" ORDER BY i.joined_at DESC')->fetchAll();
        json_ok($c);
    }

    // Liste filtree / recherche / tri / pagination
    $q        = get_str('q');
    $category = get_str('category');
    $status   = get_str('status', 'active');
    $sort     = get_str('sort', 'name');
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $per_page = min(50, max(1, (int)($_GET['per_page'] ?? 24)));
    $offset   = ($page - 1) * $per_page;

    $where = ['status = :status'];
    $params = [':status' => $status];

    if ($q !== '') {
        $where[] = '(name LIKE :q OR short_desc LIKE :q OR long_desc LIKE :q)';
        $params[':q'] = '%' . $q . '%';
    }
    if ($category !== '') {
        $where[] = 'category = :cat';
        $params[':cat'] = $category;
    }

    if ($sort === 'members') {
        $order = '(SELECT COUNT(*) FROM inscriptions i WHERE i.club_id = clubs.id AND i.status = "active") DESC';
    } elseif ($sort === 'recent') {
        $order = 'created_at DESC';
    } else {
        $order = 'name ASC';
    }

    $sql = 'SELECT c.*, (SELECT COUNT(*) FROM inscriptions i WHERE i.club_id = c.id AND i.status = "active") AS members_count
            FROM clubs c WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $order . ' LIMIT :lim OFFSET :off';
    $stmt = db()->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    foreach ($rows as &$r) {
        $r['tags'] = json_decode($r['tags'] ?? '[]', true);
        $r['members_count'] = (int)$r['members_count'];
    }

    $count_sql = 'SELECT COUNT(*) FROM clubs WHERE ' . implode(' AND ', $where);
    $cs = db()->prepare($count_sql);
    foreach ($params as $k => $v) $cs->bindValue($k, $v);
    $cs->execute();
    $total = (int)$cs->fetchColumn();

    json_ok($rows, ['total' => $total, 'page' => $page, 'per_page' => $per_page]);
}

// =============================================================
// CREATE (admin only)
// =============================================================
function clubs_create(): void {
    $u = require_role('admin');
    $in = input_json();
    $name = trim($in['name'] ?? '');
    if (!$name) json_err('Nom requis', 422);

    $slug = 'club-' . sprintf('%03d', (int)db()->query('SELECT IFNULL(MAX(id),0)+1 FROM clubs')->fetchColumn());
    $stmt = db()->prepare('INSERT INTO clubs (slug, name, category, color, logo, short_desc, long_desc, tags, room, president, responsible_id, capacity, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $slug,
        $name,
        $in['category']      ?? 'culture',
        $in['color']         ?? '#ff4502',
        $in['logo']          ?? null,
        $in['short_desc']    ?? '',
        $in['long_desc']     ?? null,
        json_encode($in['tags'] ?? []),
        $in['room']          ?? null,
        $in['president']     ?? null,
        isset($in['responsible_id']) ? (int)$in['responsible_id'] : null,
        (int)($in['capacity'] ?? 50),
        (int)$u['id'],
    ]);
    $id = (int)db()->lastInsertId();
    audit((int)$u['id'], 'CREATE', 'club', $id, ['name' => $name]);
    json_ok(['id' => $id, 'slug' => $slug], [], 201);
}

// =============================================================
// UPDATE (admin only)
// =============================================================
function clubs_update(int $id): void {
    $u = require_role('admin');
    if ($id <= 0) json_err('ID requis', 422);
    $in = input_json();

    $allowed = ['name', 'category', 'color', 'short_desc', 'long_desc', 'room', 'president', 'capacity', 'status', 'logo', 'responsible_id'];
    $sets = []; $params = [];
    foreach ($allowed as $k) {
        if (array_key_exists($k, $in)) { $sets[] = "$k = ?"; $params[] = $in[$k]; }
    }
    if (isset($in['tags'])) { $sets[] = 'tags = ?'; $params[] = json_encode($in['tags']); }
    if (!$sets) json_err('Rien a modifier', 422);
    $params[] = $id;
    db()->prepare('UPDATE clubs SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);

    audit((int)$u['id'], 'UPDATE', 'club', $id);
    json_ok(['updated' => true]);
}

// =============================================================
// DELETE (admin only, archive)
// =============================================================
function clubs_delete(int $id): void {
    $u = require_role('admin');
    if ($id <= 0) json_err('ID requis', 422);
    db()->prepare('UPDATE clubs SET status = "archived" WHERE id = ?')->execute([$id]);
    audit((int)$u['id'], 'DELETE', 'club', $id);
    json_ok(['archived' => true]);
}
