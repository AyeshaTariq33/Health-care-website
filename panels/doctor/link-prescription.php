<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get active prescriptions
$prescriptions = $pdo->query("
    SELECT p.id, p.medication, p.created_at,
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name
    FROM prescriptions p
    JOIN user u ON p.patient_id = u.id
    WHERE p.doctor_id = {$_SESSION['user_id']}
    AND p.pharmacy_id IS NULL
    AND p.status = 'active'
    ORDER BY p.created_at DESC
")->fetchAll();

// Get pharmacies
$pharmacies = $pdo->query("
    SELECT id, CONCAT(first_name, ' ', last_name) AS name 
    FROM user 
    WHERE role = 'pharmacy'
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE prescriptions 
        SET pharmacy_id = ?, delivery_status = 'pending' 
        WHERE id = ? AND doctor_id = ?
    ");
    $success = $stmt->execute([
        $_POST['pharmacy_id'],
        $_POST['prescription_id'],
        $_SESSION['user_id']
    ]);
    
    if ($success) {
        header("Location: dashboard.php?success=prescription_linked");
        exit;
    } else {
        $error = "Failed to link prescription to pharmacy";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Link Prescription | MediCare+</title>
    <style>
        .prescription-card {
            border-left: 4px solid #4e73df;
            transition: all 0.3s;
        }
        .prescription-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-medication {
            background-color: #e3f2fd;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container mt-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-link me-2"></i>Link Prescription to Pharmacy</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($prescriptions)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No unlinked active prescriptions found.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($prescriptions as $prescription): ?>
                <div class="col-md-6 mb-4">
                    <div class="card prescription-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">
                                        <?= htmlspecialchars($prescription['patient_name']) ?>
                                    </h5>
                                    <span class="badge badge-medication mb-2">
                                        <?= htmlspecialchars($prescription['medication']) ?>
                                    </span>
                                    <p class="text-muted small mb-0">
                                        <i class="far fa-calendar me-1"></i>
                                        <?= date('M j, Y', strtotime($prescription['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="prescription_id" value="<?= $prescription['id'] ?>">
                                
                                <div class="input-group">
                                    <select name="pharmacy_id" class="form-select" required>
                                        <option value="">Select Pharmacy</option>
                                        <?php foreach ($pharmacies as $pharmacy): ?>
                                        <option value="<?= $pharmacy['id'] ?>">
                                            <?= htmlspecialchars($pharmacy['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-link me-1"></i> Link
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>