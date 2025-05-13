<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Doctor Dashboard | MediCare+</title>
    <style>
        /* Reuse patient dashboard styles */
        body {
            padding-top: 70px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa; /* Match index.php background */
        }
        
        main {
            flex: 1;
            padding-bottom: 20px;
        }
        
        /* Footer Gradient Fix */
        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important;
            background-attachment: fixed !important;
            margin-top: auto;
        }
        
        /* Dashboard Styling */
        .dashboard-card {
            transition: all 0.3s;
            border-radius: 10px;
            height: 100%;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            font-size: 1.75rem;
        }
        
        /* Doctor-specific styling */
        .doctor-card {
            border-left: 4px solid #3498db;
        }
        
        .urgent {
            border-left: 4px solid #e74c3c !important;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container py-4">
        <!-- Doctor Dashboard Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
            <div>
                <h2 class="text-primary mb-1">Dr. <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
                <p class="text-muted mb-0"><?= date('l, F j, Y') ?> | <span class="badge bg-info">Cardiologist</span></p>
            </div>
            <div>
                <a href="../../logout.php" class="btn btn-danger me-2">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card doctor-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Today's Appointments</h6>
                        <h2 class="text-primary">14</h2>
                        <small class="text-success">+2 from yesterday</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card doctor-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Pending Consultations</h6>
                        <h2 class="text-primary">5</h2>
                        <small class="text-danger">2 urgent</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card doctor-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Prescriptions</h6>
                        <h2 class="text-primary">23</h2>
                        <small class="text-success">This week</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card doctor-card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Messages</h6>
                        <h2 class="text-primary">8</h2>
                        <small class="text-warning">3 unread</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-calendar-day text-primary me-2"></i>Today's Schedule</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <h6 class="mb-1">John Smith</h6>
                                        <p class="mb-1 text-muted">Follow-up consultation</p>
                                    </div>
                                    <div class="text-end">
                                        <strong>9:00 AM</strong><br>
                                        <span class="badge bg-light text-dark">Room 12</span>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action urgent">
                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Sarah Johnson</h6>
                                        <p class="mb-1 text-muted">Chest pain evaluation</p>
                                    </div>
                                    <div class="text-end">
                                        <strong>10:30 AM</strong><br>
                                        <span class="badge bg-danger">URGENT</span>
                                    </div>
                                </div>
                            </a>
                            <!-- More appointments... -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
<a href="manage-appointments.php" class="btn btn-outline-primary w-100 mb-2">
    <i class="fas fa-calendar-check me-2"></i> Manage Appointments
</a>
<a href="create-prescription.php" class="btn btn-outline-success w-100 mb-2">
    <i class="fas fa-file-prescription me-2"></i> Create Prescription
</a>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>