<?php
declare(strict_types=1);

/******************************************************
* Secure Session Termination with Full Cleanup
******************************************************/

// Initialize secure session
session_name('MEDICARE_SESS');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Validate CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die('Invalid logout request');
    }
}

// Destroy session completely
$_SESSION = [];
session_unset();

// Delete session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite']
        ]
    );
}

// Destroy server-side session
session_destroy();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Redirect with delay for browser processing
header("Refresh: 3; url=login.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Logging Out - MediCare+</title>
    <style>
        .logout-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        .logout-message {
            max-width: 600px;
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-message">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h1 class="h3 mb-3">Securely Logging You Out</h1>
            <p class="text-muted">
                All session data is being cleared...<br>
                You will be redirected to the login page shortly.
            </p>
            <div class="progress mt-4" style="height: 4px;">
                <div class="progress-bar" role="progressbar" style="width: 0%" 
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Animated progress bar
        document.addEventListener('DOMContentLoaded', () => {
            const progressBar = document.querySelector('.progress-bar');
            let width = 0;
            const interval = setInterval(() => {
                if (width >= 100) {
                    clearInterval(interval);
                    return;
                }
                width += 3.33; // Match 3-second redirect
                progressBar.style.width = `${width}%`;
            }, 100);
        });
    </script>
</body>
</html>