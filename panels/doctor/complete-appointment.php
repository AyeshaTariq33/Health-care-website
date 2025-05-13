<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');
    
    // Verify the appointment belongs to this doctor
    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET status = 'completed' 
        WHERE id = ? AND doctor_id = ?
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}

header("Location: manage-appointments.php");
exit;