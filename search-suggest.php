<?php
require_once dirname(__DIR__) . '/config/config.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    json_response([]);
}

try {
    // Search for matching APKs (approved only)
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.slug, a.icon_path, c.name as category_name 
        FROM apks a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'approved' 
        AND (a.title LIKE ? OR a.package_name LIKE ?)
        LIMIT 5
    ");

    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();

    $suggestions = [];
    foreach ($results as $row) {
        $suggestions[] = [
            'title' => $row['title'],
            'url' => SITE_URL . '/pages/apk-detail.php?slug=' . $row['slug'],
            'icon' => apk_icon_url($row['icon_path']),
            'category' => $row['category_name'] ?? 'Other'
        ];
    }

    json_response($suggestions);

} catch (Exception $e) {
    json_response(['error' => 'Search failed'], 500);
}
