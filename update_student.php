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
    $phone = $_POST['phone'] ?? '';

    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
    
    if ($stmt->execute([$first_name, $last_name, $email, $phone, $id])) {
        header("Location: admin_dashboard.php?updated=1");
    } else {
        echo "Error updating student.";
    }
}
?>
