<?php
$host = 'localhost';
$db   = 'empty_classroom_finder';  // Database name
$user = 'root';                    // MySQL username (default for XAMPP)
$pass = '';                        // MySQL password (default is empty in XAMPP)

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
