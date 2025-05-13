<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get lab reports for the patient
$labReports = $pdo->query("
    SELECT lr.id, lr.test_name, lr.result, lr.status, lr.report_date, 
           lo.test_type, lo.order_date,
           CONCAT(u.first_name, ' ', u.last_name) AS doctor_name
    FROM lab_reports lr
    JOIN lab_orders lo ON lr.order_id = lo.id
    LEFT JOIN user u ON lo.doctor_id = u.id
    WHERE lo.patient_id = {$_SESSION['user_id']}
    ORDER BY lr.report_date DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>My Lab Reports</title>
    <style>
        .report-card {
            border-left: 4px solid #36b9cc;
            transition: all 0.2s;
            margin-bottom: 15px;
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .badge-status {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-medical me-2"></i>My Lab Reports</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if (empty($labReports)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>You don't have any lab reports yet.
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Report Date</th>
                                    <th>Test Name</th>
                                    <th>Ordered By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($labReports as $report): ?>
                                <tr class="report-card">
                                    <td><?= date('M j, Y', strtotime($report['report_date'])) ?></td>
                                    <td><?= htmlspecialchars($report['test_name']) ?></td>
                                    <td>
                                        <?php if ($report['doctor_name']): ?>
                                            Dr. <?= htmlspecialchars($report['doctor_name']) ?>
                                        <?php else: ?>
                                            Self-requested
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $report['status'] === 'completed' ? 'success' : 
                                            ($report['status'] === 'pending' ? 'warning' : 'secondary')
                                        ?> badge-status">
                                            <?= ucfirst($report['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view-report.php?id=<?= $report['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>