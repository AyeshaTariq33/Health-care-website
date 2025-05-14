<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get pending lab orders with basic info
$orders = $pdo->query("
    SELECT lo.id, 
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
           ltt.name AS test_name,
           lo.status,
           (SELECT priority FROM lab_requests lr 
            WHERE lr.patient_id = lo.patient_id 
            AND lr.test_type_id = lo.test_type_id 
            LIMIT 1) AS priority
    FROM lab_orders lo
    JOIN user u ON lo.patient_id = u.id
    JOIN lab_test_types ltt ON lo.test_type_id = ltt.id
    WHERE lo.status IN ('pending', 'processing')
    ORDER BY 
        CASE WHEN priority = 'urgent' THEN 0 ELSE 1 END,
        lo.created_at DESC
")->fetchAll();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE lab_orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);
    
    if ($status === 'processing') {
        $pdo->query("INSERT INTO lab_reports (order_id, status) VALUES ($orderId, 'pending')");
    }
    
    header("Location: manage-orders.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Test Orders | MediCare+ Lab Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .urgent-row {
            background-color: #fff0f0;
            border-left: 4px solid #e74a3b;
        }
        .priority-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4em 0.7em;
        }
        .action-btn {
            transition: all 0.2s;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">
                <i class="fas fa-vial me-2"></i>Manage Test Orders
            </h1>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                Order status updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Current Test Orders</h5>
                    <div class="btn-group">
                        <a href="?filter=pending" class="btn btn-sm btn-outline-primary">Pending</a>
                        <a href="?filter=processing" class="btn btn-sm btn-outline-info">Processing</a>
                        <a href="?" class="btn btn-sm btn-outline-secondary">All</a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Patient</th>
                                <th>Test</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr class="<?= $order['priority'] === 'urgent' ? 'urgent-row' : '' ?>">
                                <td>#L-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($order['patient_name']) ?></td>
                                <td><?= htmlspecialchars($order['test_name']) ?></td>
                                <td>
                                    <?php if ($order['priority'] === 'urgent'): ?>
                                        <span class="badge bg-danger priority-badge">Urgent</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary priority-badge">Routine</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($order['status'] === 'processing'): ?>
                                        <span class="badge bg-info status-badge">Processing</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning status-badge">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button type="submit" name="status" value="processing" 
                                                    class="btn btn-sm btn-success action-btn me-1">
                                                <i class="fas fa-check me-1"></i> Accept
                                            </button>
                                            <button type="submit" name="status" value="rejected" 
                                                    class="btn btn-sm btn-danger action-btn">
                                                <i class="fas fa-times me-1"></i> Reject
                                            </button>
                                        <?php else: ?>
                                            <a href="upload-results.php?order_id=<?= $order['id'] ?>" 
                                               class="btn btn-sm btn-primary action-btn">
                                                <i class="fas fa-upload me-1"></i> Upload Results
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted">Showing <?= count($orders) ?> orders</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>