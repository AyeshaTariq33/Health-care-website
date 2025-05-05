<?php
session_start();
// Redirect logged-in users if needed
if(isset($_SESSION['user_role'])) {
    header("Location: panels/" . $_SESSION['user_role'] . "/dashboard.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="about-page">
    <div class="container py-5">
        <!-- Hero Section -->
        <section class="about-hero text-center mb-5">
            <h1 class="display-4 text-primary mb-3">About MediCare+</h1>
            <p class="lead">Compassionate Care Since 2010</p>
        </section>

        <!-- Main Content -->
        <div class="row g-5">
            <!-- Our Story -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="mb-4"><i class="fas fa-history me-2"></i>Our Story</h2>
                        <p class="lead">Founded in 2010, MediCare+ has grown from a single clinic to a network of healthcare centers serving over 50,000 patients annually.</p>
                        <p>We combine cutting-edge medical technology with personalized care to deliver exceptional health services.</p>
                        <img src="assets/img/clinic-interior.jpg" alt="Our Clinic" class="img-fluid rounded-3 mt-3">
                    </div>
                </div>
            </div>

            <!-- Mission & Values -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="mb-4"><i class="fas fa-heartbeat me-2"></i>Our Mission</h2>
                        <div class="mission-statement bg-light p-4 rounded-3 mb-4">
                            <blockquote class="blockquote">
                                <p class="mb-0">"To provide accessible, compassionate healthcare that puts patients first."</p>
                            </blockquote>
                        </div>
                        
                        <h3 class="mt-4">Core Values</h3>
                        <div class="list-group">
                            <div class="list-group-item border-0">
                                <i class="fas fa-user-md text-primary me-3"></i>
                                <strong>Patient-Centered Care:</strong> Your health needs come first
                            </div>
                            <div class="list-group-item border-0">
                                <i class="fas fa-flask text-primary me-3"></i>
                                <strong>Medical Excellence:</strong> Board-certified specialists
                            </div>
                            <div class="list-group-item border-0">
                                <i class="fas fa-clock text-primary me-3"></i>
                                <strong>24/7 Availability:</strong> Always here when you need us
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <section class="team-section mt-5">
            <h2 class="text-center mb-5">Meet Our Specialists</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <img src="assets/img/doctor1.jpg" class="card-img-top" alt="Dr. Sarah Johnson">
                        <div class="card-body text-center">
                            <h5 class="card-title">Dr. Sarah Johnson</h5>
                            <p class="card-text text-muted">Chief Medical Officer</p>
                        </div>
                    </div>
                </div>
                <!-- Add 3 more team members similarly -->
            </div>
        </section>
    </div>
</div>

<?php include 'includes/footer.php'; ?>