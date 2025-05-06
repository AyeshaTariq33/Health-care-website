<?php
session_start();
require_once 'includes/database.php';

// Enable error reporting (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables
$errors = [];
$allowed_roles = ['patient', 'doctor', 'admin', 'pharmacy', 'lab'];
$old_input = [
    'fullname' => '',
    'email' => '',
    'role' => 'patient'
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $fullname = trim(htmlspecialchars($_POST['fullname'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = in_array($_POST['role'] ?? 'patient', $allowed_roles) ? $_POST['role'] : 'patient';

    // Store old input for redisplay
    $old_input = [
        'fullname' => $fullname,
        'email' => $email,
        'role' => $role
    ];

    // Validate inputs
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Process if no errors
    if (empty($errors)) {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Email already registered!";
                header("Location: register.php");
                exit();
            }

            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO user (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $hashed_password, $role]);

            $_SESSION['success'] = "Registration successful! Please login";
            header("Location: login.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['error'] = "Registration error: " . $e->getMessage();
            header("Location: register.php");
            exit();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        $_SESSION['old_input'] = $old_input;
        header("Location: register.php");
        exit();
    }
}

// Retrieve old input from session if available
if (isset($_SESSION['old_input'])) {
    $old_input = array_merge($old_input, $_SESSION['old_input']);
    unset($_SESSION['old_input']);
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>
<link href="assets/css/auth.css" rel="stylesheet">

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="auth-card shadow-lg p-4">
                    <div class="text-center mb-4">
                        <div class="hospital-icon mb-3">🏥</div>
                        <h2>Create Your MediCare+ Account</h2>
                        <p class="text-muted">Join our healthcare network</p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <!-- Full Name -->
                            <div class="col-12">
                                <label for="fullname" class="form-label">
                                    <i class="fas fa-user me-2"></i>Full Name
                                </label>
                                <input type="text" class="form-control" id="fullname" name="fullname" 
                                       value="<?= htmlspecialchars($old_input['fullname']) ?>" required>
                            </div>

                            <!-- Email -->
                            <div class="col-12">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($old_input['email']) ?>" required>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Confirm Password
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <!-- User Type -->
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-user-tag me-2"></i>Account Type
                                </label>
                                <div class="d-flex gap-3 flex-wrap">
                                    <?php foreach ($allowed_roles as $role): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="role" 
                                                   id="<?= $role ?>" value="<?= $role ?>"
                                                <?= ($old_input['role'] === $role) ? 'checked' : '' ?>>
                                            <label class="form-check-label text-capitalize" for="<?= $role ?>">
                                                <?= $role ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Terms Checkbox -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label small" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a>
                                        and <a href="#" class="text-decoration-none">Privacy Policy</a>
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
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>