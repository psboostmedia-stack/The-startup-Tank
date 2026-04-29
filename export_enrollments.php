<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    exit('Unauthorized');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=startup_tank_2_0_enrollments_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Full Name', 'Class', 'Stream', 'Phone', 'Email', 'Idea', 'Enrolled At']);

$stmt = $pdo->query("SELECT * FROM enrollments ORDER BY created_at DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['full_name'],
        $row['class_year'],
        $row['stream'],
        $row['phone'],
        $row['email'],
        $row['idea'],
        $row['created_at']
    ]);
}
fclose($output);
?>
