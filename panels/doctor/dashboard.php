<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get counts for dashboard
$todaysAppointments = $pdo->query("
    SELECT COUNT(*) FROM appointments 
    WHERE doctor_id = {$_SESSION['user_id']}
    AND DATE(date) = CURDATE()
")->fetchColumn();

$prescriptionsCount = $pdo->query("
    SELECT COUNT(*) FROM prescriptions 
    WHERE doctor_id = {$_SESSION['user_id']}
")->fetchColumn();

$labRequestsCount = $pdo->query("
    SELECT COUNT(*) FROM lab_requests 
    WHERE doctor_id = {$_SESSION['user_id']}
")->fetchColumn();

$completedAppointments = $pdo->query("
    SELECT COUNT(*) FROM appointments 
    WHERE doctor_id = {$_SESSION['user_id']}
    AND status = 'completed'
    AND DATE(date) = CURDATE()
")->fetchColumn();

$linkedPrescriptions = $pdo->query("
    SELECT COUNT(*) FROM prescriptions 
    WHERE doctor_id = {$_SESSION['user_id']}
    AND pharmacy_id IS NOT NULL
")->fetchColumn();

$patientHistoryAccess = $pdo->query("
    SELECT COUNT(DISTINCT patient_id) FROM medical_history 
    WHERE created_by = {$_SESSION['user_id']}
")->fetchColumn();

$upcomingAppointments = $pdo->query("
    SELECT a.date, CONCAT(u.first_name, ' ', u.last_name) AS patient_name, a.reason
    FROM appointments a
    JOIN user u ON a.patient_id = u.id
    WHERE a.doctor_id = {$_SESSION['user_id']}
    AND a.date >= NOW()
    ORDER BY a.date ASC
    LIMIT 3
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Doctor Dashboard | MediCare+</title>
    <style>
        .dashboard-card {
            transition: all 0.3s;
            border-left: 4px solid;
        }
        .dashboard-card:hover {
            transform: translateY(-3px);
        }
        .appointments-card {
            border-left-color: #4e73df;
        }
        .prescriptions-card {
            border-left-color: #1cc88a;
        }
        .lab-card {
            border-left-color: #36b9cc;
        }
        .completed-card {
            border-left-color: #f6c23e;
        }
        .link-card {
            border-left-color: #9b59b6;
        }
        .history-card {
            border-left-color: #e74a3b;
        }
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
        <!-- Dashboard Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
            <div>
                <h2 class="text-primary mb-1">Dr. <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
                <p class="text-muted mb-0"><?= date('l, F j, Y') ?></p>
            </div>
            <div>
                <a href="../../logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- First Row of Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card appointments-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-primary">
                                <i class="fas fa-calendar-check fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Today's Appointments</h5>
                                <p class="display-6"><?= $todaysAppointments ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="manage-appointments.php" class="btn btn-outline-primary w-100">
                            Manage <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card prescriptions-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-success">
                                <i class="fas fa-file-prescription fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Prescriptions</h5>
                                <p class="display-6"><?= $prescriptionsCount ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="create-prescription.php" class="btn btn-outline-success w-100">
                            Create New <i class="fas fa-plus ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card lab-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-info">
                                <i class="fas fa-flask fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Lab Requests</h5>
                                <p class="display-6"><?= $labRequestsCount ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="request-lab-test.php" class="btn btn-outline-info w-100">
                            Order Test <i class="fas fa-plus ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Second Row of Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card completed-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-warning">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Completed Today</h5>
                                <p class="display-6"><?= $completedAppointments ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="manage-appointments.php?status=completed" class="btn btn-outline-warning w-100">
                            View All <i class="fas fa-eye ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card link-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-purple">
                                <i class="fas fa-link fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Linked Prescriptions</h5>
                                <p class="display-6"><?= $linkedPrescriptions ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="link-prescription.php" class="btn btn-outline-purple w-100">
                            Link New <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card dashboard-card history-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-danger">
                                <i class="fas fa-history fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Patient Histories</h5>
                                <p class="display-6"><?= $patientHistoryAccess ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="view-patient-history.php" class="btn btn-outline-danger w-100">
                            View History <i class="fas fa-eye ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-calendar-day text-primary me-2"></i>Upcoming Appointments</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingAppointments)): ?>
                    <div class="alert alert-info mb-0">No upcoming appointments</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($upcomingAppointments as $appt): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($appt['patient_name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($appt['reason']) ?></small>
                                </div>
                                <div>
                                    <span class="text-muted"><?= date('M j, Y g:i A', strtotime($appt['date'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>