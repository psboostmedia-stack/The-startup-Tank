<?php
include 'db.php';

try {
    // Add email_verified and google_id columns to students table
    $pdo->exec("ALTER TABLE students ADD COLUMN email_verified TINYINT(1) DEFAULT 0");
    $pdo->exec("ALTER TABLE students ADD COLUMN google_id VARCHAR(255) DEFAULT NULL UNIQUE");
    echo "Database migrated successfully: Added email_verified and google_id columns.";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage();
}
?>
