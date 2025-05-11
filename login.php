<?php
declare(strict_types=1);

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_role'])) {
    $base_url = "/healthcare-project/Health-care-Website";
    header("Location: $base_url/panels/" . $_SESSION['user_role'] . "/dashboard.php");
    exit();
}

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/config.php';

$error = '';
$login_attempts = $_SESSION['login_attempts'] ?? 0;
$base_url = "/healthcare-project/Health-care-Website";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting
    if ($login_attempts >= MAX_LOGIN_ATTEMPTS) {
        $error = "Too many failed attempts. Please try again later.";
    } else {
        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            $error = "Invalid form submission";
        } else {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            try {
                $stmt = $pdo->prepare("
                    SELECT id, full_name, password_hash, role 
                    FROM user 
                    WHERE email = ? 
                    AND account_locked = 0
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Successful login
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_fullname'] = $user['full_name'];
                    $_SESSION['last_login'] = time();
                    
                    // Reset login attempts
                    unset($_SESSION['login_attempts']);
                    
                    header("Location: $base_url/panels/" . $user['role'] . "/dashboard.php");
                    exit();
                } else {
                    $error = "Invalid credentials";
                    $_SESSION['login_attempts'] = ++$login_attempts;
                }
            } catch(PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = "System error. Please try again later.";
            }
        }
    }
}

// Generate new CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <title>Login - MediCare+</title>
</head>
<body class="auth-page">
    <main class="auth-container">
        <div class="card shadow-lg auth-card">
            <div class="card-body">
                <div class="text-center mb-5">
                    <div class="hospital-icon display-4">🏥</div>
                    <h1 class="h3 mb-3">MediCare+ Portal</h1>
                    <p class="text-muted">Secure access for patients and medical staff</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <input type="hidden" name="csrf_token" 
                           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="mb-4">
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

                    <div class="mb-4 position-relative">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg pe-5" 
                               id="password" 
                               name="password"
                               required
                               autocomplete="current-password">
                        <button type="button" class="btn btn-link password-toggle">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>

                    <div class="text-center small">
                        <a href="<?= $base_url ?>/forgot-password.php" class="text-decoration-none">
                            <i class="fas fa-question-circle me-1"></i>Forgot Password?
                        </a>
                        <div class="mt-2">
                            New user? <a href="<?= $base_url ?>/register.php" class="text-decoration-none">Create account</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Password toggle functionality
        document.querySelector('.password-toggle').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                passwordField.type = 'password';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        });
    </script>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>