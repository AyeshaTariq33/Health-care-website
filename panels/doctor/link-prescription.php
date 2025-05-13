<?php
session_start();
if ($_SESSION['user_role'] !== 'doctor') header("Location: /login.php");

if ($_POST) {
    $pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');
    $stmt = $pdo->prepare("UPDATE prescriptions SET pharmacy_id = ? WHERE id = ?");
    $stmt->execute([$_POST['pharmacy_id'], $_POST['prescription_id']]);
    header("Location: dashboard.php?success=prescription_linked");
}
?>

<form method="post">
    <!-- Simple dropdown to link prescription to pharmacy -->
</form>