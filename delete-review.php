<?php
require_once dirname(__DIR__) . '/config/config.php';

if (!is_logged_in()) {
    json_response(['success' => false, 'error' => 'You must be logged in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

// Get the json body
$input = json_decode(file_get_contents('php://input'), true);
$review_id = isset($input['review_id']) ? (int)$input['review_id'] : 0;

if (!$review_id) {
    json_response(['success' => false, 'error' => 'Missing review ID.'], 400);
}

try {
    // Check if the review exists and get its apk_id and user_id
    $stmt = $pdo->prepare("SELECT apk_id, user_id FROM reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();

    if (!$review) {
        json_response(['success' => false, 'error' => 'Review not found.'], 404);
    }

    // Check permissions: only author or admin can delete
    if ($review['user_id'] != $_SESSION['user_id'] && !is_admin()) {
        json_response(['success' => false, 'error' => 'Unauthorized deletion attempt.'], 403);
    }

    $apk_id = $review['apk_id'];

    // Delete the review
    $stmt_del = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt_del->execute([$review_id]);

    // Recalculate average rating for the APK
    $stmt_avg = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE apk_id = ?");
    $stmt_avg->execute([$apk_id]);
    $new_avg = (float) $stmt_avg->fetchColumn();

    $stmt_upd = $pdo->prepare("UPDATE apks SET avg_rating = ? WHERE id = ?");
    $stmt_upd->execute([$new_avg, $apk_id]);

    json_response([
        'success' => true,
        'message' => 'Review deleted successfully!',
        'new_avg' => round($new_avg, 1)
    ]);

} catch (PDOException $e) {
    json_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
}
