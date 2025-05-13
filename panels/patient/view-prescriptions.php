<?php
session_start();
if ($_SESSION['user_role'] !== 'patient') header("Location: /login.php");

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');
$prescriptions = $pdo->query("
    SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) AS doctor_name 
    FROM prescriptions p
    JOIN user u ON p.doctor_id = u.id
    WHERE p.patient_id = {$_SESSION['user_id']}
")->fetchAll();
?>

<!-- Simple table showing prescriptions -->
<table class="table">
    <tr><th>Date</th><th>Doctor</th><th>Medication</th><th>Dosage</th></tr>
    <?php foreach ($prescriptions as $p): ?>
    <tr>
        <td><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
        <td>Dr. <?= $p['doctor_name'] ?></td>
        <td><?= $p['medication'] ?></td>
        <td><?= $p['dosage'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>