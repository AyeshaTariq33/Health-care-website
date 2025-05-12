<?php
session_name('MEDICARE_SESS');
session_start();
$base_url = "/healthcare-project/Health-care-Website";

// Simple check - Are you logged in?
if (!isset($_SESSION['user_id'])) {
    header("Location: $base_url/login.php");
    exit();
}
?>
<?php
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/database.php';

$allowed_roles = ['lab'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: /403.php");
    exit();
}

try {
    // Lab statistics
    $stats = [
        'pending_tests' => $pdo->query("SELECT COUNT(*) FROM lab_reports WHERE status = 'pending'")->fetchColumn(),
        'completed_today' => $pdo->query("SELECT COUNT(*) FROM lab_reports WHERE status = 'completed' AND DATE(report_date) = CURDATE()")->fetchColumn(),
        'equipment_issues' => $pdo->query("SELECT COUNT(*) FROM lab_equipment WHERE status != 'operational'")->fetchColumn(),
        'overdue_reports' => $pdo->query("SELECT COUNT(*) FROM lab_reports WHERE report_date < CURDATE() AND status != 'completed'")->fetchColumn()
    ];

    // Recent test requests
    $recent_tests = $pdo->query("
        SELECT lr.*, 
               p.first_name AS patient_fname,
               p.last_name AS patient_lname,
               d.first_name AS doctor_fname,
               d.last_name AS doctor_lname
        FROM lab_reports lr
        JOIN user p ON lr.patient_id = p.id
        LEFT JOIN user d ON lr.doctor_id = d.id
        ORDER BY lr.created_at DESC
        LIMIT 5
    ")->fetchAll();

    // Equipment needing attention
    $equipment_alerts = $pdo->query("
        SELECT * FROM lab_equipment
        WHERE status != 'operational'
        ORDER BY last_maintenance ASC
        LIMIT 5
    ")->fetchAll();

} catch (PDOException $e) {
    error_log("Lab Dashboard Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading lab data";
    header("Location: /500.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Lab Dashboard - MediCare+</title>
    <style>
        .lab-card {
            border-left: 4px solid #2A7B8E;
            transition: transform 0.2s;
        }
        .lab-card:hover {
            transform: translateY(-3px);
        }
        .equipment-status {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        .status-operational { background-color: #28a745; }
        .status-maintenance { background-color: #ffc107; }
        .status-failed { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="dashboard-container lab-dashboard">
        <aside class="dashboard-sidebar bg-light">
            <div class="p-4">
                <h4 class="mb-4">Laboratory Management</h4>
                <nav class="dashboard-menu">
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-flask me-2"></i>Dashboard
                    </a>
                    <a href="test-requests.php">
                        <i class="fas fa-vial me-2"></i>Test Requests
                    </a>
                    <a href="equipment.php">
                        <i class="fas fa-microscope me-2"></i>Equipment
                    </a>
                    <a href="reports.php">
                        <i class="fas fa-file-medical me-2"></i>Reports
                    </a>
                    <a href="quality-control.php">
                        <i class="fas fa-clipboard-check me-2"></i>Quality Control
                    </a>
                    <hr class="my-3">
                    <a href="settings.php" class="small">
                        <i class="fas fa-cog me-2"></i>Lab Settings
                    </a>
                </nav>
            </div>
        </aside>

        <main class="dashboard-main">
            <div class="dashboard-header bg-white shadow-sm">
                <div>
                    <h1>Laboratory Dashboard</h1>
                    <p class="text-muted">MediCare+ Diagnostic Laboratory System</p>
                </div>
                <div class="lab-status">
                    <span class="badge bg-success">Operational</span>
                    <small class="text-muted">Last updated: <?= date('g:i A') ?></small>
                </div>
            </div>

            <div class="container-fluid mt-4">
                <?php include '../../includes/alerts.php'; ?>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-clock text-warning me-2"></i>Pending Tests</h5>
                                <h2 class="mb-0"><?= $stats['pending_tests'] ?></h2>
                                <small class="text-muted"><?= $stats['overdue_reports'] ?> overdue</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-check-circle text-success me-2"></i>Completed Today</h5>
                                <h2 class="mb-0"><?= $stats['completed_today'] ?></h2>
                                <small class="text-muted">Tests processed</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-tools text-danger me-2"></i>Equipment Issues</h5>
                                <h2 class="mb-0"><?= $stats['equipment_issues'] ?></h2>
                                <small class="text-muted">Need attention</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-vial text-primary me-2"></i>Samples Processed</h5>
                                <h2 class="mb-0">-</h2>
                                <small class="text-muted">Last 24 hours</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-vial me-2"></i>Recent Test Requests</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($recent_tests)): ?>
                                    <div class="alert alert-info m-4">No recent test requests</div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($recent_tests as $test): ?>
                                        <div class="list-group-item lab-card">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?= htmlspecialchars($test['test_type']) ?>
                                                        <span class="badge bg-<?= getTestStatusColor($test['status']) ?>">
                                                            <?= htmlspecialchars(ucfirst($test['status'])) ?>
                                                        </span>
                                                    </h6>
                                                    <small class="text-muted">
                                                        Patient: <?= htmlspecialchars($test['patient_fname'] . ' ' . $test['patient_lname']) ?>
                                                        <?php if ($test['doctor_fname']): ?>
                                                            • Doctor: Dr. <?= htmlspecialchars($test['doctor_lname']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#testDetailsModal<?= $test['id'] ?>">
                                                        <i class="fas fa-info-circle me-2"></i>Details
                                                    </button>
                                                    <a href="process-test.php?id=<?= $test['id'] ?>" 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-play-circle me-2"></i>Begin
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Equipment Alerts</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($equipment_alerts)): ?>
                                    <div class="alert alert-success m-4">All equipment operational</div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($equipment_alerts as $equipment): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <span class="equipment-status status-<?= $equipment['status'] ?>"></span>
                                                        <?= htmlspecialchars($equipment['name']) ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        Last maintenance: <?= date('M j, Y', strtotime($equipment['last_maintenance'])) ?>
                                                    </small>
                                                </div>
                                                <a href="equipment.php?id=<?= $equipment['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-wrench me-2"></i>Service
                                                </a>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Test Details Modals -->
    <?php foreach ($recent_tests as $test): ?>
    <div class="modal fade" id="testDetailsModal<?= $test['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-3">Test Type</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($test['test_type']) ?></dd>

                        <dt class="col-sm-3">Patient</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($test['patient_fname'] . ' ' . $test['patient_lname']) ?></dd>

                        <?php if ($test['doctor_fname']): ?>
                        <dt class="col-sm-3">Ordering Doctor</dt>
                        <dd class="col-sm-9">Dr. <?= htmlspecialchars($test['doctor_lname']) ?></dd>
                        <?php endif; ?>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-<?= getTestStatusColor($test['status']) ?>">
                                <?= htmlspecialchars(ucfirst($test['status'])) ?>
                            </span>
                        </dd>

                        <dt class="col-sm-3">Sample Collected</dt>
                        <dd class="col-sm-9">
                            <?= $test['sample_collected'] ? date('M j, Y g:i A', strtotime($test['sample_collected'])) : 'Pending' ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>

<?php
function getTestStatusColor(string $status): string {
    return match(strtolower($status)) {
        'pending' => 'warning',
        'processing' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}