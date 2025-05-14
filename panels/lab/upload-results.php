<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get tests ready for results
$pendingTests = $pdo->query("
    SELECT lr.id, 
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
           ltt.name AS test_name,
           lr.status
    FROM lab_reports lr
    JOIN lab_orders lo ON lr.order_id = lo.id
    JOIN user u ON lo.patient_id = u.id
    JOIN lab_test_types ltt ON lo.test_type_id = ltt.id
    WHERE lr.status = 'pending'
")->fetchAll();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportId = $_POST['report_id'];
    $result = $_POST['result'];
    $isAbnormal = isset($_POST['is_abnormal']) ? 1 : 0;
    $isCritical = isset($_POST['is_critical']) ? 1 : 0;
    
    // Simple file handling (no security checks per requirements)
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileName = 'report_' . $reportId . '_' . time() . '.pdf';
    $filePath = $uploadDir . $fileName;
    
    move_uploaded_file($_FILES['report_file']['tmp_name'], $filePath);
    
    // Update report
    $stmt = $pdo->prepare("
        UPDATE lab_reports 
        SET result = ?,
            is_abnormal = ?,
            critical_flag = ?,
            file_path = ?,
            status = 'completed',
            completed_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$result, $isAbnormal, $isCritical, $fileName, $reportId]);
    
    header("Location: upload-results.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Test Results | MediCare+ Lab Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-card {
            border-left: 4px solid #4e73df;
            transition: all 0.2s;
        }
        .test-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .badge-status {
            font-size: 0.75rem;
        }
        .upload-btn {
            transition: all 0.2s;
        }
        .upload-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">
                <i class="fas fa-microscope me-2"></i>Upload Test Results
            </h1>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                Results uploaded successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Tests Ready for Results</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingTests)): ?>
                            <div class="alert alert-info mb-0">
                                No tests currently require results upload.
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($pendingTests as $test): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($test['patient_name']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($test['test_name']) ?></small>
                                        </div>
                                        <button class="btn btn-sm btn-primary upload-btn"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#uploadModal"
                                                data-test-id="<?= $test['id'] ?>"
                                                data-test-name="<?= htmlspecialchars($test['test_name']) ?>">
                                            <i class="fas fa-upload me-1"></i> Upload
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Upload Modal -->
            <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title">Upload Test Results</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="report_id" id="modalReportId">
                                <div class="mb-3">
                                    <label class="form-label">Test</label>
                                    <input type="text" class="form-control" id="modalTestName" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Results Summary*</label>
                                    <textarea name="result" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">PDF Report*</label>
                                    <input type="file" name="report_file" class="form-control" accept=".pdf" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="is_abnormal" class="form-check-input" id="abnormalCheck">
                                    <label class="form-check-label" for="abnormalCheck">Abnormal Results</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="is_critical" class="form-check-input" id="criticalCheck">
                                    <label class="form-check-label" for="criticalCheck">Critical Findings</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Upload Results</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal setup
        document.getElementById('uploadModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('modalReportId').value = button.getAttribute('data-test-id');
            document.getElementById('modalTestName').value = button.getAttribute('data-test-name');
        });
    </script>
</body>
</html>