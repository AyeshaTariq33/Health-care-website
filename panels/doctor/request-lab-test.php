<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get test types and recent patients
$testTypes = $pdo->query("SELECT id, name, description FROM lab_test_types")->fetchAll();
$patients = $pdo->query("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name
    FROM appointments a
    JOIN user u ON a.patient_id = u.id
    WHERE a.doctor_id = {$_SESSION['user_id']}
    GROUP BY u.id
    ORDER BY MAX(a.date) DESC
    LIMIT 10
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO lab_requests 
        (doctor_id, patient_id, test_type_id, notes, priority) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['patient_id'],
        $_POST['test_type_id'],
        $_POST['notes'],
        $_POST['priority']
    ]);
    
    header("Location: dashboard.php?success=lab_test_requested");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Request Lab Test | MediCare+</title>
    <style>
        .test-card {
            border-left: 4px solid #1cc88a;
            transition: all 0.3s;
        }
        .test-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .urgent-badge {
            position: absolute;
            right: 15px;
            top: 15px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container mt-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-flask me-2"></i>Request Lab Test</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card test-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Patient</label>
                                    <select name="patient_id" class="form-select" required>
                                        <option value="">Select Patient</option>
                                        <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['id'] ?>">
                                            <?= htmlspecialchars($patient['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="routine">Routine</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Test Type</label>
                                <select name="test_type_id" class="form-select" required>
                                    <option value="">Select Test</option>
                                    <?php foreach ($testTypes as $test): ?>
                                    <option value="<?= $test['id'] ?>" 
                                        data-description="<?= htmlspecialchars($test['description']) ?>">
                                        <?= htmlspecialchars($test['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="test-description" class="text-muted small mt-2"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Clinical Notes</label>
                                <textarea name="notes" class="form-control" rows="3"
                                          placeholder="Reason for test, special instructions..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-1"></i> Submit Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Test Information</h6>
                    </div>
                    <div class="card-body">
                        <h6 class="text-muted">Common Tests:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle text-success me-2"></i> Complete Blood Count</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i> Lipid Profile</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i> Liver Function Test</li>
                        </ul>
                        <hr>
                        <p class="small text-muted">
                            <i class="fas fa-clock me-1"></i> Routine tests typically completed within 24-48 hours
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <script>
        // Show test description when selected
        document.querySelector('[name="test_type_id"]').addEventListener('change', function() {
            const desc = this.options[this.selectedIndex].getAttribute('data-description');
            document.getElementById('test-description').textContent = desc || 'No description available';
        });
    </script>
</body>
</html>