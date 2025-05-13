<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pharmacy') {
    header("Location: ../../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Pharmacy Dashboard | MediCare+</title>
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
        
        /* Pharmacy-specific styling */
        .pharmacy-card {
            border-left: 4px solid #2ecc71;
        }
        
        .low-stock {
            background-color: #fff8f0;
            border-left: 4px solid #f39c12 !important;
        }
        
        .out-of-stock {
            background-color: #fff0f0;
        }
        
        .med-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container py-4">
        <!-- Pharmacy Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
            <div>
                <h2 class="text-primary mb-1">Pharmacy Dashboard</h2>
                <p class="text-muted mb-0"><?= date('l, F j, Y') ?> | <span class="badge bg-success">Medication Center</span></p>
            </div>
            <div>
                <a href="../../logout.php" class="btn btn-danger me-2">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- Pharmacy Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card pharmacy-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Pending Orders</h6>
                        <h2 class="text-primary">24</h2>
                        <small class="text-success">5 ready for pickup</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card pharmacy-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Today's Dispensed</h6>
                        <h2 class="text-primary">87</h2>
                        <small class="text-info">12 controlled substances</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card pharmacy-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Low Stock Items</h6>
                        <h2 class="text-primary">9</h2>
                        <small class="text-warning">3 critical</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card pharmacy-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Expiring Soon</h6>
                        <h2 class="text-primary">5</h2>
                        <small class="text-danger">This month</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pharmacy Management -->
        <div class="row">
            <!-- Medication Inventory -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0"><i class="fas fa-pills text-success me-2"></i>Medication Inventory</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-success me-2">
                                <i class="fas fa-plus me-1"></i> Add Medication
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
                                        <th>Medication</th>
                                        <th>Stock</th>
                                        <th>Category</th>
                                        <th>Expiry</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>Metformin 500mg</strong><br>
                                            <small class="text-muted">Bottle of 100</small>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" style="width: 75%">42</div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-info med-badge">Diabetes</span></td>
                                        <td>2024-12-15</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="low-stock">
                                        <td>
                                            <strong>Amoxicillin 250mg</strong><br>
                                            <small class="text-muted">Capsules</small>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-warning" style="width: 15%">3</div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-primary med-badge">Antibiotic</span></td>
                                        <td>2024-08-30</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- More medications... -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Pharmacy Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-success text-start">
                                <i class="fas fa-prescription me-2"></i> Process New Prescription
                            </button>
                            <button class="btn btn-outline-primary text-start">
                                <i class="fas fa-truck me-2"></i> Place Supplier Order
                            </button>
                            <button class="btn btn-outline-info text-start">
                                <i class="fas fa-search me-2"></i> Medication Lookup
                            </button>
                            <button class="btn btn-outline-warning text-start">
                                <i class="fas fa-exclamation-triangle me-2"></i> Report Discrepancy
                            </button>
                            <button class="btn btn-outline-danger text-start">
                                <i class="fas fa-ban me-2"></i> Recall Management
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