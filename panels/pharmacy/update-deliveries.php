<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pharmacy') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Update delivery status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE prescriptions SET delivery_status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    header("Location: pharmacy-dashboard.php?success=status_updated");
    exit;
}
// Get processed orders
$orders = $pdo->query("
    SELECT p.id, p.medication, p.delivery_status,
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name
    FROM prescriptions p
    JOIN user u ON p.patient_id = u.id
    WHERE p.status = 'processed'
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Deliveries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h3>Update Delivery Status</h3>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Patient</th>
                    <th>Medication</th>
                    <th>Current Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['patient_name']) ?></td>
                    <td><?= htmlspecialchars($order['medication']) ?></td>
                    <td><?= $order['delivery_status'] ?: 'Not shipped' ?></td>
                    <td>
                        <form method="POST" class="row g-2">
                            <div class="col-md-8">
                                <select name="status" class="form-control">
                                    <option value="shipped">Shipped</option>
                                    <option value="in_transit">In Transit</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>