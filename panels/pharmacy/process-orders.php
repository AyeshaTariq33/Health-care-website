<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pharmacy') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_order'])) {
    $stmt = $pdo->prepare("UPDATE prescriptions SET status = 'processed' WHERE id = ?");
    $stmt->execute([$_POST['order_id']]);
    header("Location: pharmacy-dashboard.php?success=order_processed");
    exit;
}
// Get pending orders
$orders = $pdo->query("
    SELECT p.id, p.medication, p.dosage, p.instructions, 
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name
    FROM prescriptions p
    JOIN user u ON p.patient_id = u.id
    WHERE p.status = 'pending'
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Process Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h3>Process Medicine Orders</h3>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Patient</th>
                    <th>Medication</th>
                    <th>Dosage</th>
                    <th>Instructions</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['patient_name']) ?></td>
                    <td><?= htmlspecialchars($order['medication']) ?></td>
                    <td><?= htmlspecialchars($order['dosage']) ?></td>
                    <td><?= htmlspecialchars($order['instructions']) ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <button type="submit" name="process_order" class="btn btn-success">Process</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>