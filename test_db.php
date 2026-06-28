<?php
require_once dirname(__DIR__) . '/app/config/config.php';

$stmt = $pdo->query("SELECT * FROM password_resets ORDER BY id DESC LIMIT 1");
$row = $stmt->fetch();

$phpTime = date('Y-m-d H:i:s');
$dbTimeStmt = $pdo->query("SELECT NOW() as db_time");
$dbTime = $dbTimeStmt->fetchColumn();

echo "PHP Time: $phpTime\n";
echo "DB Time:  $dbTime\n";
echo "Last OTP Row:\n";
print_r($row);

// Let's also test the verify query exactly as it is:
if ($row) {
    $verifyStmt = $pdo->prepare("SELECT id FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW() AND used = 0");
    $verifyStmt->execute([$row['email'], $row['token']]);
    $verifyResult = $verifyStmt->fetch();
    echo "Verification with NOW(): " . ($verifyResult ? "SUCCESS" : "FAILED") . "\n";
}
