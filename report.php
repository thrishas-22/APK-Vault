<?php
require_once dirname(__DIR__) . '/config/config.php';
if (!is_logged_in())
    json_response(['success' => false, 'error' => 'Not logged in'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    json_response(['success' => false, 'error' => 'Method not allowed'], 405);
if (!csrf_verify())
    json_response(['success' => false, 'error' => 'Invalid token'], 403);
$type = in_array($_POST['type'] ?? '', ['review', 'apk', 'user']) ? $_POST['type'] : null;
$reported_id = (int)($_POST['reported_id'] ?? 0);
$reason = trim(substr($_POST['reason'] ?? '', 0, 500));
if (!$type || !$reported_id || !$reason)
    json_response(['success' => false, 'error' => 'Missing data'], 400);
$uid = $_SESSION['user_id'];
$existing = $pdo->prepare("SELECT id FROM reports WHERE reporter_id=? AND reported_type=? AND reported_id=?");
$existing->execute([$uid, $type, $reported_id]);
if ($existing->fetch())
    json_response(['success' => false, 'error' => 'You already reported this'], 409);
$pdo->prepare("INSERT INTO reports (reporter_id,reported_type,reported_id,reason) VALUES (?,?,?,?)")->execute([$uid, $type, $reported_id, $reason]);
if ($type === 'review')
    $pdo->prepare("UPDATE reviews SET is_flagged=1, flag_count=flag_count+1 WHERE id=?")->execute([$reported_id]);
json_response(['success' => true, 'message' => 'Reported. Thank you.']);
