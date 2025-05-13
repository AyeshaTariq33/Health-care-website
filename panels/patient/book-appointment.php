<?php
session_start();

// Only allow logged-in patients
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');
    
    $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, date, reason) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'], // Patient ID from session
        $_POST['doctor_id'], // Selected doctor
        $_POST['date'],      // Format: YYYY-MM-DD HH:MM:SS
        $_POST['reason']     // Appointment reason
    ]);
    
    header("Location: dashboard.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3>Book Appointment</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Doctor:</label>
                <select name="doctor_id" class="form-control" required>
                    <?php
                    $pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');
                    $doctors = $pdo->query("SELECT id, first_name, last_name FROM user WHERE role = 'doctor'")->fetchAll();
                    
                    foreach ($doctors as $doctor) {
                        echo "<option value='{$doctor['id']}'>Dr. {$doctor['first_name']} {$doctor['last_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label>Date & Time:</label>
                <input type="datetime-local" name="date" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label>Reason:</label>
                <textarea name="reason" class="form-control" rows="3" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Book Now</button>
        </form>
    </div>
</body>
</html>