<?php
session_start();

// Redirect if not logged in or wrong role
if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

// Verify role matches dashboard
$required_role = basename(dirname($_SERVER['PHP_SELF'])); // Get role from directory name
if ($_SESSION['user_role'] !== $required_role) {
    header("Location: ../../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title><?= ucfirst($required_role) ?> Dashboard</title>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <main class="container mt-4">
        <h2>Welcome <?= $_SESSION['user_role'] ?> User</h2>
        <p>This is your <?= $_SESSION['user_role'] ?> dashboard</p>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>