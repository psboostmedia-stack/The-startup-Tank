<?php
include 'db.php';
try {
    // Add stream column if it doesn't exist
    $pdo->exec("ALTER TABLE students ADD COLUMN stream VARCHAR(100) DEFAULT NULL AFTER class_year;");
    echo "Stream column added successfully.";
} catch (Exception $e) {
    echo "Error or column already exists: " . $e->getMessage();
}
?>
