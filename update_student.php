<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $institution = $_POST['institution'];
    $student_type = $_POST['student_type'];
    $class_year = $_POST['class_year'];
    $phone = $_POST['phone'] ?? '';
    $stream = $_POST['stream'] ?? '';
    $city = $_POST['city'] ?? '';
    $idea = $_POST['idea'];

    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, institution = ?, student_type = ?, class_year = ?, phone = ?, stream = ?, city = ?, idea = ? WHERE id = ?");
    
    if ($stmt->execute([$first_name, $last_name, $email, $institution, $student_type, $class_year, $phone, $stream, $city, $idea, $id])) {
        header("Location: admin_dashboard.php?updated=1");
    } else {
        echo "Error updating student.";
    }
}
?>
