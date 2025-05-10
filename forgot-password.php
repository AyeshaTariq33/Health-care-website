<?php
declare(strict_types=1);

session_start();
require_once 'includes/database.php';
require_once 'config.php';

$error = '';
$success = '';
$show_form = true;

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid form submission');
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Check if user exists
        $stmt = $pdo->prepare("
            SELECT id FROM user 
            WHERE email = ? 
            AND account_locked = 0
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $token_hash = password_hash($token, PASSWORD_DEFAULT);
            $expires_at = date('Y-m-d H:i:s', time() + 900); // 15 minutes

            // Store token in database
            $stmt = $pdo->prepare("
                UPDATE user 
                SET reset_token = ?, 
                    reset_token_expires = ? 
                WHERE id = ?
            ");
            $stmt->execute([$token_hash, $expires_at, $user['id']]);

            // Send reset email
            $reset_link = BASE_URL . "reset-password.php?token=" . urlencode($token);
            $subject = "Password Reset Request - MediCare+";
            $message = "
                <h2>Password Reset Request</h2>
                <p>We received a request to reset your password. Click the link below to proceed:</p>
                <p><a href='$reset_link'>Reset Password</a></p>
                <p>This link will expire in 15 minutes.</p>
                <p>If you didn't request this, please ignore this email.</p>
            ";

            $headers = "From: " . EMAIL_FROM . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            mail($email, $subject, $message, $headers);
        }

        // Always show success message to prevent email enumeration
        $_SESSION['success'] = "If the email exists, a password reset link has been sent.";
        header("Location: forgot-password.php");
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Password Recovery - MediCare+</title>
    <style>
        .password-reset-card {
            max-width: 500px;
            margin: 2rem auto;
            border-radius: 1rem;
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg password-reset-card">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="hospital-icon mb-3">🏥</div>
                                <h2>Password Recovery</h2>
                                <p class="text-muted">Enter your email to reset your password</p>
                            </div>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success">
                                    <?= htmlspecialchars($_SESSION['success']) ?>
                                    <?php unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="csrf_token" 
                                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                    </label>
                                    <input type="email" 
                                           class="form-control form-control-lg" 
                                           id="email" 
                                           name="email" 
                                           required
                                           autocomplete="email"
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-key me-2"></i>Reset Password
                                    </button>
                                </div>

                                <div class="text-center small">
                                    Remember your password? 
                                    <a href="login.php" class="text-decoration-none">Login here</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
