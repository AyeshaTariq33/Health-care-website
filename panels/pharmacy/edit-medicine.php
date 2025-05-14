<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pharmacy') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get medicine details
$medicine = $pdo->query("SELECT * FROM medicines WHERE id = " . $_GET['id'])->fetch();

// Update medicine
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE medicines SET name = ?, quantity = ?, price = ? WHERE id = ?");
    $stmt->execute([$_POST['name'], $_POST['quantity'], $_POST['price'], $_POST['id']]);
    header("Location: manage-inventory.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Medicine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h3>Edit Medicine</h3>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?= $medicine['id'] ?>">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($medicine['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" value="<?= $medicine['quantity'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= $medicine['price'] ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="manage-inventory.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>