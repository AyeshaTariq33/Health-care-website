<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get patient list for dropdown
$patients = $pdo->query("
    SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name
    FROM appointments a
    JOIN user u ON a.patient_id = u.id
    WHERE a.doctor_id = {$_SESSION['user_id']}
    ORDER BY name ASC
")->fetchAll();

$patientHistory = [];
$selectedPatient = null;

if (isset($_GET['patient_id']) && is_numeric($_GET['patient_id'])) {
    $patientId = $_GET['patient_id'];
    $selectedPatient = $pdo->query("
        SELECT CONCAT(first_name, ' ', last_name) AS name 
        FROM user WHERE id = $patientId
    ")->fetch()['name'];

    // Get comprehensive patient history
    $patientHistory = $pdo->query("
        (SELECT 
            'prescription' AS type,
            p.id,
            p.created_at AS date,
            CONCAT('Prescription: ', p.medication) AS title,
            CONCAT('Dosage: ', p.dosage, ' | Instructions: ', p.instructions) AS details,
            NULL AS file_path
        FROM prescriptions p
        WHERE p.patient_id = $patientId)
        
        UNION
        
        (SELECT 
            'lab_result' AS type,
            lr.id,
            lr.report_date AS date,
            CONCAT('Lab Test: ', lr.test_name) AS title,
            lr.result AS details,
            lr.file_path
        FROM lab_reports lr
        JOIN lab_orders lo ON lr.order_id = lo.id
        WHERE lo.patient_id = $patientId)
        
        UNION
        
        (SELECT 
            'diagnosis' AS type,
            d.id,
            d.created_at AS date,
            CONCAT('Diagnosis: ', d.diagnosis) AS title,
            d.notes AS details,
            NULL AS file_path
        FROM diagnoses d
        WHERE d.patient_id = $patientId)
        
        ORDER BY date DESC
        LIMIT 100
    ")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Patient History | MediCare+</title>
    <style>
        .history-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        .card-prescription {
            border-left-color: #4e73df;
        }
        .card-lab {
            border-left-color: #1cc88a;
        }
        .card-diagnosis {
            border-left-color: #f6c23e;
        }
        .badge-type {
            position: absolute;
            right: 15px;
            top: 15px;
        }
        .patient-selector {
            max-width: 400px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container mt-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-history me-2"></i>Patient Medical History</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Select Patient</label>
                        <select name="patient_id" class="form-select patient-selector" required>
                            <option value="">Choose a patient...</option>
                            <?php foreach ($patients as $patient): ?>
                            <option value="<?= $patient['id'] ?>" 
                                <?= isset($_GET['patient_id']) && $_GET['patient_id'] == $patient['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($patient['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> View History
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($selectedPatient): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>
                <i class="fas fa-user me-2"></i>
                <?= htmlspecialchars($selectedPatient) ?>
            </h4>
            <span class="badge bg-info">
                <?= count($patientHistory) ?> records found
            </span>
        </div>

        <?php if (empty($patientHistory)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No medical history found for this patient.
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($patientHistory as $record): ?>
                <div class="list-group-item history-card card-<?= $record['type'] ?> mb-3 position-relative">
                    <span class="badge bg-<?= 
                        $record['type'] === 'prescription' ? 'primary' : 
                        ($record['type'] === 'lab_result' ? 'success' : 'warning')
                    ?> badge-type">
                        <?= ucfirst($record['type']) ?>
                    </span>
                    
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($record['title']) ?></h6>
                            <p class="mb-1"><?= htmlspecialchars($record['details']) ?></p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">
                                <?= date('M j, Y h:i A', strtotime($record['date'])) ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if ($record['type'] === 'lab_result' && $record['file_path']): ?>
                    <div class="mt-2">
                        <a href="<?= htmlspecialchars($record['file_path']) ?>" 
                           class="btn btn-sm btn-outline-success" target="_blank">
                           <i class="fas fa-file-pdf me-1"></i> View Report
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>