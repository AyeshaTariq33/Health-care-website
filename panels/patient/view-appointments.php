<?php
session_start();

// Only allow logged-in patients
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit();
}

// Fetch appointments
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');
$appointments = $pdo->query("
    SELECT a.date, a.reason, a.status, 
           CONCAT(u.first_name, ' ', u.last_name) AS doctor_name
    FROM appointments a
    JOIN user u ON a.doctor_id = u.id
    WHERE a.patient_id = {$_SESSION['user_id']}
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    footer {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important;
            background-attachment: fixed !important;
            margin-top: auto;
        }
</style>
</head>
<body>
    <div class="container mt-5">
        <h3>My Appointments</h3>
        
        <?php if (empty($appointments)): ?>
            <div class="alert alert-info">No appointments found.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td><?= date('M j, Y h:i A', strtotime($appt['date'])) ?></td>
                            <td>Dr. <?= $appt['doctor_name'] ?></td>
                            <td><?= $appt['reason'] ?></td>
                            <td>
                                <span class="badge bg-<?= $appt['status'] == 'pending' ? 'warning' : 'success' ?>">
                                    <?= ucfirst($appt['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>