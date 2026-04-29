<?php
include 'db.php';
try {
    $pdo->exec("ALTER TABLE students ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER password;");
    echo "Status column added successfully.";
} catch (Exception $e) {
    echo "Error or column already exists: " . $e->getMessage();
}
?>
