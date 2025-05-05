<?php
session_start();
// Redirect if already logged in
if(isset($_SESSION['user_role'])) {
    header("Location: panels/" . $_SESSION['user_role'] . "/dashboard.php");
    exit();
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

                    <?php if(isset($_SESSION['register_error'])): ?>
                        <div class="alert alert-danger"><?= 
                            $_SESSION['register_error']; 
                            unset($_SESSION['register_error']); 
                        ?></div>
                    <?php endif; ?>

                    <form method="POST" action="processes/auth/register.php">
                        <div class="row g-3">
                            <!-- Full Name -->
                            <div class="col-12">
                                <label for="fullname" class="form-label">
                                    <i class="fas fa-user me-2"></i>Full Name
                                </label>
                                <input type="text" class="form-control" id="fullname" 
                                       name="fullname" required>
                            </div>

                            <!-- Email -->
                            <div class="col-12">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" 
                                       name="email" required>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control" 
                                       id="password" name="password" required>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Confirm Password
                                </label>
                                <input type="password" class="form-control" 
                                       id="confirm_password" name="confirm_password" required>
                            </div>

                            <!-- User Type -->
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-user-tag me-2"></i>Account Type
                                </label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="role" id="patient" value="patient" checked>
                                        <label class="form-check-label" for="patient">
                                            Patient
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="role" id="doctor" value="doctor">
                                        <label class="form-check-label" for="doctor">
                                            Doctor
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="role" id="admin" value="admin">
                                        <label class="form-check-label" for="admin">
                                            Admin
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="role" id="pharmacy" value="pharmacy">
                                        <label class="form-check-label" for="pharmacy">
                                            Pharmacy
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="role" id="lab" value="lab">
                                        <label class="form-check-label" for="lab">
                                            Lab
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms Checkbox -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="terms" required>
                                    <label class="form-check-label small" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a>
                                        and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? 
                            <a href="login.php" class="text-decoration-none">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>