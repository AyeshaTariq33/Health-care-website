<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action active">Dashboard</a>
                <a href="#" class="list-group-item list-group-item-action">Appointments</a>
                <a href="#" class="list-group-item list-group-item-action">Prescriptions</a>
                <a href="#" class="list-group-item list-group-item-action">Lab Reports</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <h3>Welcome, Patient Name</h3>
            <div class="row mt-4">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">New Appointment</h5>
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Select Doctor</label>
                                    <select class="form-select">
                                        <option>Dr. Smith (Cardiology)</option>
                                        <option>Dr. Johnson (Neurology)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Appointment Date</label>
                                    <input type="date" class="form-control">
                                </div>
                                <button class="btn btn-primary">Book Now</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Recent Appointments</h5>
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action">
                                    15 Sep - Dr. Smith (Pending)
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    10 Sep - Dr. Johnson (Completed)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
