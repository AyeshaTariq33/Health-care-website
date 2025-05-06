<?php
session_start();
require_once 'includes/database.php';

// Redirect if already logged in
if(isset($_SESSION['user_role'])) {
    header("Location: Panels/" . $_SESSION['user_role'] . "/dashboard.php");
    exit();
}

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirect to dashboard
            header("Location: Panels/" . $user['role'] . "/dashboard.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid email or password";
            header("Location: login.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['login_error'] = "Database error: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>
<link href="assets/css/style.css" rel="stylesheet">

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="auth-card shadow-lg p-4">
                    <div class="text-center mb-4">
                        <div class="hospital-icon mb-3">🏥</div>
                        <h2>MediCare+ Portal</h2>
                        <p class="text-muted">Secure Access for All Users</p>
                    </div>

                    <?php if(isset($_SESSION['login_error'])): ?>
                        <div class="alert alert-danger"><?= 
                            $_SESSION['login_error']; 
                            unset($_SESSION['login_error']); 
                        ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="name@example.com" required>
                        </div>

                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <input type="password" class="form-control pe-5" id="password" 
                                   name="password" placeholder="Enter password" required>
                            <span class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>

                        <div class="text-center">
                            <p class="mb-1">New user? 
                                <a href="register.php" class="text-decoration-none">Create account</a>
                            </p>
                            <a href="forgot-password.php" class="text-decoration-none small">
                                <i class="fas fa-question-circle me-1"></i>Forgot Password?
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password toggle functionality
document.querySelector('.password-toggle').addEventListener('click', function() {
    const passwordField = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if(passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
        passwordField.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
});
</script>

<?php include 'includes/footer.php'; ?>