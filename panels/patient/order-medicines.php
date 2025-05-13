<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: /login.php");
    exit();
}

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$error = null;
$prescriptions = [];

try {
    // Check if medicines table has stock column
    $hasStockColumn = $pdo->query("
        SELECT COUNT(*) FROM information_schema.columns 
        WHERE table_schema = 'healthcare-db' 
        AND table_name = 'medicines' 
        AND column_name = 'stock'
    ")->fetchColumn();

    if ($hasStockColumn) {
        // Get patient's active prescriptions with medicines
        $prescriptions = $pdo->query("
            SELECT p.id, p.medication, p.dosage, p.instructions, 
                   m.id AS medicine_id, m.name, m.price, m.stock
            FROM prescriptions p
            LEFT JOIN medicines m ON p.medication LIKE CONCAT('%', m.name, '%')
            WHERE p.patient_id = {$_SESSION['user_id']}
            AND p.status = 'active'
        ")->fetchAll();
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
        $pdo->beginTransaction();
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO medicine_orders 
            (patient_id, prescription_id, total_amount, status) 
            VALUES (?, ?, ?, 'pending')
        ");
        $totalAmount = array_reduce($_POST['medicines'], function($sum, $qty) use ($pdo) {
            return $sum + ($qty * $pdo->query("SELECT price FROM medicines WHERE id = " . key($qty))->fetchColumn());
        }, 0);
        
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['prescription_id'] ?? null,
            $totalAmount
        ]);
        $orderId = $pdo->lastInsertId();
        
        // Add order items
        foreach ($_POST['medicines'] as $medicineId => $qty) {
            if ($qty > 0) {
                // Get current medicine price
                $price = $pdo->query("SELECT price FROM medicines WHERE id = $medicineId")->fetchColumn();
                
                $stmt = $pdo->prepare("
                    INSERT INTO order_items 
                    (order_id, medicine_id, quantity, price_at_order) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$orderId, $medicineId, $qty, $price]);
                
                // Update stock
                $pdo->exec("UPDATE medicines SET stock = stock - $qty WHERE id = $medicineId");
            }
        }
        
        $pdo->commit();
        header("Location: payment.php?order_id=$orderId");
        exit();
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = "Error processing your order. Please try again later.";
    error_log("Order Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Medicines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .medicine-card {
            border-left: 3px solid #4e73df;
            margin-bottom: 1rem;
        }
        .quantity-control {
            width: 80px;
        }
        .stock-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .out-of-stock {
            color: #dc3545;
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
    
    <div class="container mt-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-pills me-2"></i>Order Medicines</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!$hasStockColumn): ?>
            <div class="alert alert-warning">
                The medicine ordering system is currently being set up. Please check back later.
            </div>
        <?php else: ?>
            <form method="POST" id="orderForm">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Available Medicines from Your Prescriptions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($prescriptions)): ?>
                            <div class="alert alert-info">
                                You don't have any active prescriptions with available medicines.
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($prescriptions as $prescription): ?>
                                    <?php if ($prescription['medicine_id']): ?>
                                    <div class="col-md-6">
                                        <div class="card medicine-card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h5><?= htmlspecialchars($prescription['name']) ?></h5>
                                                        <p class="text-muted mb-1">
                                                            <strong>Prescribed:</strong> <?= htmlspecialchars($prescription['medication']) ?>
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <strong>Dosage:</strong> <?= htmlspecialchars($prescription['dosage']) ?>
                                                        </p>
                                                        <p class="text-success mb-1">
                                                            <strong>Price:</strong> $<?= number_format($prescription['price'], 2) ?>
                                                        </p>
                                                        <p class="stock-info <?= $prescription['stock'] <= 0 ? 'out-of-stock' : '' ?>">
                                                            <strong>Stock:</strong> 
                                                            <?= $prescription['stock'] > 0 ? $prescription['stock'] . ' available' : 'Out of stock' ?>
                                                        </p>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <input type="number" 
                                                               name="medicines[<?= $prescription['medicine_id'] ?>]" 
                                                               class="form-control quantity-control" 
                                                               min="0" max="<?= $prescription['stock'] ?>" 
                                                               value="0" <?= $prescription['stock'] <= 0 ? 'disabled' : '' ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($prescriptions)): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Order Summary</h5>
                            <button type="submit" name="place_order" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-2"></i> Proceed to Payment
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const quantities = document.querySelectorAll('input[type="number"]:not(:disabled)');
            let hasSelection = false;
            
            quantities.forEach(input => {
                if (parseInt(input.value) > 0) hasSelection = true;
            });
            
            if (!hasSelection) {
                e.preventDefault();
                alert('Please select at least one medicine to order');
            }
        });
    </script>
</body>
</html>