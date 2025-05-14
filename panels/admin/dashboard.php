<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get system statistics
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM user) AS total_users,
        (SELECT COUNT(*) FROM user WHERE role IN ('doctor', 'nurse', 'staff')) AS active_staff,
        (SELECT COUNT(*) FROM appointments WHERE DATE(date) = CURDATE()) AS todays_appointments,
        (SELECT COUNT(*) FROM system_alerts WHERE resolved = 0) AS system_alerts,
        (SELECT COUNT(*) FROM system_alerts WHERE priority = 'critical' AND resolved = 0) AS critical_alerts,
        (SELECT COUNT(*) FROM lab_orders WHERE DATE(created_at) = CURDATE()) AS todays_tests,
        (SELECT COUNT(*) FROM appointments WHERE DATE(date) = CURDATE() AND status = 'completed') AS completed_appointments
")->fetch();

// Get recent alerts
$alerts = $pdo->query("
    SELECT * FROM system_alerts 
    WHERE resolved = 0 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Get recent activity
$activity = $pdo->query("
    SELECT * FROM system_logs 
    ORDER BY timestamp DESC 
    LIMIT 8
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Admin Dashboard | MediCare+</title>
    <style>
        /* Consistent dashboard styling */
        body {
            padding-top: 70px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        main {
            flex: 1;
            padding-bottom: 20px;
        }
        
        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important;
            background-attachment: fixed !important;
            margin-top: auto;
        }
        
        /* Admin-specific styling */
        .admin-card {
            border-left: 4px solid #9b59b6;
            transition: all 0.3s;
        }
        
        .admin-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .critical {
            background-color: #fff5f5;
            border-left: 4px solid #e74c3c !important;
        }
        
        .data-table {
            font-size: 0.9rem;
        }
        
        .progress-thin {
            height: 5px;
        }
        
        .alert-badge {
            position: absolute;
            top: -8px;
            right: -8px;
        }
        
        .activity-item {
            border-left: 3px solid;
            padding-left: 10px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container py-4">
        <!-- Admin Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
            <div>
                <h2 class="text-primary mb-1">Admin Dashboard</h2>
                <p class="text-muted mb-0"><?= date('l, F j, Y') ?> | System Overview</p>
            </div>
            <div>
                <a href="user-management.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-users-cog me-1"></i> Manage Users
                </a>
                <a href="../../logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- System Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Total Users</h6>
                        <h2 class="text-primary"><?= number_format($stats['total_users']) ?></h2>
                        <div class="progress progress-thin mt-2">
                            <div class="progress-bar bg-success" style="width: <?= min(100, ($stats['total_users']/2000)*100) ?>%"></div>
                        </div>
                        <small class="text-muted"><?= round(($stats['total_users']/2000)*100) ?>% of system capacity</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Active Staff</h6>
                        <h2 class="text-primary"><?= $stats['active_staff'] ?></h2>
                        <div class="progress progress-thin mt-2">
                            <div class="progress-bar bg-info" style="width: <?= min(100, ($stats['active_staff']/150)*100) ?>%"></div>
                        </div>
                        <small class="text-muted"><?= round(($stats['active_staff']/150)*100) ?>% staffing level</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Today's Appointments</h6>
                        <h2 class="text-primary"><?= $stats['todays_appointments'] ?></h2>
                        <div class="progress progress-thin mt-2">
                            <div class="progress-bar bg-warning" 
                                 style="width: <?= $stats['todays_appointments'] > 0 ? ($stats['completed_appointments']/$stats['todays_appointments'])*100 : 0 ?>%">
                            </div>
                        </div>
                        <small class="text-muted">
                            <?= $stats['todays_appointments'] > 0 ? round(($stats['completed_appointments']/$stats['todays_appointments'])*100) : 0 ?>% completed
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card admin-card h-100 <?= $stats['critical_alerts'] > 0 ? 'critical' : '' ?>">
                    <div class="card-body">
                        <h6 class="text-muted">System Alerts</h6>
                        <h2 class="<?= $stats['critical_alerts'] > 0 ? 'text-danger' : 'text-primary' ?>">
                            <?= $stats['system_alerts'] ?>
                            <?php if($stats['critical_alerts'] > 0): ?>
                                <span class="badge bg-danger"><?= $stats['critical_alerts'] ?> critical</span>
                            <?php endif; ?>
                        </h2>
                        <div class="progress progress-thin mt-2">
                            <div class="progress-bar bg-danger" style="width: <?= min(100, ($stats['system_alerts']/10)*100) ?>%"></div>
                        </div>
                        <small class="<?= $stats['critical_alerts'] > 0 ? 'text-danger' : 'text-muted' ?>">
                            <?= $stats['critical_alerts'] > 0 ? 'Immediate attention needed' : 'System normal' ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Panels -->
        <div class="row g-4">
            <!-- System Alerts -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Recent Alerts</h5>
                        <span class="badge bg-danger rounded-pill"><?= count($alerts) ?> active</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if(empty($alerts)): ?>
                            <div class="alert alert-success m-3">
                                <i class="fas fa-check-circle me-2"></i> No active alerts
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach($alerts as $alert): ?>
                                <a href="#" class="list-group-item list-group-item-action <?= $alert['priority'] === 'critical' ? 'critical' : '' ?>">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($alert['title']) ?></h6>
                                            <small><?= htmlspecialchars($alert['description']) ?></small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted"><?= date('h:i A', strtotime($alert['created_at'])) ?></small>
                                            <br>
                                            <span class="badge bg-<?= $alert['priority'] === 'critical' ? 'danger' : 'warning' ?>">
                                                <?= ucfirst($alert['priority']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Activity -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach($activity as $log): ?>
                            <div class="activity-item mb-3" style="border-left-color: <?= 
                                $log['action_type'] === 'login' ? '#4e73df' : (
                                $log['action_type'] === 'error' ? '#e74a3b' : '#1cc88a'
                                ) ?>;">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($log['description']) ?></strong>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars($log['user_id'] ? "User #{$log['user_id']}" : 'System') ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><?= date('h:i A', strtotime($log['timestamp'])) ?></small>
                                        <br>
                                        <span class="badge bg-<?= 
                                            $log['action_type'] === 'login' ? 'primary' : (
                                            $log['action_type'] === 'error' ? 'danger' : 'success'
                                            ) ?>">
                                            <?= ucfirst($log['action_type']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Admin Tools -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-tools text-warning me-2"></i>Admin Tools</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-2 col-6">
                                <button class="btn btn-outline-danger w-100 text-start" data-bs-toggle="modal" data-bs-target="#emergencyModal">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Emergency
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Emergency Modal -->
    <div class="modal fade" id="emergencyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Emergency Mode</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Warning!</strong> This will restrict system access to administrators only.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Emergency Reason</label>
                        <select class="form-select">
                            <option>System Maintenance</option>
                            <option>Security Incident</option>
                            <option>Data Breach</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (hours)</label>
                        <input type="number" class="form-control" value="2" min="1" max="24">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Activate Emergency Mode</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>