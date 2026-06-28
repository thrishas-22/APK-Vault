<?php
require_once dirname(__DIR__) . '/config/config.php';
$q = str_param('q', $_GET);
if (!$q || strlen($q) < 2) {
    json_response([]);
}
$stmt = $pdo->prepare("SELECT a.id, a.title, a.icon_path, c.name as category_name FROM apks a LEFT JOIN categories c ON a.category_id=c.id WHERE a.status='approved' AND a.title LIKE ? LIMIT 6");
$stmt->execute(["%$q%"]);
$results = [];
foreach ($stmt->fetchAll() as $app) {
    $results[] = ['id' => $app['id'], 'title' => e($app['title']), 'icon' => apk_icon_url($app['icon_path']), 'category' => e($app['category_name'] ?? ''), 'url' => SITE_URL . '/pages/apk-detail.php?id=' . $app['id']];
}
json_response($results);
