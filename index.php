<?php
session_start();

if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

// Redirect students to their dedicated view
if ($_SESSION['role'] === 'student') {
    header('Location: student_classrooms.php');
    exit;
}

require 'connection.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        ul {
            list-style: none;
            padding: 0;
            margin-top: 30px;
        }
        li {
            margin: 15px 0;
            text-align: center;
        }
        a {
            text-decoration: none;
            color: #0066cc;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        .logout {
            color: #cc0000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['email']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</h2>
        <ul>
            <li><a href="upload_schedule.php">Upload Class Schedule (CSV)</a></li>
            <li><a class="logout" href="logout.php">Logout</a></li>
        </ul>
    </div>
</body>
</html>
