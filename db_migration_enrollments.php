<?php
include 'db.php';
try {
    $sql = "CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        class_year VARCHAR(100) NOT NULL,
        stream VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(255) NOT NULL,
        idea TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Enrollments table created successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
