<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Get all test types
$testTypes = $pdo->query("SELECT * FROM lab_test_types ORDER BY name")->fetchAll();

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $data = [
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'turnaround_hours' => $_POST['turnaround_hours'],
        'sample_type' => $_POST['sample_type'],
        'preparation_instructions' => $_POST['preparation_instructions']
    ];

    if ($id) {
        // Update existing
        $stmt = $pdo->prepare("
            UPDATE lab_test_types 
            SET name=?, description=?, price=?, turnaround_hours=?, 
                sample_type=?, preparation_instructions=?
            WHERE id=?
        ");
        $data[] = $id;
    } else {
        // Insert new
        $stmt = $pdo->prepare("
            INSERT INTO lab_test_types 
            (name, description, price, turnaround_hours, sample_type, preparation_instructions)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
    }
    
    $stmt->execute(array_values($data));
    header("Location: test-types.php?success=1");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM lab_test_types WHERE id=?")->execute([$_GET['delete']]);
    header("Location: test-types.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Types Catalog | MediCare+ Lab Panel</title>
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
        .sample-badge {
            background-color: #6f42c1;
        }
        .action-btn {
            transition: all 0.2s;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">
                <i class="fas fa-flask me-2"></i>Test Types Catalog
            </h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#testTypeModal">
                <i class="fas fa-plus me-1"></i> Add New
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                Test type updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($testTypes as $test): ?>
            <div class="col-md-6 mb-4">
                <div class="card test-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title"><?= htmlspecialchars($test['name']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($test['description']) ?></p>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary action-btn me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#testTypeModal"
                                        data-test-id="<?= $test['id'] ?>"
                                        data-test-data='<?= json_encode($test) ?>'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="test-types.php?delete=<?= $test['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger action-btn"
                                   onclick="return confirm('Delete this test type?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <small class="text-muted">Sample Type:</small>
                                <p>
                                    <span class="badge sample-badge"><?= htmlspecialchars($test['sample_type']) ?></span>
                                </p>
                            </div>
                            <div class="col-3">
                                <small class="text-muted">Price:</small>
                                <p>$<?= number_format($test['price'], 2) ?></p>
                            </div>
                            <div class="col-3">
                                <small class="text-muted">Turnaround:</small>
                                <p><?= $test['turnaround_hours'] ?> hours</p>
                            </div>
                        </div>
                        
                        <?php if ($test['preparation_instructions']): ?>
                            <div class="alert alert-info p-2 mb-0 mt-2">
                                <small><strong>Preparation:</strong> <?= htmlspecialchars($test['preparation_instructions']) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Test Type Modal -->
    <div class="modal fade" id="testTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="id" id="testTypeId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add New Test Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Test Name*</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price ($)*</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Turnaround (hours)*</label>
                                <input type="number" name="turnaround_hours" min="1" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sample Type*</label>
                            <select name="sample_type" class="form-select" required>
                                <option value="blood">Blood</option>
                                <option value="urine">Urine</option>
                                <option value="saliva">Saliva</option>
                                <option value="tissue">Tissue</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preparation Instructions</label>
                            <textarea name="preparation_instructions" class="form-control" rows="2"
                                      placeholder="Fasting required, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Test Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal setup for editing
        document.getElementById('testTypeModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const modal = this;
            
            if (button.hasAttribute('data-test-id')) {
                const testData = JSON.parse(button.getAttribute('data-test-data'));
                modal.querySelector('#modalTitle').textContent = `Edit ${testData.name}`;
                modal.querySelector('#testTypeId').value = testData.id;
                
                // Fill form
                const form = modal.querySelector('form');
                form.querySelector('[name="name"]').value = testData.name;
                form.querySelector('[name="description"]').value = testData.description || '';
                form.querySelector('[name="price"]').value = testData.price;
                form.querySelector('[name="turnaround_hours"]').value = testData.turnaround_hours;
                form.querySelector('[name="sample_type"]').value = testData.sample_type;
                form.querySelector('[name="preparation_instructions"]').value = 
                    testData.preparation_instructions || '';
            } else {
                // Reset for new test
                modal.querySelector('#modalTitle').textContent = 'Add New Test Type';
                modal.querySelector('#testTypeId').value = '';
                modal.querySelector('form').reset();
            }
        });
    </script>
</body>
</html>