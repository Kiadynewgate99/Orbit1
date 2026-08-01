<?php
/**
 * ClubHub v1.0.0 - /api/audit.php
 * Admin only : consulte l'historique d'audit avec filtres
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';

send_headers();
$u = require_role('admin');

$action   = get_str('action');
$user_q   = get_str('user');
$target   = get_str('target_type');
$from     = get_str('from');
$to       = get_str('to');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = min(100, max(10, (int)($_GET['per_page'] ?? 50)));
$offset   = ($page - 1) * $per_page;

$where = ['1=1']; $params = [];
if ($action) { $where[] = 'a.action = ?'; $params[] = $action; }
if ($target) { $where[] = 'a.target_type = ?'; $params[] = $target; }
if ($from)   { $where[] = 'a.created_at >= ?'; $params[] = $from; }
if ($to)     { $where[] = 'a.created_at <= ?'; $params[] = $to; }
if ($user_q) { $where[] = '(u.name LIKE ? OR u.matricule = ?)'; $params[] = '%' . $user_q . '%'; $params[] = $user_q; }

$sql = 'SELECT a.*, u.name AS user_name, u.matricule AS user_matricule, u.role AS user_role
        FROM audit_log a LEFT JOIN users u ON u.id = a.user_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY a.created_at DESC
        LIMIT ' . $per_page . ' OFFSET ' . $offset;
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$cs = db()->prepare('SELECT COUNT(*) FROM audit_log a LEFT JOIN users u ON u.id = a.user_id WHERE ' . implode(' AND ', $where));
$cs->execute($params);
$total = (int)$cs->fetchColumn();

json_ok($rows, ['total' => $total, 'page' => $page]);
