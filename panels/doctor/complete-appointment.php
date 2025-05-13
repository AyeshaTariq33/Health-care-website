<?php
session_start();
require_once '../../includes/db_connect.php';
require_once '../../includes/auth_check.php';

// Verify doctor role
if ($_SESSION['user_role'] !== 'doctor') {
    header("Location: /login.php");
    exit;
}

// Validate and sanitize input
$appointment_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($appointment_id) {
    try {
        // Verify the appointment belongs to this doctor
        $stmt = $pdo->prepare("
            UPDATE appointments 
            SET status = 'completed' 
            WHERE id = ? AND doctor_id = ?
        ");
        $stmt->execute([$appointment_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Appointment marked as completed";
        } else {
            $_SESSION['error'] = "Appointment not found or not authorized";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid appointment ID";
}

header("Location: manage-appointments.php");
exit;