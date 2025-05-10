<?php
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/database.php';

$allowed_roles = ['patient'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: /403.php");
    exit();
}

try {
    // Get patient lab reports
    $stmt = $pdo->prepare("
        SELECT lr.*, 
               lb.name AS lab_name,
               lb.address AS lab_address,
               d.first_name AS doctor_fname,
               d.last_name AS doctor_lname
        FROM lab_reports lr
        LEFT JOIN labs lb ON lr.lab_id = lb.id
        LEFT JOIN user d ON lr.doctor_id = d.id
        WHERE lr.patient_id = ?
        ORDER BY lr.report_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $reports = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Lab Reports Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading lab reports";
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Lab Reports - MediCare+</title>
    <style>
        .report-card {
            border-left: 4px solid #2A7B8E;
            transition: transform 0.2s;
        }
        .report-card:hover {
            transform: translateY(-3px);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.25rem 0.75rem;
        }
        .lab-icon {
            font-size: 2rem;
            color: #2A7B8E;
        }
    </style>
</head>
<body>
    <div class="patient-dashboard">
        <?php include '../../includes/navbar.php'; ?>

        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>Lab Reports</h1>
                <?php if (!empty($reports)): ?>
                    <small class="text-muted">
                        Last report: <?= date('M j, Y', strtotime($reports[0]['report_date'])) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="container-fluid mt-4">
                <?php include '../../includes/alerts.php'; ?>

                <?php if (empty($reports)): ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                            <h3 class="text-muted">No Lab Reports Available</h3>
                            <p class="text-muted">Your lab results will appear here once ready</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($reports as $report): ?>
                        <div class="col-12">
                            <div class="card shadow-sm report-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-vial lab-icon me-3"></i>
                                                <div>
                                                    <h4 class="mb-0">
                                                        <?= htmlspecialchars($report['test_type']) ?>
                                                        <span class="badge bg-<?= getReportStatusColor($report['status']) ?> status-badge">
                                                            <?= htmlspecialchars(ucfirst($report['status'])) ?>
                                                        </span>
                                                    </h4>
                                                    <small class="text-muted">
                                                        <?php if ($report['lab_name']): ?>
                                                            <?= htmlspecialchars($report['lab_name']) ?> •
                                                        <?php endif; ?>
                                                        <?= date('M j, Y', strtotime($report['report_date'])) ?>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <?php if ($report['doctor_fname']): ?>
                                                        <p class="mb-1">
                                                            <strong>Ordering Doctor:</strong>
                                                            Dr. <?= htmlspecialchars($report['doctor_lname']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <p class="mb-1">
                                                        <strong>Test Type:</strong>
                                                        <?= htmlspecialchars($report['test_type']) ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if ($report['lab_name']): ?>
                                                        <p class="mb-1">
                                                            <strong>Lab:</strong>
                                                            <?= htmlspecialchars($report['lab_name']) ?>
                                                        </p>
                                                        <p class="mb-1 text-muted small">
                                                            <?= htmlspecialchars($report['lab_address']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <?php if ($report['status'] === 'completed'): ?>
                                                <a href="download-report.php?id=<?= $report['id'] ?>" 
                                                   class="btn btn-outline-primary mb-2"
                                                   target="_blank">
                                                    <i class="fas fa-download me-2"></i>PDF
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#detailsModal<?= $report['id'] ?>">
                                                <i class="fas fa-info-circle me-2"></i>Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Report Details Modal -->
                        <div class="modal fade" id="detailsModal<?= $report['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Lab Report Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <dl class="row">
                                            <dt class="col-sm-3">Test Type</dt>
                                            <dd class="col-sm-9"><?= htmlspecialchars($report['test_type']) ?></dd>

                                            <dt class="col-sm-3">Report Date</dt>
                                            <dd class="col-sm-9"><?= date('M j, Y', strtotime($report['report_date'])) ?></dd>

                                            <?php if ($report['doctor_fname']): ?>
                                            <dt class="col-sm-3">Ordered By</dt>
                                            <dd class="col-sm-9">
                                                Dr. <?= htmlspecialchars($report['doctor_lname']) ?>
                                            </dd>
                                            <?php endif; ?>

                                            <?php if ($report['lab_name']): ?>
                                            <dt class="col-sm-3">Testing Facility</dt>
                                            <dd class="col-sm-9">
                                                <?= htmlspecialchars($report['lab_name']) ?><br>
                                                <small class="text-muted"><?= htmlspecialchars($report['lab_address']) ?></small>
                                            </dd>
                                            <?php endif; ?>

                                            <?php if ($report['results']): ?>
                                            <dt class="col-sm-3">Results Summary</dt>
                                            <dd class="col-sm-9"><?= htmlspecialchars($report['results']) ?></dd>
                                            <?php endif; ?>

                                            <?php if ($report['interpretation']): ?>
                                            <dt class="col-sm-3">Doctor's Interpretation</dt>
                                            <dd class="col-sm-9"><?= htmlspecialchars($report['interpretation']) ?></dd>
                                            <?php endif; ?>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>

<?php
function getReportStatusColor(string $status): string {
    return match(strtolower($status)) {
        'completed' => 'success',
        'pending' => 'warning',
        'in progress' => 'primary',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}