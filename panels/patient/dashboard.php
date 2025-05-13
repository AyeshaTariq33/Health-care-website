<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get counts for dashboard
$prescriptionCount = $pdo->query("
    SELECT COUNT(*) FROM prescriptions 
    WHERE patient_id = {$_SESSION['user_id']}
")->fetchColumn();

$labTestCount = $pdo->query("
    SELECT COUNT(*) FROM lab_orders 
    WHERE patient_id = {$_SESSION['user_id']}
")->fetchColumn();

$labReportsCount = $pdo->query("
    SELECT COUNT(*) FROM lab_reports lr
    JOIN lab_orders lo ON lr.order_id = lo.id
    WHERE lo.patient_id = {$_SESSION['user_id']}
")->fetchColumn();

$pendingOrders = $pdo->query("
    SELECT COUNT(*) FROM medicine_orders 
    WHERE patient_id = {$_SESSION['user_id']} AND status = 'pending'
")->fetchColumn();

$completedPayments = $pdo->query("
    SELECT COUNT(*) FROM payments p
    JOIN medicine_orders o ON p.order_id = o.id
    WHERE o.patient_id = {$_SESSION['user_id']} AND p.status = 'completed'
")->fetchColumn();

$upcomingAppointments = $pdo->query("
    SELECT a.date, CONCAT(u.first_name, ' ', u.last_name) AS doctor_name
    FROM appointments a
    JOIN user u ON a.doctor_id = u.id
    WHERE a.patient_id = {$_SESSION['user_id']}
    AND a.date >= NOW()
    ORDER BY a.date ASC
    LIMIT 3
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Patient Dashboard</title>
    <style>
        .dashboard-card {
            transition: all 0.3s;
            border-left: 4px solid;
        }
        .dashboard-card:hover {
            transform: translateY(-3px);
        }
        .prescription-card {
            border-left-color: #4e73df;
        }
        .labtest-card {
            border-left-color: #1cc88a;
        }
        .labreport-card {
            border-left-color: #9b59b6;
        }
        .order-card {
            border-left-color: #36b9cc;
        }
        .payment-card {
            border-left-color: #f6c23e;
        }
        .bookappointment-card {
            border-left-color: #e74a3b;
        }
        .quick-action-btn {
            transition: all 0.2s;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
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
                <h2 class="text-primary mb-1">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
                <p class="text-muted mb-0"><?= date('l, F j, Y') ?></p>
            </div>
            <div>
                <a href="../../logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- Success Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <?php
                switch($_GET['success']) {
                    case 'test_booked': 
                        echo '<i class="fas fa-check-circle me-2"></i>Lab test booked successfully!';
                        break;
                    case 'appointment_booked':
                        echo '<i class="fas fa-calendar-check me-2"></i>Appointment booked successfully!';
                        break;
                    case 'order_placed':
                        echo '<i class="fas fa-check-circle me-2"></i>Medicine order placed successfully!';
                        break;
                    case 'payment_completed':
                        echo '<i class="fas fa-check-circle me-2"></i>Payment completed successfully!';
                        break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Quick Stats -->
         
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card prescription-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-primary">
                                <i class="fas fa-prescription-bottle fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Prescriptions</h5>
                                <p class="display-6"><?= $prescriptionCount ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="view-prescriptions.php" class="btn btn-outline-primary w-100">
                            View All <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card labtest-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-success">
                                <i class="fas fa-flask fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Lab Tests</h5>
                                <p class="display-6"><?= $labTestCount ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="book-lab-test.php" class="btn btn-outline-success w-100">
                            Book New <i class="fas fa-plus ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card order-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-info">
                                <i class="fas fa-pills fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Pending Orders</h5>
                                <p class="display-6"><?= $pendingOrders ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="order-medicines.php" class="btn btn-outline-info w-100">
                            Order Now <i class="fas fa-shopping-cart ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card labreport-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-purple">
                                <i class="fas fa-file-medical fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Lab Reports</h5>
                                <p class="display-6"><?= $labReportsCount ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="view-lab-reports.php" class="btn btn-outline-purple w-100">
                            View Reports <i class="fas fa-eye ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card payment-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-warning">
                                <i class="fas fa-receipt fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Completed Payments</h5>
                                <p class="display-6"><?= $completedPayments ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="payment-history.php" class="btn btn-outline-warning w-100">
                            View History <i class="fas fa-history ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card dashboard-card bookappointment-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-danger">
                                <i class="fas fa-calendar-check fa-3x"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Book Appointment</h5>
                                <p class="display-6"><?= $completedPayments ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="book-appointment.php" class="btn btn-outline-danger w-100">
                            Book Now <i class="fas fa-calendar-plus ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-calendar-day text-danger me-2"></i>Upcoming Appointments</h5>
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
                                    <h6 class="mb-1">Dr. <?= htmlspecialchars($appt['doctor_name']) ?></h6>
                                    <small class="text-muted"><?= date('M j, Y g:i A', strtotime($appt['date'])) ?></small>
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