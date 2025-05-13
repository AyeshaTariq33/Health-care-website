<?php
session_start();

// Strict authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: /login.php");
    exit();
}

// Database connection
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=healthcare-db',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Fetch prescriptions with prepared statement
    $stmt = $pdo->prepare("
        SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) AS doctor_name 
        FROM prescriptions p
        JOIN user u ON p.doctor_id = u.id
        WHERE p.patient_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $prescriptions = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .prescription-card {
            border-left: 4px solid #4e73df;
            transition: all 0.3s;
        }
        .prescription-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .badge-status {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }
        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important;
            background-attachment: fixed !important;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-prescription me-2"></i>My Prescriptions</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if (empty($prescriptions)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No prescriptions found.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Doctor</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Instructions</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptions as $prescription): ?>
                        <tr class="prescription-card">
                            <td><?= htmlspecialchars(date('M j, Y', strtotime($prescription['created_at']))) ?></td>
                            <td>Dr. <?= htmlspecialchars($prescription['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($prescription['medication']) ?></td>
                            <td><?= htmlspecialchars($prescription['dosage']) ?></td>
                            <td><?= htmlspecialchars($prescription['instructions']) ?></td>
                            <td>
                                <span class="badge bg-<?= $prescription['status'] === 'filled' ? 'success' : 'warning' ?> badge-status">
                                    <?= ucfirst($prescription['status'] ?? 'pending') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <!-- Bootstrap & Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>