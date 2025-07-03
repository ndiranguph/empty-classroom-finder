<?php
session_start();

if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] === 'student') {
    header('Location: student_classrooms.php');
    exit;
} elseif ($_SESSION['role'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
} else {
    echo "Unauthorized access.";
    exit;
}
