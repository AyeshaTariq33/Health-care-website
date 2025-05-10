<?php
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/database.php';

// Set role-specific access control
$allowed_roles = ['patient'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: /403.php");
    exit();
}

try {
    // Fetch patient data
    $stmt = $pdo->prepare("
        SELECT u.first_name, u.last_name, u.email, u.join_date, 
               p.insurance_provider, p.insurance_number
        FROM user u
        LEFT JOIN patients p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $patient = $stmt->fetch();

    // Fetch statistics
    $stats = [
        'appointments' => getUpcomingAppointmentsCount(),
        'prescriptions' => getActivePrescriptionsCount(),
        'lab_tests' => getPendingLabTestsCount(),
        'balance' => getCurrentBalance()
    ];

    // Fetch recent appointments
    $appointments = getRecentAppointments(5);

    // Fetch recent prescriptions
    $prescriptions = getRecentPrescriptions(5);

} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading dashboard data";
    header("Location: /500.php");
    exit();
}

// Helper functions
function getUpcomingAppointmentsCount(): int {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM appointments 
        WHERE patient_id = ? AND appointment_date >= NOW()
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}

function getRecentAppointments(int $limit): array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.*, d.first_name AS doctor_fname, d.last_name AS doctor_lname,
               s.name AS speciality
        FROM appointments a
        JOIN user d ON a.doctor_id = d.id
        JOIN doctors doc ON d.id = doc.user_id
        JOIN specialities s ON doc.speciality_id = s.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC
        LIMIT ?
    ");
    $stmt->execute([$_SESSION['user_id'], $limit]);
    return $stmt->fetchAll();
}

// Similar functions for prescriptions, lab tests, and balance...

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Patient Dashboard - MediCare+</title>
</head>
<body>
    <div class="patient-dashboard">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div class="patient-profile">
                <img src="../../assets/img/patient-avatar.jpg" class="patient-avatar" 
                     alt="<?= htmlspecialchars($patient['first_name']) ?>'s Avatar">
                <h3 class="patient-name"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h3>
                <p class="patient-meta">
                    Member since <?= date('M Y', strtotime($patient['join_date'])) ?><br>
                    Insurance: <?= htmlspecialchars($patient['insurance_provider']) ?>
                </p>
            </div>

            <nav class="dashboard-menu">
                <?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
                <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="book-appointment.php">
                    <i class="fas fa-calendar-plus"></i> Book Appointment
                </a>
                <!-- Other menu items -->
            </nav>
        </aside>

        <main class="dashboard-main">
            <div class="dashboard-header">
                <div>
                    <h1 class="greeting">
                        Good <?= match(true) {
                            date('H') < 12 => 'Morning',
                            date('H') < 17 => 'Afternoon',
                            default => 'Evening'
                        } ?>, <?= htmlspecialchars($patient['first_name']) ?>!
                    </h1>
                    <p class="current-date"><?= date('l, F j, Y') ?></p>
                </div>
                <a href="book-appointment.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Appointment
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card primary">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <h3><?= $stats['appointments'] ?></h3>
                        <p>Upcoming Appointments</p>
                    </div>
                </div>
                <!-- Other stat cards -->
            </div>

            <!-- Appointments Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-alt"></i> Recent Appointments</h2>
                    <a href="appointments.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="appointments-list">
                    <?php foreach ($appointments as $apt): ?>
                    <div class="appointment-card <?= $apt['status'] ?>">
                        <div class="appointment-info">
                            <h4>Dr. <?= htmlspecialchars($apt['doctor_lname']) ?></h4>
                            <div class="appointment-meta">
                                <span class="department"><?= htmlspecialchars($apt['speciality']) ?></span>
                                <span class="date">
                                    <?= date('M j, Y', strtotime($apt['appointment_date'])) ?>
                                </span>
                                <span class="time">
                                    <?= date('g:i A', strtotime($apt['appointment_date'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="appointment-actions">
                            <button class="btn btn-icon" data-bs-toggle="modal" 
                                    data-bs-target="#appointmentModal-<?= $apt['id'] ?>">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Prescriptions Section -->
            <!-- Similar dynamic content for prescriptions -->
            
        </main>
    </div>

    <!-- Modals -->
    <?php foreach ($appointments as $apt): ?>
    <div class="modal fade" id="appointmentModal-<?= $apt['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Doctor:</strong> Dr. <?= htmlspecialchars($apt['doctor_lname']) ?></p>
                    <p><strong>Date:</strong> <?= date('F j, Y', strtotime($apt['appointment_date'])) ?></p>
                    <p><strong>Time:</strong> <?= date('g:i A', strtotime($apt['appointment_date'])) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($apt['status']) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>