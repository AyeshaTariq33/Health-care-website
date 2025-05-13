<?php
session_start();
if ($_SESSION['user_role'] !== 'patient') header("Location: /login.php");

if ($_POST) {
    $pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');
    $stmt = $pdo->prepare("INSERT INTO lab_orders (patient_id, test_type) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['test_type']]);
    header("Location: dashboard.php?success=test_booked");
}
?>

<form method="post">
    <select name="test_type" class="form-control" required>
        <option value="blood_test">Blood Test</option>
        <option value="xray">X-Ray</option>
    </select>
    <button type="submit" class="btn btn-primary">Book Test</button>
</form>