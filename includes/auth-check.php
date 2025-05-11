<?php
declare(strict_types=1);

session_name('MEDICARE_SESS');
session_start();

$base_url = "/healthcare-project/Health-care-Website";

// Session validation
if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    error_log('Unauthorized access attempt from IP: ' . $_SERVER['REMOTE_ADDR']);
    session_unset();
    session_destroy();
    header("Location: $base_url/login.php?error=unauthorized");
    exit();
}

// Normalize role to lowercase
$user_role = strtolower($_SESSION['user_role']);
$allowed_roles = ['patient', 'doctor', 'admin', 'pharmacy', 'lab'];

if (!in_array($user_role, $allowed_roles, true)) {
    session_unset();
    session_destroy();
    header("Location: $base_url/login.php?error=invalid_role");
    exit();
}

// Session fixation protection
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 900) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Session hijacking protection
$user_agent_hash = hash('sha256', $_SERVER['HTTP_USER_AGENT']);
if (!isset($_SESSION['user_agent_hash'])) {
    $_SESSION['user_agent_hash'] = $user_agent_hash;
} elseif ($_SESSION['user_agent_hash'] !== $user_agent_hash) {
    session_unset();
    session_destroy();
    header("Location: $base_url/login.php?error=session_hijack");
    exit();
}

// Session timeout (30 minutes)
$inactive_timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_timeout)) {
    session_unset();
    session_destroy();
    header("Location: $base_url/login.php?error=session_expired");
    exit();
}
$_SESSION['last_activity'] = time();

// CSRF token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Role-based access control
$current_page = basename($_SERVER['PHP_SELF']);
$allowed_pages = [
    'patient' => ['dashboard.php', 'appointments.php'],
    'doctor' => ['dashboard.php', 'patients.php'],
    'admin' => ['dashboard.php', 'users.php'],
    'pharmacy' => ['dashboard.php', 'inventory.php'],
    'lab' => ['dashboard.php', 'reports.php']
];

if (!in_array($current_page, $allowed_pages[$user_role])) {
    header("Location: $base_url/panels/$user_role/dashboard.php");
    exit();
}
?>