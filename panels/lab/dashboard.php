<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lab') {
    header("Location: ../../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Lab Dashboard | MediCare+</title>
    <style>
        /* Consistent dashboard styling */
        body {
            padding-top: 70px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        main {
            flex: 1;
            padding-bottom: 20px;
        }
        
        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important;
            background-attachment: fixed !important;
            margin-top: auto;
        }
        
        /* Lab-specific styling */
        .lab-card {
            border-left: 4px solid #3498db;
        }
        
        .priority-test {
            background-color: #f0f8ff;
            border-left: 4px solid #e74c3c !important;
        }
        
        .test-badge {
            font-size: 0.75rem;
        }
        
        .status-pending {
            color: #f39c12;
        }
        
        .status-completed {
            color: #2ecc71;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container py-4">
        <!-- Lab Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
            <div>
                <h2 class="text-primary mb-1">Laboratory Dashboard</h2>
                <p class="text-muted mb-0"><?= date('l, F j, Y') ?> | <span class="badge bg-info">Diagnostic Center</span></p>
            </div>
            <div>
                <a href="../../logout.php" class="btn btn-danger me-2">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- Lab Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card lab-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Tests Today</h6>
                        <h2 class="text-primary">48</h2>
                        <small class="text-success">12 completed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card lab-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Pending Tests</h6>
                        <h2 class="text-primary">26</h2>
                        <small class="text-warning">5 urgent</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card lab-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Abnormal Results</h6>
                        <h2 class="text-primary">7</h2>
                        <small class="text-danger">2 critical</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card lab-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Equipment Issues</h6>
                        <h2 class="text-primary">3</h2>
                        <small class="text-info">1 maintenance due</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lab Management -->
        <div class="row">
            <!-- Test Queue -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0"><i class="fas fa-vial text-info me-2"></i>Test Queue</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-info me-2">
                                <i class="fas fa-plus me-1"></i> New Test
                            </button>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Test ID</th>
                                        <th>Patient</th>
                                        <th>Test Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="priority-test">
                                        <td>#L-4821</td>
                                        <td>John Smith</td>
                                        <td>
                                            <span class="badge bg-danger test-badge">CBC</span>
                                            <span class="badge bg-warning test-badge">Urgent</span>
                                        </td>
                                        <td class="status-pending">
                                            <i class="fas fa-hourglass-half me-1"></i> Processing
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-exclamation"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#L-4819</td>
                                        <td>Sarah Johnson</td>
                                        <td>
                                            <span class="badge bg-primary test-badge">Lipid Panel</span>
                                        </td>
                                        <td class="status-completed">
                                            <i class="fas fa-check-circle me-1"></i> Completed
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-file-pdf"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- More test entries... -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lab Actions -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Lab Tools</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-info text-start">
                                <i class="fas fa-microscope me-2"></i> Record Test Results
                            </button>
                            <button class="btn btn-outline-primary text-start">
                                <i class="fas fa-search me-2"></i> Test Lookup
                            </button>
                            <button class="btn btn-outline-success text-start">
                                <i class="fas fa-file-export me-2"></i> Generate Reports
                            </button>
                            <button class="btn btn-outline-warning text-start">
                                <i class="fas fa-calendar-alt me-2"></i> Schedule Maintenance
                            </button>
                            <button class="btn btn-outline-danger text-start">
                                <i class="fas fa-exclamation-triangle me-2"></i> Critical Results
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>