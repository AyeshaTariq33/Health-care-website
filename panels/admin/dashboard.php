<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Admin Dashboard | MediCare+</title>
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
        
        /* Admin-specific styling */
        .admin-card {
            border-left: 4px solid #9b59b6;
        }
        
        .critical {
            background-color: #fff5f5;
            border-left: 4px solid #e74c3c !important;
        }
        
        .data-table {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container py-4">
        <!-- Admin Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
            <div>
                <h2 class="text-primary mb-1">Admin Dashboard</h2>
                <p class="text-muted mb-0"><?= date('l, F j, Y') ?> | System Overview</p>
            </div>
            <div>
                <a href="../../logout.php" class="btn btn-danger me-2">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- System Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Total Users</h6>
                        <h2 class="text-primary">1,248</h2>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: 72%"></div>
                        </div>
                        <small class="text-muted">+12% this month</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Active Staff</h6>
                        <h2 class="text-primary">84</h2>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-info" style="width: 65%"></div>
                        </div>
                        <small class="text-muted">5 on leave</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Today's Appointments</h6>
                        <h2 class="text-primary">217</h2>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-warning" style="width: 85%"></div>
                        </div>
                        <small class="text-muted">92% completed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">System Alerts</h6>
                        <h2 class="text-primary">3</h2>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-danger" style="width: 30%"></div>
                        </div>
                        <small class="text-danger">1 critical</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Panels -->
        <div class="row">
            <!-- User Management -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0"><i class="fas fa-users-cog text-primary me-2"></i>User Management</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i> Add User
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table data-table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Last Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Dr. Sarah Johnson</td>
                                        <td>s.johnson@medicare.com</td>
                                        <td><span class="badge bg-info">Doctor</span></td>
                                        <td>2 hours ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="critical">
                                        <td>Robert Chen</td>
                                        <td>r.chen@medicare.com</td>
                                        <td><span class="badge bg-danger">Locked</span></td>
                                        <td>5 days ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- More users... -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Admin Tools -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-tools text-warning me-2"></i>Admin Tools</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary text-start">
                                <i class="fas fa-database me-2"></i> Database Backup
                            </button>
                            <button class="btn btn-outline-success text-start">
                                <i class="fas fa-user-shield me-2"></i> Permission Management
                            </button>
                            <button class="btn btn-outline-info text-start">
                                <i class="fas fa-chart-line me-2"></i> System Analytics
                            </button>
                            <button class="btn btn-outline-warning text-start">
                                <i class="fas fa-bell me-2"></i> Notification Center
                            </button>
                            <button class="btn btn-outline-danger text-start">
                                <i class="fas fa-exclamation-triangle me-2"></i> Emergency Mode
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