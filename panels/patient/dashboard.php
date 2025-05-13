<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../../includes/header.php'; ?>
    <title>Patient Dashboard | MediCare+</title>
    <style>
        /* Layout Fixes */
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
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container py-4">
        <!-- Enhanced Header -->
        <div class="dashboard-header p-4 mb-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary mb-1">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
                    <p class="text-muted mb-0"><?= date('l, F j, Y') ?></p>
                </div>
                <div class="d-flex">
                    <a href="../../logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-6">
    <a href="book-appointment.php" class="card quick-action-btn text-center py-3 text-decoration-none">
        <div class="card-body">
            <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h6 class="mb-0">Book Appointments</h6>
        </div>
    </a>
</div>
            <div class="col-6 col-md-6">
                <a href="view-appointments.php" class="card quick-action-btn text-center py-3 text-decoration-none">
                    <div class="card-body">
                        <div class="feature-icon bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-prescription"></i>
                        </div>
                        <h6 class="mb-0">View Appointments</h6>
                    </div>
                </a>
    </div>
        </div>

        <!-- Recent Prescriptions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-prescription-bottle text-success me-2"></i>Current Medications</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Metformin 500mg
                                <span class="badge bg-light text-dark">Twice Daily</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Lisinopril 10mg
                                <span class="badge bg-light text-dark">Once Daily</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-heartbeat text-danger me-2"></i>Health Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Last Blood Pressure</h6>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%">120/80 mmHg</div>
                            </div>
                        </div>
                        <div>
                            <h6>Last Cholesterol</h6>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 85%">185 mg/dL</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>