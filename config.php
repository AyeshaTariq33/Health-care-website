<?php
declare(strict_types=1);

/*******************************************************
* Core Configuration Settings for MediCare+ Application
*******************************************************/

// Environment Configuration
define('ENVIRONMENT', 'development'); // 'production' or 'development'
define('BASE_URL', 'http://localhost/health-care-website/');
define('TIMEZONE', 'Asia/Karachi');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'healthcare-db');
define('DB_USER', 'healthcare_admin');
define('DB_PASS', 'SecurePassword123!');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', '3306');
define('DB_SSL', false); // Set to true in production

// Path Configuration
define('ASSETS_PATH', BASE_URL . 'assets/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('LOG_PATH', __DIR__ . '/../logs/');
define('TEMPLATE_PATH', __DIR__ . '/templates/');

// Security Configuration
define('CSRF_TOKEN_NAME', 'medicare_csrf');
define('PASSWORD_PEPPER', '2e8a9c7b1d6f4e3a5b8c9d0e1f2a3b4c');
define('ENCRYPTION_KEY', 'd6f4e3a5b8c9d0e1f2a3b4c5d6e7f8a9b');
define('ALLOWED_ROLES', ['patient', 'doctor', 'admin', 'pharmacy', 'lab']);
define('SESSION_INACTIVITY_TIMEOUT', 1800); // 30 minutes in seconds

// Session Configuration
define('SESSION_NAME', 'MEDICARE_SESS');
define('SESSION_LIFETIME', 0); // Until browser closes
define('SESSION_COOKIE_PARAMS', [
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
    'secure' => ENVIRONMENT === 'production',
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Email Configuration
define('SMTP_HOST', 'smtp.medicareplus.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@medicareplus.com');
define('SMTP_PASS', 'EmailPassword123!');
define('EMAIL_FROM', 'noreply@medicareplus.com');
define('ADMIN_EMAIL', 'admin@medicareplus.com');

// File Upload Configuration
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'application/pdf',
    'text/plain'
]);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_AVATAR_SIZE', 2 * 1024 * 1024); // 2MB

// Error Handling Configuration
define('DISPLAY_ERRORS', ENVIRONMENT === 'development');
define('LOG_ERRORS', true);
define('ERROR_LOG', LOG_PATH . 'application_errors.log');
define('DB_ERROR_LOG', LOG_PATH . 'database_errors.log');

// API Configuration
define('GOOGLE_MAPS_API_KEY', 'AIzaSyYourApiKeyHere');
define('PAYMENT_GATEWAY_KEY', 'sk_test_YourStripeKeyHere');

// Application Features
define('MAINTENANCE_MODE', false);
define('REGISTRATION_OPEN', true);
define('EMAIL_VERIFICATION', true);

/*******************************************************
* Environment Setup - Do Not Modify Below This Line
*******************************************************/

// Set default timezone
date_default_timezone_set(TIMEZONE);

// Error Reporting Configuration
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '0');
}

// Create logs directory if not exists
if (!file_exists(LOG_PATH) {
    mkdir(LOG_PATH, 0755, true);
}

// Secure Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (ENVIRONMENT === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Prevent Direct Access
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

// Database DSN
define('DB_DSN', sprintf(
    'mysql:host=%s;dbname=%s;port=%s;charset=%s',
    DB_HOST,
    DB_NAME,
    DB_PORT,
    DB_CHARSET
));