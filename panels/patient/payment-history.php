<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: /login.php");
    exit();
}

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get payment history
$payments = $pdo->query("
    SELECT p.id, p.amount, p.payment_date, p.status, 
           GROUP_CONCAT(m.name SEPARATOR ', ') AS medicines
    FROM payments p
    JOIN medicine_orders o ON p.order_id = o.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN medicines m ON oi.medicine_id = m.id
    WHERE o.patient_id = {$_SESSION['user_id']}
    GROUP BY p.id
    ORDER BY p.payment_date DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payment-card {
            border-left: 3px solid;
            margin-bottom: 1rem;
        }
        .payment-success {
            border-left-color: #1cc88a;
        }
        .payment-pending {
            border-left-color: #f6c23e;
        }
        .payment-failed {
            border-left-color: #e74a3b;
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
            <h2><i class="fas fa-history me-2"></i>Payment History</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if (empty($payments)): ?>
            <div class="alert alert-info">
                You don't have any payment history yet.
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Medicines</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                <tr class="payment-card payment-<?= $payment['status'] ?>">
                                    <td><?= date('M j, Y', strtotime($payment['payment_date'])) ?></td>
                                    <td><?= htmlspecialchars($payment['medicines']) ?></td>
                                    <td>$<?= number_format($payment['amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $payment['status'] === 'completed' ? 'success' : 
                                            ($payment['status'] === 'pending' ? 'warning' : 'danger')
                                        ?>">
                                            <?= ucfirst($payment['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="payment-details.php?id=<?= $payment['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            Details
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