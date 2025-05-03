<?php
session_start();
if(isset($_SESSION['patient_logged_in'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>
<link href="../../assets/css/patient.css" rel="stylesheet">

<div class="patient-auth-container" >
    <div class="auth-card">
        <div class="auth-header">
            <h2>🏥 MediCare+</h2>
            <h5> Patient Login</h5>
            <p>Access your health portal</p>
        </div>

        <form class="auth-form" method="POST" action="process-login.php">
            <div class="input-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" placeholder="john@example.com" required>
            </div>

            <div class="input-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <span class="password-toggle"><i class="fas fa-eye"></i></span>
            </div>

            <button type="submit" class="auth-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <div class="auth-links">
                <a href="register.php">Create new account</a>
            </div>
        </form>
    </div>

    <div class="auth-side-image">
        <img src="../../assets/img/patient-login-illustration.jpg" alt="Healthcare Illustration">
    </div>
</div>
<?php include '../../includes/footer.php'; ?>