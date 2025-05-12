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

$allowed_roles = ['pharmacy'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: /403.php");
    exit();
}

try {
    // Get pharmacy statistics
    $stats = [
        'pending_orders' => $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE status = 'pending'")->fetchColumn(),
        'today_orders' => $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
        'low_stock' => $pdo->query("SELECT COUNT(*) FROM inventory WHERE quantity < min_stock")->fetchColumn(),
        'total_inventory' => $pdo->query("SELECT SUM(quantity) FROM inventory")->fetchColumn()
    ];

    // Get pending prescriptions
    $prescriptions = $pdo->query("
        SELECT p.*, 
               pt.first_name AS patient_fname,
               pt.last_name AS patient_lname,
               d.first_name AS doctor_fname,
               d.last_name AS doctor_lname
        FROM prescriptions p
        JOIN user pt ON p.patient_id = pt.id
        LEFT JOIN user d ON p.doctor_id = d.id
        WHERE p.status = 'pending'
        ORDER BY p.created_at DESC
        LIMIT 10
    ")->fetchAll();

    // Get inventory alerts
    $inventory_alerts = $pdo->query("
        SELECT * FROM inventory 
        WHERE quantity < min_stock
        ORDER BY quantity ASC
        LIMIT 5
    ")->fetchAll();

} catch (PDOException $e) {
    error_log("Pharmacy Dashboard Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading dashboard data";
    header("Location: /500.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Pharmacy Dashboard - MediCare+</title>
    <style>
        .pharmacy-card {
            border-left: 4px solid #2A7B8E;
            transition: transform 0.2s;
        }
        .pharmacy-card:hover {
            transform: translateY(-3px);
        }
        .inventory-alert {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="dashboard-container pharmacy-dashboard">
        <aside class="dashboard-sidebar bg-light">
            <div class="p-4">
                <h4 class="mb-4">Pharmacy Management</h4>
                <nav class="dashboard-menu">
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="orders.php">
                        <i class="fas fa-prescription me-2"></i>Prescription Orders
                    </a>
                    <a href="inventory.php">
                        <i class="fas fa-capsules me-2"></i>Inventory
                    </a>
                    <a href="patients.php">
                        <i class="fas fa-user-injured me-2"></i>Patients
                    </a>
                    <a href="reports.php">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                    <hr class="my-3">
                    <a href="settings.php" class="small">
                        <i class="fas fa-cog me-2"></i>Pharmacy Settings
                    </a>
                </nav>
            </div>
        </aside>

        <main class="dashboard-main">
            <div class="dashboard-header bg-white shadow-sm">
                <div>
                    <h1>Pharmacy Dashboard</h1>
                    <p class="text-muted">MediCare+ Pharmacy Management System</p>
                </div>
                <div class="pharmacy-status">
                    <span class="badge bg-success">Online</span>
                    <small class="text-muted">Last sync: <?= date('g:i A') ?></small>
                </div>
            </div>

            <div class="container-fluid mt-4">
                <?php include '../../includes/alerts.php'; ?>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-clock text-warning me-2"></i>Pending Orders</h5>
                                <h2 class="mb-0"><?= $stats['pending_orders'] ?></h2>
                                <small class="text-muted"><?= $stats['today_orders'] ?> new today</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-exclamation-triangle text-danger me-2"></i>Low Stock</h5>
                                <h2 class="mb-0"><?= $stats['low_stock'] ?></h2>
                                <small class="text-muted">Items need restocking</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-cubes text-info me-2"></i>Total Inventory</h5>
                                <h2 class="mb-0"><?= number_format($stats['total_inventory']) ?></h2>
                                <small class="text-muted">Items in stock</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-check-circle text-success me-2"></i>Ready for Pickup</h5>
                                <h2 class="mb-0">-</h2>
                                <small class="text-muted">Completed orders</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-prescription me-2"></i>Pending Prescriptions</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($prescriptions)): ?>
                                    <div class="alert alert-info m-4">No pending prescriptions</div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($prescriptions as $rx): ?>
                                        <div class="list-group-item pharmacy-card">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?= htmlspecialchars($rx['medication_name']) ?> 
                                                        <small class="text-muted">(<?= htmlspecialchars($rx['dosage']) ?>)</small>
                                                    </h6>
                                                    <small class="text-muted">
                                                        Patient: <?= htmlspecialchars($rx['patient_fname'] . ' ' . $rx['patient_lname']) ?> •
                                                        Doctor: Dr. <?= htmlspecialchars($rx['doctor_lname']) ?>
                                                    </small>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#processRxModal<?= $rx['id'] ?>">
                                                        <i class="fas fa-check me-2"></i>Process
                                                    </button>
                                                    <a href="patient-profile.php?id=<?= $rx['patient_id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-user me-2"></i>Profile
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
                                <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Inventory Alerts</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($inventory_alerts)): ?>
                                    <div class="alert alert-success m-4">All inventory items are stocked</div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($inventory_alerts as $item): ?>
                                        <div class="list-group-item inventory-alert">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                    <small class="text-muted">
                                                        Current Stock: <?= $item['quantity'] ?> 
                                                        (Min: <?= $item['min_stock'] ?>)
                                                    </small>
                                                </div>
                                                <a href="inventory.php#item-<?= $item['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-plus me-2"></i>Reorder
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

    <?php include '../../includes/footer.php'; ?>
</body>
</html>