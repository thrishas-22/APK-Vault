<?php
require_once 'config/config.php';

// 1. Create dummy APK file
$dummy_content = "This is a dummy APK file for testing.";
$filename = 'dummy_test_app.apk';
$filepath = UPLOAD_DIR . 'apks/' . $filename;

if (!is_dir(UPLOAD_DIR . 'apks/')) {
    mkdir(UPLOAD_DIR . 'apks/', 0755, true);
}

file_put_contents($filepath, $dummy_content);

// 2. Update the 'weather' app in DB
$stmt = $pdo->prepare("UPDATE apks SET file_path = ?, status = 'approved' WHERE id = 1");
$stmt->execute([$filename]);

echo "Fixed APK record ID 1. Dummy file created at: $filepath\n";
