<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_role'])) {
    header("Location: panels/" . $_SESSION['user_role'] . "/dashboard.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO user (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $role]);
        
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_role'] = $role;
        header("Location: panels/" . $role . "/dashboard.php");
        exit();
    } catch(PDOException $e) {
        $error = 'Registration failed - email may already exist';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <title>Register</title>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="auth-page">
        <div class="container mb-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg mt-5">
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="first_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Role</label>
                                        <select class="form-select" name="role" required>
                                            <option value="patient">Patient</option>
                                            <option value="doctor">Doctor</option>
                                            <option value="pharmacy">Pharmacy</option>
                                            <option value="lab">Lab</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary w-100">Register</button>
                                    </div>
                                </div>
                            </form>
                            
                            <div class="text-center mt-3">
                                <a href="login.php">Login here</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>