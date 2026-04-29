<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $student_type = $_POST['student_type'];
    $institution = $_POST['institution'];
    $class_year = $_POST['class_year'];
    $city = $_POST['city'];
    $idea = $_POST['idea'];

    try {
        // Migration: Ensure status column exists
        try {
            $pdo->query("SELECT status FROM students LIMIT 1");
        } catch (Exception $m) {
            $pdo->exec("ALTER TABLE students ADD COLUMN status VARCHAR(20) DEFAULT 'pending'");
        }

        $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, password, phone, student_type, institution, class_year, city, idea, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
        $stmt->execute([$first_name, $last_name, $email, $password, $phone, $student_type, $institution, $class_year, $city, $idea]);
        
        header("Location: login.php?registered=1");
        exit();
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            echo "Email already exists! <a href='index.php'>Go back</a>";
        } else {
            echo "Registration failed: " . $e->getMessage();
        }
    }
}
?>
