<?php
session_start();
require 'connection.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

// Extract session variables
$role = $_SESSION['role'];
$firstName = $_SESSION['firstName'] ?? 'User';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Empty Classroom Finder - Dashboard</title>
</head>
<body>
    <h2>Hi, <?php echo htmlspecialchars($firstName); ?></h2>

    <?php if ($role === 'student'): ?>
        <h3>Classroom Schedule</h3>
        <p>This section will show currently available classrooms.</p>

    <?php elseif ($role === 'admin'): ?>
        <h3>Course Admin Area</h3>
        <p>Tools for managing course schedules will appear here.</p>

    <?php elseif ($role === 'system_admin'): ?>
        <h3>System Admin Dashboard</h3>
        <p>Manage classrooms, users, and timetables.</p>

    <?php else: ?>
        <p>Unknown role.</p>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
