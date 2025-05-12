<?php
session_start();

// Redirect logged-in users
if (isset($_SESSION['user_role'])) {
    header("Location: panels/" . $_SESSION['user_role'] . "/dashboard.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Simple validation
    if (empty($firstName)) $errors[] = "First name required";
    if (empty($lastName)) $errors[] = "Last name required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if (strlen($password) < 8) $errors[] = "Password must be 8+ characters";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO user (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $role]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_role'] = $role;
        header("Location: panels/" . $role . "/dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include 'includes/header.php'; ?>
    <title>Register</title>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Register</h2>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <div><?= $error ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="text" name="first_name" class="form-control mb-3" placeholder="First Name" required>
                            <input type="text" name="last_name" class="form-control mb-3" placeholder="Last Name" required>
                            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
                            <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                            <select name="role" class="form-select mb-3">
                                <option value="patient">Patient</option>
                                <option value="doctor">Doctor</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="lab">Lab</option>
                                <option value="admin">Admin</option>
                            </select>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="login.php" class="text-decoration-none">Existing user? Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>