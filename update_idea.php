<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idea = $_POST['idea'];

    $stmt = $pdo->prepare("UPDATE students SET idea = ? WHERE id = ?");
    if ($stmt->execute([$idea, $student_id])) {
        header("Location: profile.php?updated=1");
    } else {
        echo "Error updating idea.";
    }
}
?>
