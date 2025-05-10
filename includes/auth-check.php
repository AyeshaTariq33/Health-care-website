<?php
declare(strict_types=1);

// Session Configuration
$session_name = 'MEDICARE_SESS';
$session_lifetime = 1800; // 30 minutes
$session_path = '/';
$session_domain = $_SERVER['HTTP_HOST'];
$session_secure = true; // Set to true in production
$session_http_only = true;
$session_samesite = 'Strict';

// Secure Session Initialization
if (session_status() === PHP_SESSION_NONE) {
    session_name($session_name);
    session_set_cookie_params([
        'lifetime' => $session_lifetime,
        'path' => $session_path,
        'domain' => $session_domain,
        'secure' => $session_secure,
        'httponly' => $session_http_only,
        'samesite' => $session_samesite
    ]);
    
    session_start();
    
    // Regenerate ID if session is empty
    if (empty($_SESSION)) {
        session_regenerate_id(true);
    }
}

// Authentication Check
if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    // Log access attempt
    error_log('Unauthorized access attempt from IP: ' . $_SERVER['REMOTE_ADDR'] . 
              ' to URL: ' . $_SERVER['REQUEST_URI']);
    
    // Clear existing session
    session_unset();
    session_destroy();
    
    // Secure redirect
    header('Location: /login.php?error=unauthorized');
    exit();
}

// Role Validation
$valid_roles = ['patient', 'doctor', 'admin', 'pharmacy', 'lab'];
if (!in_array($_SESSION['user_role'], $valid_roles, true)) {
    session_unset();
    session_destroy();
    header('Location: /login.php?error=invalid_role');
    exit();
}

// Session Fixation Protection
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 900) { // 15 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Session Hijacking Protection
$user_agent_hash = hash('sha256', $_SERVER['HTTP_USER_AGENT']);
if (!isset($_SESSION['user_agent_hash'])) {
    $_SESSION['user_agent_hash'] = $user_agent_hash;
} else {
    if ($_SESSION['user_agent_hash'] !== $user_agent_hash) {
        session_unset();
        session_destroy();
        header('Location: /login.php?error=session_hijack');
        exit();
    }
}

// Session Timeout
$inactive_timeout = 1800; // 30 minutes
if (isset($_SESSION['last_activity']) && 
   (time() - $_SESSION['last_activity'] > $inactive_timeout)) {
    session_unset();
    session_destroy();
    header('Location: /login.php?error=session_expired');
    exit();
}
$_SESSION['last_activity'] = time();

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains'); // Enable HTTPS in production

// IP Binding (Optional - enable in production)
/*
if (!isset($_SESSION['ip_address'])) {
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
} else {
    if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header('Location: /login.php?error=ip_mismatch');
        exit();
    }
}
*/

// Role-Based Access Control (Example - customize per panel)
/*
$allowed_roles = ['patient']; // Set per panel
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header('HTTP/1.1 403 Forbidden');
    include_once __DIR__ . '/../403.php';
    exit();
}
*/