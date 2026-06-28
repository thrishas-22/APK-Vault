<?php
require_once dirname(__DIR__) . '/config/config.php';
if (!is_logged_in())
    json_response(['success' => false, 'error' => 'Not logged in'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    json_response(['success' => false, 'error' => 'Method not allowed'], 405);
$token = $_POST['csrf_token'] ?? '';
if (!csrf_verify() && !hash_equals($_SESSION['csrf_token'] ?? '', $token))
    json_response(['success' => false, 'error' => 'Invalid token'], 403);
$apk_id = (int)($_POST['apk_id'] ?? 0);
if (!$apk_id)
    json_response(['success' => false, 'error' => 'Missing APK ID'], 400);
$apk = $pdo->prepare("SELECT id,status FROM apks WHERE id=? AND status='approved'");
$apk->execute([$apk_id]);
$apk = $apk->fetch();
if (!$apk)
    json_response(['success' => false, 'error' => 'APK not found'], 404);
$uid = $_SESSION['user_id'];
$existing = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id=? AND apk_id=?");
$existing->execute([$uid, $apk_id]);
$bm = $existing->fetch();
if ($bm) {
    $pdo->prepare("DELETE FROM bookmarks WHERE user_id=? AND apk_id=?")->execute([$uid, $apk_id]);
    json_response(['success' => true, 'bookmarked' => false, 'message' => 'Bookmark removed']);
}
else {
    $pdo->prepare("INSERT INTO bookmarks (user_id,apk_id) VALUES (?,?)")->execute([$uid, $apk_id]);
    json_response(['success' => true, 'bookmarked' => true, 'message' => 'Bookmarked']);
}
