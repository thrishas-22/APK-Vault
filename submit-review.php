<?php
require_once dirname(__DIR__) . '/config/config.php';

if (!is_logged_in()) {
    json_response(['success' => false, 'error' => 'You must be logged in to leave a review.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

$apk_id = int_param('apk_id', $_POST);
$rating = int_param('rating', $_POST);
$review_text = str_param('review_text', $_POST);
$user_id = $_SESSION['user_id'];

if (!$apk_id) {
    json_response(['success' => false, 'error' => 'Missing Application ID.'], 400);
}

if ($rating < 1 || $rating > 5) {
    json_response(['success' => false, 'error' => 'Please provide a rating between 1 and 5.'], 400);
}

try {
    // Check if user already reviewed this app
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE apk_id = ? AND user_id = ?");
    $stmt->execute([$apk_id, $user_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing review
        $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, review_text = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$rating, $review_text, $existing['id']]);
        $msg = 'Review updated successfully!';
    } else {
        // Insert new review
        $stmt = $pdo->prepare("INSERT INTO reviews (apk_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$apk_id, $user_id, $rating, $review_text]);
        $msg = 'Review submitted successfully!';
    }

    // Recalculate average rating for the APK
    $stmt_avg = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE apk_id = ?");
    $stmt_avg->execute([$apk_id]);
    $new_avg = (float) $stmt_avg->fetchColumn();

    $stmt_upd = $pdo->prepare("UPDATE apks SET avg_rating = ? WHERE id = ?");
    $stmt_upd->execute([$new_avg, $apk_id]);

    json_response([
        'success' => true,
        'message' => $msg,
        'new_avg' => round($new_avg, 1)
    ]);

} catch (PDOException $e) {
    json_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
}
