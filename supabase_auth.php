<?php
require_once dirname(__DIR__) . '/config/config.php';

// Accept JSON payload
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['access_token'])) {
    json_response(['success' => false, 'error' => 'Missing access token']);
}

$access_token = $data['access_token'];

// Verify the token by calling Supabase API
$ch = curl_init(rtrim(SUPABASE_URL, '/') . '/auth/v1/user');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . SUPABASE_PUBLISHABLE_KEY,
    'Authorization: Bearer ' . $access_token
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode !== 200 || !$response) {
    json_response(['success' => false, 'error' => 'Invalid or expired token']);
}

$user_data = json_decode($response, true);

if (!isset($user_data['email'])) {
    json_response(['success' => false, 'error' => 'Could not retrieve email from Google']);
}

$email = $user_data['email'];
$google_id = $user_data['id'] ?? '';
$full_name = $user_data['user_metadata']['full_name'] ?? '';
$avatar_url = $user_data['user_metadata']['avatar_url'] ?? '';

// Check if user already exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    if ($user['is_banned']) {
        json_response(['success' => false, 'error' => 'Your account has been suspended.']);
    }
    
    // Log the user in
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['avatar'] = $user['avatar'];
    
    // Update last login
    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
    
    flash('success', "Welcome back, " . e($user['username']) . "!");
} else {
    // Register the new user
    $pdo->beginTransaction();
    try {
        // Generate a random, safe password for Google users
        $random_password = bin2hex(random_bytes(16));
        $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
        
        // Generate a unique username based on full name or email
        $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
        if (empty($base_username)) $base_username = 'user';
        
        // Ensure username is unique
        $username = $base_username;
        $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if (!$stmt->fetch()) break;
            $username = $base_username . $i++;
        }
        
        $role = 'user'; // Default role, user can apply for dev later
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, full_name, is_verified, created_at, last_login) VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())");
        $stmt->execute([$username, $email, $hashed_password, $role, $full_name]);
        $user_id = $pdo->lastInsertId();
        
        $pdo->commit();
        
        // Log the new user in
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['avatar'] = null; // Avatar might be set via download, but we leave as default for now
        
        flash('success', 'Registration successful! Welcome to ' . SITE_NAME . '.');
    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['success' => false, 'error' => 'Database error during registration.']);
    }
}

// Determine redirect URL
$redirect_url = SITE_URL . '/pages/user/dashboard.php';
if ($_SESSION['role'] === 'admin') {
    $redirect_url = SITE_URL . '/pages/admin/dashboard.php';
} elseif ($_SESSION['role'] === 'developer') {
    $redirect_url = SITE_URL . '/pages/developer/dashboard.php';
}

json_response([
    'success' => true,
    'redirect' => $redirect_url
]);
