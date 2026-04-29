<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all students
$stmt = $pdo->query("SELECT first_name, last_name, email, phone, student_type, institution, class_year, idea, source, created_at FROM students ORDER BY created_at DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$students) {
    echo "No students found to export.";
    exit();
}

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=startup_tank_students_' . date('Y-m-d') . '.csv');

// Create file handle
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, array('First Name', 'Last Name', 'Email', 'Phone', 'Type', 'Institution', 'Class/Year', 'Idea', 'Source', 'Registration Date'));

// Add student data
foreach ($students as $student) {
    fputcsv($output, $student);
}

fclose($output);
exit();
?>
