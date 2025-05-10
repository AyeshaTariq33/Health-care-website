<?php
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/database.php';

$allowed_roles = ['patient'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: /403.php");
    exit();
}

try {
    // Get patient prescriptions
    $stmt = $pdo->prepare("
        SELECT p.*, 
               d.first_name AS doctor_fname,
               d.last_name AS doctor_lname,
               s.name AS speciality
        FROM prescriptions p
        JOIN user d ON p.doctor_id = d.id
        JOIN doctors doc ON d.id = doc.user_id
        JOIN specialities s ON doc.speciality_id = s.id
        WHERE p.patient_id = ?
        ORDER BY p.prescribed_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $prescriptions = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Prescriptions Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading prescriptions";
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>My Prescriptions - MediCare+</title>
    <style>
        .prescription-card {
            transition: transform 0.2s;
            border-left: 4px solid #2A7B8E;
        }
        .prescription-card:hover {
            transform: translateY(-3px);
        }
        .medicine-icon {
            font-size: 2rem;
            color: #2A7B8E;
        }
    </style>
</head>
<body>
    <div class="patient-dashboard">
        <?php include '../../includes/navbar.php'; ?>

        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>My Prescriptions</h1>
                <div class="last-updated">
                    <?php if (!empty($prescriptions)): ?>
                        <small class="text-muted">
                            Last updated: <?= date('M j, Y', strtotime(end($prescriptions)['prescribed_date'])) ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="container-fluid mt-4">
                <?php include '../../includes/alerts.php'; ?>

                <?php if (empty($prescriptions)): ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-prescription-bottle-alt fa-3x text-muted mb-3"></i>
                            <h3 class="text-muted">No Active Prescriptions</h3>
                            <p class="text-muted">Your prescribed medications will appear here</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($prescriptions as $prescription): ?>
                        <div class="col-12">
                            <div class="card shadow-sm prescription-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-prescription-bottle-alt medicine-icon me-3"></i>
                                                <div>
                                                    <h4 class="mb-0">
                                                        <?= htmlspecialchars($prescription['medication_name']) ?>
                                                        <small class="text-muted">
                                                            (<?= htmlspecialchars($prescription['dosage']) ?>)
                                                        </small>
                                                    </h4>
                                                    <small class="text-muted">
                                                        Prescribed by Dr. <?= htmlspecialchars($prescription['doctor_lname']) ?>
                                                        (<?= htmlspecialchars($prescription['speciality']) ?>)
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <strong>Instructions:</strong>
                                                        <?= htmlspecialchars($prescription['instructions']) ?>
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Refills Left:</strong>
                                                        <?= $prescription['refills_remaining'] ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <small class="text-muted">
                                                                <strong>Prescribed:</strong><br>
                                                                <?= date('M j, Y', strtotime($prescription['prescribed_date'])) ?>
                                                            </small>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">
                                                                <strong>Valid Until:</strong><br>
                                                                <?= date('M j, Y', strtotime($prescription['expiry_date'])) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <a href="download-prescription.php?id=<?= $prescription['id'] ?>" 
                                               class="btn btn-outline-primary mb-2"
                                               target="_blank">
                                                <i class="fas fa-download me-2"></i>PDF
                                            </a>
                                            <button class="btn btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#detailsModal<?= $prescription['id'] ?>">
                                                <i class="fas fa-info-circle me-2"></i>Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prescription Details Modal -->
                        <div class="modal fade" id="detailsModal<?= $prescription['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Prescription Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <dl class="row">
                                            <dt class="col-sm-3">Medication</dt>
                                            <dd class="col-sm-9">
                                                <?= htmlspecialchars($prescription['medication_name']) ?>
                                                (<?= htmlspecialchars($prescription['dosage']) ?>)
                                            </dd>

                                            <dt class="col-sm-3">Instructions</dt>
                                            <dd class="col-sm-9">
                                                <?= htmlspecialchars($prescription['instructions']) ?>
                                            </dd>

                                            <dt class="col-sm-3">Prescribing Doctor</dt>
                                            <dd class="col-sm-9">
                                                Dr. <?= htmlspecialchars($prescription['doctor_lname']) ?>
                                                (<?= htmlspecialchars($prescription['speciality']) ?>)
                                            </dd>

                                            <dt class="col-sm-3">Validity</dt>
                                            <dd class="col-sm-9">
                                                <?= date('M j, Y', strtotime($prescription['prescribed_date'])) ?> - 
                                                <?= date('M j, Y', strtotime($prescription['expiry_date'])) ?>
                                            </dd>

                                            <?php if (!empty($prescription['notes'])): ?>
                                            <dt class="col-sm-3">Doctor's Notes</dt>
                                            <dd class="col-sm-9">
                                                <?= htmlspecialchars($prescription['notes']) ?>
                                            </dd>
                                            <?php endif; ?>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>