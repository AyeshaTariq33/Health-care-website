<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO prescriptions 
        (doctor_id, patient_id, medication, dosage, instructions) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['patient_id'],
        $_POST['medication'],
        $_POST['dosage'],
        $_POST['instructions']
    ]);
    
    header("Location: dashboard.php?success=1");
    exit;
}

// Fetch patients
$patients = $pdo->query("
    SELECT id, CONCAT(first_name, ' ', last_name) AS name 
    FROM user 
    WHERE role = 'patient'
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Prescription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h3>Create New Prescription</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label>Patient:</label>
                <select name="patient_id" class="form-control" required>
                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= $patient['id'] ?>"><?= $patient['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label>Medication:</label>
                <input type="text" name="medication" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label>Dosage:</label>
                <input type="text" name="dosage" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label>Instructions:</label>
                <textarea name="instructions" class="form-control" rows="3" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Prescription</button>
        </form>
    </div>
</body>
</html>