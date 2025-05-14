<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pharmacy') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

$prescriptions = $pdo->query("
    SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) AS patient_name
    FROM prescriptions p
    JOIN user u ON p.patient_id = u.id
    WHERE p.pharmacy_id = {$_SESSION['user_id']} AND p.status = 'pending'
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receive Prescriptions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h3>Received Prescriptions</h3>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Prescription ID</th>
                    <th>Patient</th>
                    <th>Medication</th>
                    <th>Dosage</th>
                    <th>Instructions</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prescriptions as $prescription): ?>
                <tr>
                    <td><?= $prescription['id'] ?></td>
                    <td><?= htmlspecialchars($prescription['patient_name']) ?></td>
                    <td><?= htmlspecialchars($prescription['medication']) ?></td>
                    <td><?= htmlspecialchars($prescription['dosage']) ?></td>
                    <td><?= htmlspecialchars($prescription['instructions']) ?></td>
                    <td><?= date('M j, Y', strtotime($prescription['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>