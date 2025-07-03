<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'system_admin') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Admin Dashboard</title>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['email']) ?> (System Admin)</h2>
    <p>This page will contain analytics in the future.</p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>
