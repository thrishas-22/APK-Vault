<?php
require_once 'config/config.php';
$stmt = $pdo->query("SELECT id, title, file_path, status FROM apks");
while ($row = $stmt->fetch()) {
    echo "ID: {$row['id']} | Title: {$row['title']} | Path: {$row['file_path']} | Status: {$row['status']}\n";
}
