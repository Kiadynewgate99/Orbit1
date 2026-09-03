<?php
/**
 * Orbit v1.0.0 - /api/recommend.php
 * GET /api/recommend.php            -> recommande 5 clubs pour l'user courant
 * GET /api/recommend.php?user_id=X  -> pour un user donne (admin)
 *
 * Algorithme : cosine similarity entre vecteur hobbies user
 * et vecteur "tags/categorie" du club. Renvoie un score 0-1.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();
$u = require_auth();

$target_id = get_int('user_id') ?: (int)$u['id'];
if ($u['role'] !== 'admin' && $target_id !== (int)$u['id']) json_err('Forbidden', 403);

$uh = db()->prepare('SELECT h.slug, h.label, uh.weight
                      FROM user_hobbies uh JOIN hobbies h ON h.id = uh.hobby_id
                      WHERE uh.user_id = ?');
$uh->execute([$target_id]);
$user_hobbies = $uh->fetchAll();
if (!$user_hobbies) json_ok(['recommendations' => [], 'reason' => 'Aucun hobby renseigne']);

$user_vec = [];
foreach ($user_hobbies as $h) $user_vec[$h['slug']] = (int)$h['weight'];

$clubs = db()->query('SELECT id, slug, name, category, color, short_desc, tags, room, capacity,
                             (SELECT COUNT(*) FROM inscriptions i WHERE i.club_id = clubs.id AND i.status = "active") AS members
                      FROM clubs WHERE status = "active"')->fetchAll();

$scored = [];
foreach ($clubs as $c) {
    $club_vec = [];
    $cat = $c['category'];
    $club_vec[$cat] = 3;
    $tags = json_decode($c['tags'] ?? '[]', true) ?: [];
    foreach ($tags as $t) {
        $slug = strtolower(trim($t));
        $club_vec[$slug] = ($club_vec[$slug] ?? 0) + 1;
    }

    $score = cosine_similarity($user_vec, $club_vec);
    if ($score > 0) {
        $c['score'] = round($score, 3);
        $c['reasons'] = build_reasons($user_hobbies, $cat, $tags);
        $scored[] = $c;
    }
}

usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
$top = array_slice($scored, 0, 5);

foreach ($top as &$c) {
    $c['tags'] = json_decode($c['tags'] ?? '[]', true);
    unset($c['reasons_raw']);
}
json_ok(['recommendations' => $top, 'user_hobbies' => $user_hobbies]);

function cosine_similarity(array $a, array $b): float {
    $dot = 0; $na = 0; $nb = 0;
    foreach ($a as $k => $v) {
        $na += $v * $v;
        if (isset($b[$k])) $dot += $v * $b[$k];
    }
    foreach ($b as $v) $nb += $v * $v;
    if ($na == 0 || $nb == 0) return 0;
    return $dot / (sqrt($na) * sqrt($nb));
}

function build_reasons(array $user_hobbies, string $cat, array $tags): array {
    $reasons = [];
    foreach ($user_hobbies as $h) {
        if ($h['slug'] === $cat) $reasons[] = "Tu aimes " . strtolower($h['label']);
    }
    foreach ($tags as $t) {
        foreach ($user_hobbies as $h) {
            if (strtolower($t) === $h['slug']) $reasons[] = "Tag match : " . $t;
        }
    }
    return array_unique($reasons);
}