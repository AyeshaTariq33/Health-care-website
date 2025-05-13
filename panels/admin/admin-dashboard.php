<?php
session_start();
if ($_SESSION['user_role'] !== 'admin') header("Location: /login.php");

$stats = [
    'patients' => $pdo->query("SELECT COUNT(*) FROM user WHERE role = 'patient'")->fetchColumn(),
    'prescriptions' => $pdo->query("SELECT COUNT(*) FROM prescriptions")->fetchColumn(),
    'lab_orders' => $pdo->query("SELECT COUNT(*) FROM lab_orders")->fetchColumn()
];
?>

<!-- Simple stats cards -->
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5>Patients</h5>
                <p><?= $stats['patients'] ?></p>
            </div>
        </div>
    </div>
    <!-- More stats cards -->
</div>