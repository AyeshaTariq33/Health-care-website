<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pharmacy') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Add new medicine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_medicine'])) {
    $stmt = $pdo->prepare("INSERT INTO medicines (name, quantity, price) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['quantity'], $_POST['price']]);
    header("Location: pharmacy-dashboard.php?success=medicine_added");
    exit;
}

// Get all medicines
$medicines = $pdo->query("SELECT * FROM medicines")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h3>Manage Inventory</h3>
        
        <!-- Add Medicine Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Add New Medicine</h5>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control" placeholder="Medicine Name" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="quantity" class="form-control" placeholder="Quantity" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_medicine" class="btn btn-primary">Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Medicine List -->
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($medicines as $medicine): ?>
                <tr>
                    <td><?= $medicine['id'] ?></td>
                    <td><?= htmlspecialchars($medicine['name']) ?></td>
                    <td><?= $medicine['quantity'] ?></td>
                    <td>$<?= number_format($medicine['price'], 2) ?></td>
                    <td>
                        <a href="edit-medicine.php?id=<?= $medicine['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>