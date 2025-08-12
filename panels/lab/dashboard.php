<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM lab_orders WHERE status = 'pending') AS pending_tests,
        (SELECT COUNT(*) FROM lab_orders WHERE status = 'processing') AS processing_tests,
        (SELECT COUNT(*) FROM lab_reports WHERE DATE(report_date) = CURDATE()) AS completed_today,
        (SELECT COUNT(*) FROM lab_reports WHERE critical_flag = 1 AND DATE(report_date) = CURDATE()) AS critical_results,
        (SELECT COUNT(*) FROM sample_collections WHERE DATE(scheduled_date) = CURDATE()) AS scheduled_collections,
        (SELECT COUNT(*) FROM lab_requests WHERE priority = 'urgent' AND DATE(created_at) = CURDATE()) AS urgent_tests
")->fetch();

$recentOrders = $pdo->query("
    SELECT lo.id, ltt.name AS test_name, 
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
           lo.status
    FROM lab_orders lo
    JOIN lab_test_types ltt ON lo.test_type_id = ltt.id
    JOIN user u ON lo.patient_id = u.id
    ORDER BY lo.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Dashboard | MediCare+</title>
    <!-- CDN Links Only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --card-1: #4e73df;
            --card-2: #1cc88a;
            --card-3: #36b9cc;
            --card-4: #e74a3b;
            --card-5: #9b59b6;
            --card-6: #f6c23e;
        }
        .dashboard-card {
            border-left: 4px solid;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .card-1 { border-left-color: var(--card-1); }
        .card-2 { border-left-color: var(--card-2); }
        .card-3 { border-left-color: var(--card-3); }
        .card-4 { border-left-color: var(--card-4); }
        .card-5 { border-left-color: var(--card-5); }
        .card-6 { border-left-color: var(--card-6); }
        .card-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        .quick-action-btn {
            transition: all 0.2s;
            border-width: 2px;
        }
        .badge-status {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        .bg-processing { background-color: var(--card-2); }
        .bg-pending { background-color: var(--card-6); }
        .bg-completed { background-color: var(--card-3); }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-gray-800">
                    <i class="fas fa-flask text-primary me-2"></i>Lab Dashboard
                </h1>
                <p class="mb-0 text-muted"><?= date('l, F j, Y') ?></p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <!-- Card 1: Pending Tests -->
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card card-1 h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fas fa-clock card-icon text-primary"></i>
                            </div>
                            <div class="col-9">
                                <h5 class="card-title text-uppercase text-muted mb-0">Pending Tests</h5>
                                <p class="h2 font-weight-bold mb-0"><?= $stats['pending_tests'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent py-2">
                        <a href="manage-orders.php" class="btn btn-outline-primary w-100 quick-action-btn">
                            Manage <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card card-2 h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fas fa-vial card-icon text-success"></i>
                            </div>
                            <div class="col-9">
                                <h5 class="card-title text-uppercase text-muted mb-0">Processing</h5>
                                <p class="h2 font-weight-bold mb-0"><?= $stats['processing_tests'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent py-2">
                        <a href="upload-results.php" class="btn btn-outline-success w-100 quick-action-btn">
                            Upload Results <i class="fas fa-upload ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 3: Completed Today -->
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card card-3 h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fas fa-check-circle card-icon text-info"></i>
                            </div>
                            <div class="col-9">
                                <h5 class="card-title text-uppercase text-muted mb-0">Completed Today</h5>
                                <p class="h2 font-weight-bold mb-0"><?= $stats['completed_today'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent py-2">
                        <a href="upload-results.php?filter=completed" class="btn btn-outline-info w-100 quick-action-btn">
                            View All <i class="fas fa-list ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card card-4 h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fas fa-exclamation-triangle card-icon text-danger"></i>
                            </div>
                            <div class="col-9">
                                <h5 class="card-title text-uppercase text-muted mb-0">Critical Results</h5>
                                <p class="h2 font-weight-bold mb-0"><?= $stats['critical_results'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent py-2">
                        <a href="#" class="btn btn-outline-danger w-100 quick-action-btn">
                            Review <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 5: Collections Today -->
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card card-5 h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fas fa-syringe card-icon text-purple"></i>
                            </div>
                            <div class="col-9">
                                <h5 class="card-title text-uppercase text-muted mb-0">Collections Today</h5>
                                <p class="h2 font-weight-bold mb-0"><?= $stats['scheduled_collections'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent py-2">
                        <a href="schedule-collections.php" class="btn btn-outline-purple w-100 quick-action-btn">
                            Schedule <i class="fas fa-calendar-alt ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card card-6 h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fas fa-bell card-icon text-warning"></i>
                            </div>
                            <div class="col-9">
                                <h5 class="card-title text-uppercase text-muted mb-0">Urgent Tests</h5>
                                <p class="h2 font-weight-bold mb-0"><?= $stats['urgent_tests'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent py-2">
                        <a href="manage-orders.php?priority=urgent" class="btn btn-outline-warning w-100 quick-action-btn">
                            Process <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-list-alt text-primary me-2"></i>Recent Test Orders
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Patient</th>
                                <th>Test</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#L-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($order['patient_name']) ?></td>
                                <td><?= htmlspecialchars($order['test_name']) ?></td>
                                <td>
                                    <?php if ($order['status'] === 'processing'): ?>
                                        <span class="badge bg-processing badge-status">Processing</span>
                                    <?php else: ?>
                                        <span class="badge bg-pending badge-status">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <a href="manage-orders.php?process=<?= $order['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary quick-action-btn">
                                            <i class="fas fa-edit me-1"></i> Process
                                        </a>
                                    <?php else: ?>
                                        <a href="upload-results.php?order_id=<?= $order['id'] ?>" 
                                           class="btn btn-sm btn-outline-success quick-action-btn">
                                            <i class="fas fa-upload me-1"></i> Upload
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-2">
                <a href="manage-orders.php" class="btn btn-link float-end">
                    View All Orders <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>