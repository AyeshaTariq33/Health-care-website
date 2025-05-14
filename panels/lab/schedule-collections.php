<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get today's collections
$todaysCollections = $pdo->query("
    SELECT sc.*, 
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
           ltt.name AS test_name,
           CONCAT(phleb.first_name, ' ', phleb.last_name) AS collector_name
    FROM sample_collections sc
    JOIN lab_orders lo ON sc.order_id = lo.id
    JOIN user u ON sc.patient_id = u.id
    JOIN lab_test_types ltt ON lo.test_type_id = ltt.id
    LEFT JOIN user phleb ON sc.collector_id = phleb.id
    WHERE DATE(sc.scheduled_date) = CURDATE()
    ORDER BY sc.scheduled_date
")->fetchAll();

// Get pending collections
$pendingCollections = $pdo->query("
    SELECT lo.id, 
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
           ltt.name AS test_name,
           ltt.sample_type
    FROM lab_orders lo
    JOIN user u ON lo.patient_id = u.id
    JOIN lab_test_types ltt ON lo.test_type_id = ltt.id
    LEFT JOIN sample_collections sc ON sc.order_id = lo.id
    WHERE lo.status = 'processing'
    AND sc.id IS NULL
")->fetchAll();

// Get available phlebotomists
$phlebotomists = $pdo->query("
    SELECT id, CONCAT(first_name, ' ', last_name) AS name 
    FROM user 
    WHERE role = 'phlebotomist'
")->fetchAll();

// Handle collection scheduling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO sample_collections 
        (order_id, patient_id, scheduled_date, collector_id, status)
        VALUES (?, ?, ?, ?, 'scheduled')
    ");
    $stmt->execute([
        $_POST['order_id'],
        $_POST['patient_id'],
        $_POST['scheduled_date'],
        $_POST['collector_id']
    ]);
    
    header("Location: schedule-collections.php?success=1");
    exit;
}

// Handle collection status update
if (isset($_GET['update_status'])) {
    $stmt = $pdo->prepare("
        UPDATE sample_collections 
        SET status = ?
        WHERE id = ?
    ");
    $stmt->execute([$_GET['status'], $_GET['id']]);
    
    header("Location: schedule-collections.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Collections | MediCare+ Lab Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .collection-card {
            border-left: 4px solid #4e73df;
            transition: all 0.2s;
        }
        .collection-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.75rem;
        }
        .badge-scheduled { background-color: #4e73df; }
        .badge-completed { background-color: #1cc88a; }
        .badge-failed { background-color: #e74a3b; }
        .sample-badge {
            background-color: #6f42c1;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">
                <i class="fas fa-syringe me-2"></i>Sample Collections
            </h1>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                Collection successfully scheduled!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-day me-2"></i>Today's Collections
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($todaysCollections)): ?>
                            <div class="alert alert-info m-3">
                                No collections scheduled for today.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($todaysCollections as $collection): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($collection['patient_name']) ?></h6>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($collection['test_name']) ?>
                                                <span class="badge sample-badge ms-2">
                                                    <?= htmlspecialchars($collection['sample_type'] ?? 'N/A') ?>
                                                </span>
                                            </small>
                                        </div>
                                        <span class="badge status-badge badge-<?= $collection['status'] ?>">
                                            <?= ucfirst($collection['status']) ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <small class="text-muted">Scheduled:</small>
                                            <p class="mb-0">
                                                <?= date('h:i A', strtotime($collection['scheduled_date'])) ?>
                                                <?php if ($collection['collector_name']): ?>
                                                    with <?= htmlspecialchars($collection['collector_name']) ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="btn-group">
                                            <a href="?update_status=1&id=<?= $collection['id'] ?>&status=completed" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="?update_status=1&id=<?= $collection['id'] ?>&status=failed" 
                                               class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Schedule New Collection
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingCollections)): ?>
                            <div class="alert alert-info">
                                No tests currently require sample collection.
                            </div>
                        <?php else: ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Test Order</label>
                                    <select name="order_id" class="form-select" required>
                                        <option value="">Select Test Order</option>
                                        <?php foreach ($pendingCollections as $order): ?>
                                        <option value="<?= $order['id'] ?>">
                                            <?= htmlspecialchars($order['patient_name']) ?> - 
                                            <?= htmlspecialchars($order['test_name']) ?>
                                            (<?= htmlspecialchars($order['sample_type']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Patient ID</label>
                                    <input type="text" name="patient_id" class="form-control" required
                                           placeholder="Enter patient ID">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Collection Date & Time</label>
                                    <input type="datetime-local" name="scheduled_date" 
                                           class="form-control datetime-picker" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Phlebotomist</label>
                                    <select name="collector_id" class="form-select" required>
                                        <option value="">Select Phlebotomist</option>
                                        <?php foreach ($phlebotomists as $phleb): ?>
                                        <option value="<?= $phleb['id'] ?>">
                                            <?= htmlspecialchars($phleb['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-calendar-check me-1"></i> Schedule Collection
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize datetime picker
        flatpickr(".datetime-picker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: true
        });
    </script>
</body>
</html>