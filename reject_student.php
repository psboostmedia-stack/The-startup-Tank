<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("UPDATE students SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: admin_dashboard.php");
exit();
?>
