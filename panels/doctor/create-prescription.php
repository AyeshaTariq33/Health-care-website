<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get today's appointments for dropdown
$appointments = $pdo->query("
    SELECT a.id, a.date, CONCAT(u.first_name, ' ', u.last_name) AS patient_name
    FROM appointments a
    JOIN user u ON a.patient_id = u.id
    WHERE a.doctor_id = {$_SESSION['user_id']}
    AND a.status = 'completed'
    ORDER BY a.date DESC
")->fetchAll();

// Get medicines for autocomplete
$medicines = $pdo->query("SELECT name FROM medicines")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    
    try {
        // 1. Create diagnosis record
        $stmt = $pdo->prepare("
            INSERT INTO diagnoses 
            (appointment_id, doctor_id, patient_id, diagnosis, notes) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['appointment_id'],
            $_SESSION['user_id'],
            $_POST['patient_id'],
            $_POST['diagnosis'],
            $_POST['notes']
        ]);
        $diagnosisId = $pdo->lastInsertId();
        
        // 2. Create prescription
        $stmt = $pdo->prepare("
            INSERT INTO prescriptions 
            (doctor_id, patient_id, diagnosis_id, appointment_id, 
             medication, dosage, instructions, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['patient_id'],
            $diagnosisId,
            $_POST['appointment_id'],
            $_POST['medication'],
            $_POST['dosage'],
            $_POST['instructions']
        ]);
        
        $pdo->commit();
        header("Location: dashboard.php?success=prescription_created");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to create prescription: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Create Prescription | MediCare+</title>
    <style>
        /* Reuse your dashboard styles */
        .form-card {
            border-left: 4px solid #3498db;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .medicine-chip {
            background-color: #e3f2fd;
            border-radius: 16px;
            padding: 2px 10px;
            margin: 2px;
            display: inline-block;
        }
    </style>
</head>
<body>
    
    <main class="container mt-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-prescription me-2"></i>Create Prescription</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <div class="card form-card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Appointment</label>
                            <select name="appointment_id" class="form-select" required>
                                <option value="">Select Appointment</option>
                                <?php foreach ($appointments as $apt): ?>
                                <option value="<?= $apt['id'] ?>" 
                                    data-patient-id="<?= $apt['patient_id'] ?>">
                                    <?= date('M j, Y', strtotime($apt['date'])) ?> - 
                                    <?= htmlspecialchars($apt['patient_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="patient_id" id="patient_id">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Diagnosis</label>
                            <input type="text" name="diagnosis" class="form-control" required 
                                   placeholder="Enter primary diagnosis">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Medication</label>
                            <input type="text" name="medication" class="form-control" 
                                   id="medicine-input" required list="medicines">
                            <datalist id="medicines">
                                <?php foreach ($medicines as $med): ?>
                                <option value="<?= htmlspecialchars($med) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dosage</label>
                            <input type="text" name="dosage" class="form-control" required 
                                   placeholder="e.g., 1 tablet twice daily">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Instructions</label>
                        <textarea name="instructions" class="form-control" rows="3"
                                  placeholder="Special instructions..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Clinical notes..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Prescription
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Auto-set patient ID when appointment is selected
        document.querySelector('[name="appointment_id"]').addEventListener('change', function() {
            const patientId = this.options[this.selectedIndex].getAttribute('data-patient-id');
            document.getElementById('patient_id').value = patientId;
        });
    </script>
</body>
</html>