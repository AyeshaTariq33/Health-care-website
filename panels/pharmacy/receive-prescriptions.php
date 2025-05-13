<?php
session_start();
if ($_SESSION['user_role'] !== 'pharmacy') header("Location: /login.php");

$prescriptions = $pdo->query("
    SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) AS patient_name
    FROM prescriptions p
    JOIN user u ON p.patient_id = u.id
    WHERE p.pharmacy_id = {$_SESSION['user_id']}
")->fetchAll();
?>

<!-- Display prescriptions with process button -->