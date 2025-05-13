<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pharmacy') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get counts for dashboard
$inventoryCount = $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE status = 'pending'")->fetchColumn();
$activeDeliveries = $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE delivery_status IS NOT NULL AND delivery_status != 'delivered'")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Pharmacy Dashboard</title>
    <style>
        .dashboard-card {
            transition: transform 0.3s;
            border-left: 4px solid;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .inventory-card {
            border-left-color: #3498db;
        }
        .orders-card {
            border-left-color: #2ecc71;
        }
        .delivery-card {
            border-left-color: #9b59b6;
        }
        .quick-stats {
            font-size: 2.5rem;
            font-weight: bold;
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
    
    <div class="container py-4 mt-5">
        <!-- Header with Success Messages -->
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
            <div>
                <h2 class="text-primary mb-1"><i class="fas fa-prescription-bottle me-2"></i>Pharmacy Dashboard</h2>
                <p class="text-muted mb-0"><?= date('l, F j, Y') ?></p>
            </div>
            <div>
                <a href="../../logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <?php
                switch($_GET['success']) {
                    case 'medicine_added': echo "<i class='fas fa-pills me-2'></i>Medicine added successfully!"; break;
                    case 'order_processed': echo "<i class='fas fa-check-circle me-2'></i>Order processed successfully!"; break;
                    case 'status_updated': echo "<i class='fas fa-truck me-2'></i>Delivery status updated!"; break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Quick Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card inventory-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-primary">
                                <i class="fas fa-pills fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Inventory</h5>
                                <p class="quick-stats text-primary"><?= $inventoryCount ?></p>
                                <small class="text-muted">Medicines in stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="manage-inventory.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-arrow-right me-2"></i>Manage
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card orders-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-success">
                                <i class="fas fa-clipboard-list fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Pending Orders</h5>
                                <p class="quick-stats text-success"><?= $pendingOrders ?></p>
                                <small class="text-muted">Require processing</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="process-orders.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-arrow-right me-2"></i>Process
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card delivery-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-purple">
                                <i class="fas fa-truck-fast fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Active Deliveries</h5>
                                <p class="quick-stats text-purple"><?= $activeDeliveries ?></p>
                                <small class="text-muted">In progress</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="update-deliveries.php" class="btn btn-outline-purple w-100">
                            <i class="fas fa-arrow-right me-2"></i>Update
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-clock-rotate-left me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php
                    $recentActivity = $pdo->query("
                        SELECT 'prescription' AS type, p.id, p.medication, 
                               CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
                               p.created_at
                        FROM prescriptions p
                        JOIN user u ON p.patient_id = u.id
                        ORDER BY p.created_at DESC
                        LIMIT 5
                    ")->fetchAll();
                    
                    foreach ($recentActivity as $activity): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">
                                <i class="fas fa-prescription me-2 text-primary"></i>
                                New <?= $activity['type'] ?> for <?= $activity['patient_name'] ?>
                            </h6>
                            <small><?= date('M j, g:i A', strtotime($activity['created_at'])) ?></small>
                        </div>
                        <p class="mb-1"><?= $activity['medication'] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>