<?php
declare(strict_types=1);

session_start();
require_once 'includes/database.php';
require_once 'config.php';

$error = '';
$success = '';
$valid_token = false;
$show_form = false;

// Handle token verification
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    $token = trim($_GET['token']);

    try {
        $stmt = $pdo->prepare("
            SELECT id, reset_token, reset_token_expires 
            FROM user 
            WHERE reset_token_expires > NOW()
        ");
        $stmt->execute();
        
        while ($user = $stmt->fetch()) {
            if (password_verify($token, $user['reset_token'])) {
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_token_used'] = false;
                $valid_token = true;
                $show_form = true;
                break;
            }
        }

        if (!$valid_token) {
            $error = "Invalid or expired password reset link";
            log_reset_attempt($_SERVER['REMOTE_ADDR'], $token, 'invalid_token');
        }

    } catch (PDOException $e) {
        error_log("Password Reset Error: " . $e->getMessage());
        $error = "Error processing your request";
    }
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_SESSION['reset_user_id']) || $_SESSION['reset_token_used']) {
            throw new Exception("Reset session expired");
        }

        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new Exception("Invalid form submission");
        }

        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate passwords
        if (empty($password) || $password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        if (strlen($password) < 12) {
            throw new Exception("Password must be at least 12 characters");
        }

        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/", $password)) {
            throw new Exception("Password must contain uppercase, lowercase, number, and special character");
        }

        // Update password
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
        
        $stmt = $pdo->prepare("
            UPDATE user 
            SET password_hash = ?, 
                reset_token = NULL, 
                reset_token_expires = NULL,
                last_password_change = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$hashed_password, $_SESSION['reset_user_id']]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Password update failed");
        }

        // Log successful reset
        log_reset_attempt($_SERVER['REMOTE_ADDR'], $token ?? '', 'success');
        
        // Clear reset session
        unset($_SESSION['reset_user_id']);
        $_SESSION['reset_token_used'] = true;
        
        $_SESSION['success'] = "Password reset successfully! You can now login";
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
        log_reset_attempt($_SERVER['REMOTE_ADDR'], $token ?? '', 'error: ' . $error);
        $show_form = true; // Show form to allow retry
    }
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Logging function
function log_reset_attempt(string $ip, string $token, string $status): void {
    $log_entry = sprintf(
        "[%s] Reset attempt - IP: %s, Token: %s, Status: %s\n",
        date('Y-m-d H:i:s'),
        $ip,
        $token,
        $status
    );
    error_log($log_entry, 3, LOG_PATH . 'password_resets.log');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Reset Password - MediCare+</title>
    <style>
        .password-strength-meter {
            height: 5px;
            background-color: #eee;
            margin-top: 5px;
        }
        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="hospital-icon mb-3">🏥</div>
                                <h2>Reset Your Password</h2>
                                <?php if ($valid_token): ?>
                                    <p class="text-muted">Enter your new password below</p>
                                <?php endif; ?>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($show_form && $valid_token): ?>
                                <form method="POST" id="resetForm">
                                    <input type="hidden" name="csrf_token" 
                                           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>New Password
                                        </label>
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               id="password" 
                                               name="password" 
                                               required
                                               autocomplete="new-password">
                                        <div class="password-strength-meter mt-2">
                                            <div class="password-strength-fill"></div>
                                        </div>
                                        <small class="text-muted">
                                            Must be 12+ chars with uppercase, lowercase, number, and special character
                                        </small>
                                    </div>

                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Confirm Password
                                        </label>
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               required
                                               autocomplete="new-password">
                                    </div>

                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-sync-alt me-2"></i>Reset Password
                                        </button>
                                    </div>
                                </form>
                            <?php elseif (!$valid_token): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    The password reset link is invalid or has expired. 
                                    Please request a new reset link.
                                </div>
                                <div class="text-center mt-3">
                                    <a href="forgot-password.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Password Reset
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthMeter = document.querySelector('.password-strength-fill');
            const strength = calculatePasswordStrength(password);
            
            strengthMeter.style.width = strength + '%';
            strengthMeter.style.backgroundColor = getStrengthColor(strength);
        });

        function calculatePasswordStrength(password) {
            // Implement zxcvbn or similar for real strength calculation
            // Simplified version for demonstration:
            let strength = 0;
            if (password.length >= 12) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            if (password.match(/[\W_]/)) strength += 25;
            
            return Math.min(strength, 100);
        }

        function getStrengthColor(strength) {
            if (strength < 40) return '#dc3545';
            if (strength < 70) return '#ffc107';
            return '#28a745';
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>