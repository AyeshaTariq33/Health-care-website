<?php
session_start();
if ($_SESSION['user_role'] !== 'lab') header("Location: /login.php");

if ($_POST) {
    // Handle report upload
    move_uploaded_file($_FILES['report']['tmp_name'], "reports/{$filename}");
    header("Location: dashboard.php?success=report_uploaded");
}
?>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="report" required>
    <button type="submit">Upload Report</button>
</form>