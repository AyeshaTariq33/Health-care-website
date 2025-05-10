<?php
declare(strict_types=1);

session_start();
require_once 'includes/database.php';
require_once 'includes/auth-check.php';  // For CSRF protection

// Security headers
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");

// Disable error display in production
if (ENVIRONMENT === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Initialize variables
$allowed_roles = ['patient', 'doctor', 'pharmacy', 'lab']; // Removed admin from self-registration
$min_password_strength = 3; // zxcvbn score

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid form submission');
        }

        // Sanitize inputs
        $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = in_array($_POST['role'] ?? 'patient', $allowed_roles) ? $_POST['role'] : 'patient';

        // Validate inputs
        $errors = [];
        
        // Name validation
        if (empty($firstName) || !preg_match("/^[a-zA-Z-' ]{2,50}$/", $firstName)) {
            $errors[] = "Valid first name (2-50 characters) required";
        }
        
        if (empty($lastName) || !preg_match("/^[a-zA-Z-' ]{2,50}$/", $lastName)) {
            $errors[] = "Valid last name (2-50 characters) required";
        }

        // Email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Password validation
        if (strlen($password) < 12) {
            $errors[] = "Password must be at least 12 characters";
        } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/", $password)) {
            $errors[] = "Password must contain uppercase, lowercase, number, and special character";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        }

        // Check password strength
        if (password_strength($password) < $min_password_strength) {
            $errors[] = "Password is too weak";
        }

        if (!empty($errors)) {
            throw new Exception(implode('|', $errors));
        }

        // Check email existence
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('Email already registered');
        }

        // Create verification token
        $verificationToken = bin2hex(random_bytes(32));
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO user 
            (first_name, last_name, email, password_hash, role, verification_token, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        if (!$stmt->execute([$firstName, $lastName, $email, $hashedPassword, $role, $verificationToken])) {
            throw new Exception('Registration failed');
        }

        // Send verification email
        sendVerificationEmail($email, $verificationToken);

        // Store success in session
        $_SESSION['success'] = 'Registration successful! Please check your email to verify your account';
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        error_log("Registration Error: " . $e->getMessage());
        $errorMessages = explode('|', $e->getMessage());
        $_SESSION['form_errors'] = $errorMessages;
        $_SESSION['old_input'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'role' => $role
        ];
        header("Location: register.php");
        exit();
    }
}

// Password strength checker
function password_strength(string $password): int {
    $zxcvbn = new Zxcvbn(); // Requires zxcvbn-php library
    return $zxcvbn->passwordStrength($password)['score'];
}

// Verification email sender
function sendVerificationEmail(string $email, string $token): void {
    $verificationLink = BASE_URL . "verify-email.php?token=$token";
    $subject = "Verify Your MediCare+ Account";
    $message = "<h2>Welcome to MediCare+</h2>
               <p>Please click the link below to verify your email address:</p>
               <a href='$verificationLink'>Verify Email</a>
               <p>This link will expire in 24 hours.</p>";
    
    $headers = "From: no-reply@medicareplus.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    mail($email, $subject, $message, $headers);
}

// Generate new CSRF token if needed
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Register - MediCare+</title>
    <style>
        .password-rules {
            font-size: 0.9rem;
            color: #666;
        }
        .strength-meter {
            height: 5px;
            margin-top: 5px;
            background-color: #eee;
        }
        .strength-meter-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="auth-page">
    <main class="auth-container">
        <div class="card shadow-lg auth-card">
            <div class="card-body">
                <div class="text-center mb-5">
                    <div class="hospital-icon display-4">🏥</div>
                    <h1 class="h3 mb-3">Create Account</h1>
                    <p class="text-muted">Join our healthcare community</p>
                </div>

                <?php if (!empty($_SESSION['form_errors'])): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['form_errors']); ?>
                <?php endif; ?>

                <form method="POST" id="registrationForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                   value="<?= htmlspecialchars($_SESSION['old_input']['first_name'] ?? '') ?>"
                                   required pattern="[A-Za-z-' ]{2,50}" maxlength="50">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                   value="<?= htmlspecialchars($_SESSION['old_input']['last_name'] ?? '') ?>"
                                   required pattern="[A-Za-z-' ]{2,50}" maxlength="50">
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($_SESSION['old_input']['email'] ?? '') ?>"
                                   required maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   required minlength="12">
                            <div class="strength-meter mt-1">
                                <div class="strength-meter-fill"></div>
                            </div>
                            <div class="password-rules">
                                Requires: 12+ chars, uppercase, lowercase, number, special character
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   required minlength="12">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Account Type</label>
                            <div class="d-flex gap-3 flex-wrap">
                                <?php foreach ($allowed_roles as $role): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="role" 
                                               id="<?= $role ?>" value="<?= $role ?>"
                                            <?= (($_SESSION['old_input']['role'] ?? 'patient') === $role) ? 'checked' : '' ?>>
                                        <label class="form-check-label text-capitalize" for="<?= $role ?>">
                                            <?= $role ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label small" for="terms">
                                    I agree to the <a href="terms.php" class="text-decoration-none">Terms of Service</a>
                                    and <a href="privacy.php" class="text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0">Already have an account? 
                        <a href="login.php" class="text-decoration-none">Sign in here</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthMeter = document.querySelector('.strength-meter-fill');
            const strength = Math.min(password.length / 12 * 100, 100);
            strengthMeter.style.width = strength + '%';
            strengthMeter.style.backgroundColor = strength < 40 ? '#dc3545' : 
                                                strength < 70 ? '#ffc107' : '#28a745';
        });

        // Password toggle
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                input.type = input.type === 'password' ? 'text' : 'password';
                icon.classList.toggle('fa-eye-slash');
                icon.classList.toggle('fa-eye');
            });
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
<?php
// Clear old input after display
if (isset($_SESSION['old_input'])) {
    unset($_SESSION['old_input']);
}