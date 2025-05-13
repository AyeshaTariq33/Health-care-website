<?php
session_start();

// Strict authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: /login.php");
    exit();
}

// Database connection
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=healthcare-db',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("
            INSERT INTO lab_orders 
            (patient_id, test_type, preferred_date, notes) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['test_type'],
            $_POST['preferred_date'],
            $_POST['notes'] ?? null
        ]);
        
        header("Location: dashboard.php?success=test_booked");
        exit();
    }
    
    // Get available test types
    $testTypes = $pdo->query("SELECT id, name, description FROM lab_test_types")->fetchAll();
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Lab Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
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
            <h2><i class="fas fa-flask me-2"></i>Book Lab Test</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" id="labTestForm">
                    <!-- Test Type Selection -->
                    <div class="mb-4">
                        <h5 class="mb-3">1. Select Test Type</h5>
                        <select name="test_type" class="form-select" required>
                            <option value="">-- Select a test type --</option>
                            <?php foreach ($testTypes as $test): ?>
                                <option value="<?= $test['id'] ?>">
                                    <?= htmlspecialchars($test['name']) ?> - <?= htmlspecialchars($test['description']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Date Selection -->
                    <div class="mb-4">
                        <h5 class="mb-3">2. Preferred Date</h5>
                        <input type="date" name="preferred_date" class="form-control" 
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <!-- Additional Notes -->
                    <div class="mb-4">
                        <h5 class="mb-3">3. Additional Notes</h5>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Any special instructions..."></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-check me-2"></i> Book Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <!-- Bootstrap & Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>