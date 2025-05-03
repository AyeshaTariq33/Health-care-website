<?php 
session_start();
if(!isset($_SESSION['patient_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<?php include '../../includes/header.php'; ?>
<link href="../../assets/css/patient.css" rel="stylesheet">

<div class="patient-dashboard">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="patient-profile">
            <img src="../../assets/img/patient-avatar.jpg" class="patient-avatar" alt="Patient Avatar">
            <h3 class="patient-name"><?php echo $_SESSION['patient_name']; ?></h3>
            <p class="patient-meta">Member since <?php echo date('M Y', strtotime($_SESSION['join_date'])); ?></p>
        </div>

        <nav class="dashboard-menu">
            <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="book-appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
            <a href="prescriptions.php"><i class="fas fa-prescription-bottle-alt"></i> Prescriptions</a>
            <a href="lab-reports.php"><i class="fas fa-file-medical"></i> Lab Reports</a>
            <a href="medicine-orders.php"><i class="fas fa-pills"></i> Medicines</a>
            <a href="payments.php"><i class="fas fa-receipt"></i> Payments</a>
            <a href="settings.php"><i class="fas fa-user-cog"></i> Settings</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
        <!-- Header -->
        <div class="dashboard-header">
            <div>
                <h1 class="greeting">Good <?php echo date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening'); ?>, <?php echo $_SESSION['patient_first_name']; ?>!</h1>
                <p class="current-date"><?php echo date('l, F j, Y'); ?></p>
            </div>
            <a href="book-appointment.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Appointment
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card primary">
                <i class="fas fa-calendar-check"></i>
                <div>
                    <h3>2</h3>
                    <p>Upcoming Appointments</p>
                </div>
            </div>
            
            <div class="stat-card success">
                <i class="fas fa-prescription"></i>
                <div>
                    <h3>4</h3>
                    <p>Active Prescriptions</p>
                </div>
            </div>
            
            <div class="stat-card warning">
                <i class="fas fa-vial"></i>
                <div>
                    <h3>1</h3>
                    <p>Pending Tests</p>
                </div>
            </div>
            
            <div class="stat-card danger">
                <i class="fas fa-wallet"></i>
                <div>
                    <h3>$235</h3>
                    <p>Total Due</p>
                </div>
            </div>
        </div>

        <!-- Appointments Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-calendar-alt"></i> Recent Appointments</h2>
                <a href="appointments.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="appointments-list">
                <div class="appointment-card confirmed">
                    <div class="appointment-info">
                        <h4>Dr. Sarah Johnson</h4>
                        <div class="appointment-meta">
                            <span class="department">Cardiology</span>
                            <span class="date">Sep 15, 2023</span>
                            <span class="time">10:00 AM</span>
                        </div>
                    </div>
                    <div class="appointment-actions">
                        <button class="btn btn-icon"><i class="fas fa-info-circle"></i></button>
                        <button class="btn btn-icon danger"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                
                <!-- More appointment cards -->
            </div>
        </div>

        <!-- Prescriptions Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-prescription-bottle-alt"></i> Recent Prescriptions</h2>
                <a href="prescriptions.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="prescriptions-list">
                <div class="prescription-card">
                    <div class="prescription-info">
                        <i class="fas fa-pills"></i>
                        <div>
                            <h4>Metformin 500mg</h4>
                            <p>Dr. Michael Chen</p>
                            <span class="date">Prescribed on Sep 12, 2023</span>
                        </div>
                    </div>
                    <button class="btn btn-primary">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                
                <!-- More prescription cards -->
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>