<?php
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/database.php';

$allowed_roles = ['patient'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: /403.php");
    exit();
}

// Handle appointment cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid request";
        header("Location: appointments.php");
        exit();
    }

    $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
    
    try {
        $stmt = $pdo->prepare("
            UPDATE appointments 
            SET status = 'cancelled', cancelled_at = NOW() 
            WHERE id = ? AND patient_id = ?
        ");
        $stmt->execute([$appointment_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Appointment cancelled successfully";
        } else {
            $_SESSION['error'] = "Appointment not found or already cancelled";
        }
    } catch (PDOException $e) {
        error_log("Cancellation Error: " . $e->getMessage());
        $_SESSION['error'] = "Error cancelling appointment";
    }
    
    header("Location: appointments.php");
    exit();
}

// Fetch appointments
try {
    // Upcoming appointments
    $upcoming_stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.status, a.notes,
               d.first_name AS doctor_fname, d.last_name AS doctor_lname,
               s.name AS speciality
        FROM appointments a
        JOIN user d ON a.doctor_id = d.id
        JOIN doctors doc ON d.id = doc.user_id
        JOIN specialities s ON doc.speciality_id = s.id
        WHERE a.patient_id = ? 
        AND a.appointment_date >= NOW()
        AND a.status != 'cancelled'
        ORDER BY a.appointment_date ASC
    ");
    $upcoming_stmt->execute([$_SESSION['user_id']]);
    $upcoming_appointments = $upcoming_stmt->fetchAll();

    // Past appointments
    $past_stmt = $pdo->prepare("
        SELECT a.*, 
               d.first_name AS doctor_fname, d.last_name AS doctor_lname,
               s.name AS speciality
        FROM appointments a
        JOIN user d ON a.doctor_id = d.id
        JOIN doctors doc ON d.id = doc.user_id
        JOIN specialities s ON doc.speciality_id = s.id
        WHERE a.patient_id = ? 
        AND (a.appointment_date < NOW() OR a.status = 'cancelled')
        ORDER BY a.appointment_date DESC
        LIMIT 50
    ");
    $past_stmt->execute([$_SESSION['user_id']]);
    $past_appointments = $past_stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Appointments Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading appointments";
    header("Location: dashboard.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>My Appointments - MediCare+</title>
    <style>
        .appointment-card {
            transition: transform 0.2s;
        }
        .appointment-card:hover {
            transform: translateY(-3px);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .cancel-btn {
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        .cancel-btn:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="patient-dashboard">
        <?php include '../../includes/navbar.php'; ?>

        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>My Appointments</h1>
                <a href="book-appointment.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Appointment
                </a>
            </div>

            <?php include '../../includes/alerts.php'; ?>

            <div class="container-fluid mt-4">
                <!-- Upcoming Appointments -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>Upcoming Appointments
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcoming_appointments)): ?>
                            <div class="alert alert-info mb-0">No upcoming appointments</div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($upcoming_appointments as $apt): ?>
                                <div class="col-12">
                                    <div class="appointment-card card shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="card-title">
                                                        Dr. <?= htmlspecialchars($apt['doctor_lname']) ?>
                                                        <small class="text-muted">(<?= htmlspecialchars($apt['speciality']) ?>)</small>
                                                    </h5>
                                                    <div class="text-muted">
                                                        <i class="fas fa-clock me-2"></i>
                                                        <?= date('M j, Y \a\t g:i A', strtotime($apt['appointment_date'])) ?>
                                                    </div>
                                                    <?php if ($apt['notes']): ?>
                                                    <p class="mt-2 mb-0 text-muted small">
                                                        <i class="fas fa-sticky-note me-2"></i>
                                                        <?= htmlspecialchars($apt['notes']) ?>
                                                    </p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-<?= getStatusColor($apt['status']) ?> status-badge">
                                                        <?= ucfirst($apt['status']) ?>
                                                    </span>
                                                    <div class="mt-2">
                                                        <button class="btn btn-sm btn-outline-secondary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detailsModal<?= $apt['id'] ?>">
                                                            <i class="fas fa-info-circle"></i>
                                                        </button>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="csrf_token" 
                                                                   value="<?= $_SESSION['csrf_token'] ?>">
                                                            <input type="hidden" name="appointment_id" 
                                                                   value="<?= $apt['id'] ?>">
                                                            <button type="submit" name="cancel_appointment" 
                                                                    class="btn btn-sm btn-danger cancel-btn"
                                                                    onclick="return confirm('Are you sure?')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Past Appointments -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Appointment History
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($past_appointments)): ?>
                            <div class="alert alert-info mb-0">No past appointments</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Doctor</th>
                                            <th>Speciality</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($past_appointments as $apt): ?>
                                        <tr>
                                            <td><?= date('M j, Y', strtotime($apt['appointment_date'])) ?></td>
                                            <td>Dr. <?= htmlspecialchars($apt['doctor_lname']) ?></td>
                                            <td><?= htmlspecialchars($apt['speciality']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getStatusColor($apt['status']) ?>">
                                                    <?= ucfirst($apt['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailsModal<?= $apt['id'] ?>">
                                                    Details
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <?php foreach (array_merge($upcoming_appointments, $past_appointments) as $apt): ?>
    <div class="modal fade" id="detailsModal<?= $apt['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-3">Doctor</dt>
                        <dd class="col-sm-9">Dr. <?= htmlspecialchars($apt['doctor_fname'] . ' ' . $apt['doctor_lname']) ?></dd>

                        <dt class="col-sm-3">Speciality</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($apt['speciality']) ?></dd>

                        <dt class="col-sm-3">Date & Time</dt>
                        <dd class="col-sm-9">
                            <?= date('M j, Y \a\t g:i A', strtotime($apt['appointment_date'])) ?>
                        </dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-<?= getStatusColor($apt['status']) ?>">
                                <?= ucfirst($apt['status']) ?>
                            </span>
                        </dd>

                        <?php if ($apt['notes']): ?>
                        <dt class="col-sm-3">Notes</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($apt['notes']) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php include '../../includes/footer.php'; ?>

    <script>
        // Initialize Bootstrap components
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            .forEach(tooltip => new bootstrap.Tooltip(tooltip));
    </script>
</body>
</html>

<?php
// Helper function for status colors
function getStatusColor(string $status): string {
    return match($status) {
        'completed' => 'success',
        'cancelled' => 'danger',
        'scheduled' => 'primary',
        default => 'secondary'
    };
}
?>