<?php
session_start();
require_once '../../includes/db_connect.php';
require_once '../../includes/auth_check.php';

// Verify doctor role
if ($_SESSION['user_role'] !== 'doctor') {
    header("Location: /login.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO prescriptions 
            (doctor_id, patient_id, medication, dosage, instructions) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['patient_id'],
            htmlspecialchars($_POST['medication']),
            htmlspecialchars($_POST['dosage']),
            htmlspecialchars($_POST['instructions'])
        ]);
        
        $_SESSION['success'] = "Prescription created successfully";
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error saving prescription: " . $e->getMessage();
    }
}

// Fetch patients list
$patients = $pdo->query("
    SELECT id, CONCAT(first_name, ' ', last_name) AS name 
    FROM user 
    WHERE role = 'patient'
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>New Prescription | MediCare</title>
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container py-4">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Create New Prescription</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary">Back</a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="patient_id" class="form-label">Patient</label>
                        <select name="patient_id" id="patient_id" class="form-select" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['id'] ?>">
                                    <?= htmlspecialchars($patient['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="medication" class="form-label">Medication</label>
                        <input type="text" name="medication" id="medication" class="form-control" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="dosage" class="form-label">Dosage</label>
                        <input type="text" name="dosage" id="dosage" class="form-control" required>
                    </div>
                    
                    <div class="col-12">
                        <label for="instructions" class="form-label">Instructions</label>
                        <textarea name="instructions" id="instructions" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Save Prescription
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
    
    <script>
        // Basic client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields');
            }
        });
    </script>
</body>
</html>