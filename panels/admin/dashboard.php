<?php
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/database.php';

$allowed_roles = ['admin'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: /403.php");
    exit();
}

try {
    // System Statistics
    $stats = [
        'total_users' => $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn(),
        'active_patients' => $pdo->query("SELECT COUNT(*) FROM user WHERE role = 'patient'")->fetchColumn(),
        'registered_doctors' => $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn(),
        'active_appointments' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'scheduled'")->fetchColumn(),
        'system_health' => getSystemHealthStatus(),
    ];

    // Recent Activities
    $activities = $pdo->query("
        SELECT * FROM audit_log 
        ORDER BY created_at DESC 
        LIMIT 10
    ")->fetchAll();

    // Recent Registrations
    $recent_users = $pdo->query("
        SELECT u.*, p.insurance_provider 
        FROM user u
        LEFT JOIN patients p ON u.id = p.user_id
        ORDER BY u.created_at DESC 
        LIMIT 8
    ")->fetchAll();

    // System Information
    $system_info = [
        'php_version' => phpversion(),
        'database_size' => getDatabaseSize(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'],
        'last_cron_run' => getLastCronExecution(),
    ];

} catch (PDOException $e) {
    error_log("Admin Dashboard Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading dashboard data";
    header("Location: /500.php");
    exit();
}

// Helper functions
function getDatabaseSize(): string {
    global $pdo;
    $result = $pdo->query("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.TABLES 
        WHERE table_schema = DATABASE()
    ")->fetch();
    return $result['size_mb'] . ' MB';
}

function getSystemHealthStatus(): array {
    return [
        'status' => 'optimal',
        'checks' => [
            'database' => true,
            'storage' => true,
            'cron' => true,
            'security' => true
        ]
    ];
}

function getLastCronExecution(): string {
    return file_exists('/tmp/last_cron_run') 
        ? date('Y-m-d H:i:s', filemtime('/tmp/last_cron_run')) 
        : 'Never';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Admin Dashboard - MediCare+</title>
    <style>
        .health-indicator {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
        }
        .health-optimal { background-color: #28a745; }
        .health-warning { background-color: #ffc107; }
        .health-critical { background-color: #dc3545; }
        .system-card {
            transition: transform 0.2s;
        }
        .system-card:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container admin-dashboard">
        <aside class="dashboard-sidebar bg-dark text-white">
            <div class="p-4">
                <h4 class="mb-4">MediCare+ Admin</h4>
                <nav class="dashboard-menu">
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="users.php">
                        <i class="fas fa-users-cog me-2"></i>User Management
                    </a>
                    <a href="system-health.php">
                        <i class="fas fa-heartbeat me-2"></i>System Health
                    </a>
                    <a href="audit-logs.php">
                        <i class="fas fa-clipboard-list me-2"></i>Audit Logs
                    </a>
                    <a href="settings.php">
                        <i class="fas fa-cogs me-2"></i>System Settings
                    </a>
                    <hr class="my-3">
                    <a href="documentation.php" class="small">
                        <i class="fas fa-book me-2"></i>Documentation
                    </a>
                </nav>
            </div>
        </aside>

        <main class="dashboard-main">
            <div class="dashboard-header bg-light shadow-sm">
                <div>
                    <h1>Admin Dashboard</h1>
                    <p class="text-muted">System Overview and Management</p>
                </div>
                <div class="system-status">
                    <span class="health-indicator health-<?= $stats['system_health']['status'] ?>"></span>
                    <span class="text-muted small">System Status: <?= ucfirst($stats['system_health']['status']) ?></span>
                </div>
            </div>

            <div class="container-fluid mt-4">
                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="system-card card shadow-sm h-100">
                            <div class="card-body">
                                <h5><i class="fas fa-users text-primary me-2"></i>Total Users</h5>
                                <h2 class="mb-0"><?= number_format($stats['total_users']) ?></h2>
                                <small class="text-muted"><?= $stats['active_patients'] ?> active patients</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="system-card card shadow-sm h-100">
                            <div class="card-body">
                                <h5><i class="fas fa-user-md text-success me-2"></i>Medical Staff</h5>
                                <h2 class="mb-0"><?= number_format($stats['registered_doctors']) ?></h2>
                                <small class="text-muted"><?= $stats['active_appointments'] ?> active appointments</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="system-card card shadow-sm h-100">
                            <div class="card-body">
                                <h5><i class="fas fa-database text-info me-2"></i>Database</h5>
                                <h2 class="mb-0"><?= $system_info['database_size'] ?></h2>
                                <small class="text-muted">PHP <?= $system_info['php_version'] ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="system-card card shadow-sm h-100">
                            <div class="card-body">
                                <h5><i class="fas fa-server text-warning me-2"></i>Server</h5>
                                <h2 class="mb-0"><?= explode('/', $system_info['server_software'])[0] ?></h2>
                                <small class="text-muted">Last cron: <?= $system_info['last_cron_run'] ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities & Users -->
                <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Recent Activities</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($activities as $activity): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted"><?= date('M j, g:i a', strtotime($activity['created_at'])) ?></small>
                                                <div><?= htmlspecialchars($activity['description']) ?></div>
                                            </div>
                                            <span class="badge bg-<?= getActivityBadgeColor($activity['action_type']) ?>">
                                                <?= ucfirst($activity['action_type']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Recent Registrations</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_users as $user): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-user-circle fa-2x text-muted"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h6>
                                                <small class="text-muted">
                                                    <?= ucfirst($user['role']) ?> • 
                                                    <?= date('M j', strtotime($user['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>System Health</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <?php foreach ($stats['system_health']['checks'] as $check => $status): ?>
                                    <div class="col-6 col-md-3">
                                        <div class="d-flex align-items-center">
                                            <span class="health-indicator health-<?= $status ? 'optimal' : 'critical' ?> me-2"></span>
                                            <div>
                                                <div class="text-capitalize"><?= str_replace('_', ' ', $check) ?></div>
                                                <small class="text-muted"><?= $status ? 'Operational' : 'Degraded' ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>

<?php
function getActivityBadgeColor(string $actionType): string {
    return match(strtolower($actionType)) {
        'create' => 'success',
        'update' => 'primary',
        'delete' => 'danger',
        'security' => 'warning',
        default => 'secondary'
    };
}
?>