<?php
session_start();
// Simple authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

// Database connection (direct in file)
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Fetch appointments
$stmt = $pdo->prepare("
    SELECT a.id, a.date, a.reason, a.status, 
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name
    FROM appointments a
    JOIN user u ON a.patient_id = u.id
    WHERE a.doctor_id = ?
    ORDER BY a.date ASC
");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Appointments</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 py-3">
        <h3>My Appointments</h3>
        
        <?php if (empty($appointments)): ?>
            <div class="alert alert-info">No appointments scheduled.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appt): ?>
                    <tr>
                        <td><?= date('M j, Y h:i A', strtotime($appt['date'])) ?></td>
                        <td><?= htmlspecialchars($appt['patient_name']) ?></td>
                        <td><?= htmlspecialchars($appt['reason']) ?></td>
                        <td>
                            <span class="badge bg-<?= $appt['status'] === 'completed' ? 'success' : 'warning' ?>">
                                <?= ucfirst($appt['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($appt['status'] !== 'completed'): ?>
                            <a href="complete-appointment.php?id=<?= $appt['id'] ?>" 
                               class="btn btn-sm btn-success">
                               Complete
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>