<?php
session_start();
include 'db.php';

// Quick migration check
try {
    $pdo->query("SELECT 1 FROM enrollments LIMIT 1");
} catch (Exception $e) {
    try {
        $pdo->exec("CREATE TABLE enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            class_year VARCHAR(100) NOT NULL,
            stream VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(255) NOT NULL,
            idea TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    } catch (Exception $e2) {}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'] ?? '';
    $class_year = $_POST['class_year'] ?? '';
    $stream = $_POST['stream'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $idea = $_POST['idea'] ?? '';

    try {
        $stmt = $pdo->prepare("INSERT INTO enrollments (full_name, class_year, stream, phone, email, idea) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $class_year, $stream, $phone, $email, $idea]);
        
        $_SESSION['enrollment_success'] = true;
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
