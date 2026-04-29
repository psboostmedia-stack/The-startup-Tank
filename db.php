<?php
/**
 * Database connection for The Startup Tank
 */

$host = "localhost";
$dbname = "startup_tank_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In a real environment, you might log this instead of outputting
    // die("Connection failed: " . $e->getMessage());
}
?>
