<?php
require_once __DIR__ . '/config/config.php';

$username = 'admin';
$password = 'Admin@123';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ? OR email = ?");
    $stmt->execute([$hash, $username, 'apkvault08@gmail.com']);

    if ($stmt->rowCount() > 0) {
        echo "<h1>Success!</h1>";
        echo "<p>Admin password has been reset to: <strong>$password</strong></p>";
        echo "<p><a href='pages/login.php'>Go to Login</a></p>";
    } else {
        echo "<h1>Notice</h1>";
        echo "<p>Admin account not found in database. Make sure you have imported the SQL schema.</p>";
    }
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
