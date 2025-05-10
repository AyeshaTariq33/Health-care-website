<?php
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/database.php';

$allowed_roles = ['doctor'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: /403.php");
    exit();
}

try {
    // Get doctor details
    $stmt = $pdo->prepare("
        SELECT u.*, d.license_number, d.speciality_id, s.name AS speciality
        FROM user u
        JOIN doctors d ON u.id = d.user_id
        JOIN specialities s ON d.speciality_id = s.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $doctor = $stmt->fetch();

    // Get today's schedule
    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');
    
    $schedule_stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.status, 
               p.first_name, p.last_name, p.date_of_birth,
               TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age
        FROM appointments a
        JOIN user p ON a.patient_id = p.id
        WHERE a.doctor_id = ?
        AND a.appointment_date BETWEEN ? AND ?
        ORDER BY a.appointment_date ASC
    ");
    $schedule_stmt->execute([$_SESSION['user_id'], $today_start, $today_end]);
    $todays_appointments = $schedule_stmt->fetchAll();

    // Get statistics
    $stats = [
        'today' => count($todays_appointments),
        'weekly' => getWeeklyAppointmentCount(),
        'patients' => getUniquePatientCount(),
        'pending' => getPendingFollowups()
    ];

    // Get upcoming appointments
    $upcoming_stmt = $pdo->prepare("
        SELECT a.*, p.first_name, p.last_name
        FROM appointments a
        JOIN user p ON a.patient_id = p.id
        WHERE a.doctor_id = ?
        AND a.appointment_date > NOW()
        AND a.status = 'scheduled'
        ORDER BY a.appointment_date ASC
        LIMIT 5
    ");
    $upcoming_stmt->execute([$_SESSION['user_id']]);
    $upcoming_appointments = $upcoming_stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Doctor Dashboard Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading dashboard data";
    header("Location: /500.php");
    exit();
}

// Helper functions
function getWeeklyAppointmentCount(): int {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM appointments
        WHERE doctor_id = ?
        AND appointment_date BETWEEN CURDATE() - INTERVAL 7 DAY AND NOW()
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}

function getUniquePatientCount(): int {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT patient_id) 
        FROM appointments 
        WHERE doctor_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}

function getPendingFollowups(): int {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM followups
        WHERE doctor_id = ?
        AND status = 'pending'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Doctor Dashboard - MediCare+</title>
    <style>
        .availability-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        .available { background-color: #28a745; }
        .busy { background-color: #dc3545; }
        .consultation-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .consultation-card:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container doctor-dashboard">
        <aside class="dashboard-sidebar">
            <div class="doctor-profile">
                <div class="text-center mb-4">
                    <div class="availability-indicator available mb-2"></div>
                    <h4>Dr. <?= htmlspecialchars($doctor['last_name']) ?></h4>
                    <p class="text-muted mb-1"><?= htmlspecialchars($doctor['speciality']) ?></p>
                    <small class="text-muted">License: <?= $doctor['license_number'] ?></small>
                </div>
                
                <nav class="dashboard-menu">
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="schedule.php">
                        <i class="fas fa-calendar-alt"></i> Schedule
                    </a>
                    <a href="patients.php">
                        <i class="fas fa-user-injured"></i> Patients
                    </a>
                    <a href="prescriptions.php">
                        <i class="fas fa-prescription"></i> Prescriptions
                    </a>
                    <a href="reports.php">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </nav>
            </div>
        </aside>

        <main class="dashboard-main">
            <div class="dashboard-header">
                <div>
                    <h1>Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, Dr. <?= htmlspecialchars($doctor['last_name']) ?></h1>
                    <p class="text-muted"><?= date('l, F j, Y') ?></p>
                </div>
                <div class="quick-actions">
                    <a href="new-prescription.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Prescription
                    </a>
                </div>
            </div>

            <div class="stats-container row g-4 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card primary">
                        <i class="fas fa-calendar-day"></i>
                        <div>
                            <h3><?= $stats['today'] ?></h3>
                            <p>Today's Appointments</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card success">
                        <i class="fas fa-users"></i>
                        <div>
                            <h3><?= $stats['patients'] ?></h3>
                            <p>Total Patients</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card warning">
                        <i class="fas fa-tasks"></i>
                        <div>
                            <h3><?= $stats['pending'] ?></h3>
                            <p>Pending Follow-ups</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card info">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3><?= $stats['weekly'] ?></h3>
                            <p>Weekly Appointments</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Today's Schedule</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($todays_appointments)): ?>
                                <div class="alert alert-info mb-0">No appointments scheduled for today</div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($todays_appointments as $appt): ?>
                                    <div class="consultation-card timeline-item">
                                        <div class="timeline-point"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6><?= htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']) ?></h6>
                                                    <small class="text-muted">
                                                        <?= date('g:i A', strtotime($appt['appointment_date'])) ?>
                                                        (<?= $appt['age'] ?> y/o)
                                                    </small>
                                                </div>
                                                <div>
                                                    <a href="patient-chart.php?patient_id=<?= $appt['patient_id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-file-medical"></i> Chart
                                                    </a>
                                                    <button class="btn btn-sm btn-primary start-consultation"
                                                            data-appt-id="<?= $appt['id'] ?>">
                                                        <i class="fas fa-video"></i> Start
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Upcoming Appointments</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($upcoming_appointments)): ?>
                                <div class="alert alert-info mb-0">No upcoming appointments</div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($upcoming_appointments as $appt): ?>
                                    <a href="appointment-details.php?id=<?= $appt['id'] ?>" 
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']) ?></h6>
                                                <small class="text-muted">
                                                    <?= date('D, M j', strtotime($appt['appointment_date'])) ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-primary">
                                                <?= date('g:i A', strtotime($appt['appointment_date'])) ?>
                                            </span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <script>
        // Consultation initiation
        document.querySelectorAll('.start-consultation').forEach(button => {
            button.addEventListener('click', function() {
                const apptId = this.dataset.apptId;
                // Implement video consultation initiation logic
                window.location.href = `consultation.php?appt_id=${apptId}`;
            });
        });
    </script>
</body>
</html>