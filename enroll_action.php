<?php
session_start();
include 'db.php';

// Quick migration check
try {
    $pdo->query("SELECT stream FROM students LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE students ADD COLUMN stream VARCHAR(100) DEFAULT NULL AFTER class_year");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password_plain = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'];
    
    // Values from the updated registration form
    $class_year = $_POST['class_year'] ?? 'Not Specified';
    $stream = $_POST['stream'] ?? 'Not Specified';
    $institution = $_POST['institution'] ?? 'Not Specified';
    $city = $_POST['city'] ?? 'Not Specified';
    $idea = $_POST['idea'] ?? '';
    $student_type = $_POST['student_type'] ?? 'college';

    // Password confirmation check
    if ($password_plain !== $confirm_password) {
        die("Passwords do not match! <a href='index.php'>Go back</a>");
    }
    
    $password = password_hash($password_plain, PASSWORD_DEFAULT);
    
    try {
        // Migration: Ensure city column exists
        try {
            $pdo->query("SELECT city FROM students LIMIT 1");
        } catch (Exception $m) {
            $pdo->exec("ALTER TABLE students ADD COLUMN city VARCHAR(100) DEFAULT NULL AFTER class_year");
        }
        // Ensure new columns exist for future use
        try {
            $pdo->query("SELECT email_verified FROM students LIMIT 1");
        } catch (Exception $e) {
            $pdo->exec("ALTER TABLE students ADD COLUMN email_verified TINYINT(1) DEFAULT 0, ADD COLUMN google_id VARCHAR(255) DEFAULT NULL UNIQUE");
        }

        // Ensure status column exists
        try {
            $pdo->query("SELECT status FROM students LIMIT 1");
        } catch (Exception $e) {
            $pdo->exec("ALTER TABLE students ADD COLUMN status VARCHAR(20) DEFAULT 'pending'");
        }

        $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, password, phone, student_type, stream, institution, class_year, city, idea, email_verified, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'approved')");
        $stmt->execute([$first_name, $last_name, $email, $password, $phone, $student_type, $stream, $institution, $class_year, $city, $idea]);
        
        $_SESSION['registered_success'] = true;
        header("Location: login.php");
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
