<?php
session_start();
require_once '../../includes/db_connect.php'; // Centralized DB connection
require_once '../../includes/auth_check.php'; // Centralized auth check

// Verify doctor role
if ($_SESSION['user_role'] !== 'doctor') {
    header("Location: /login.php");
    exit;
}

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
    <?php include '../../includes/header.php'; ?>
    <title>My Appointments | MediCare</title>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Appointments</h2>
        </div>

        <?php if (empty($appointments)): ?>
            <div class="alert alert-info">No appointments scheduled.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
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
                            <td><?= date('M j, Y g:i A', strtotime($appt['date'])) ?></td>
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
            </div>
        <?php endif; ?>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>