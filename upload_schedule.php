<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

function normalizeTime($timeStr) {
    $ts = strtotime($timeStr);
    return date('H:i:s', $ts); // Enforce 24-hour format
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    $file = $_FILES['csvFile']['tmp_name'];
    $handle = fopen($file, 'r');

    // Skip header
    fgetcsv($handle);

    $stmt = $conn->prepare("INSERT INTO schedules (classroom, dayOfWeek, startTime, endTime, courseCode, lecturer) VALUES (?, ?, ?, ?, ?, ?)");

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $classroom = trim(preg_replace('/\s+/', ' ', $data[0]));
        $day = trim($data[1]);
        $start = normalizeTime($data[2]);
        $end = normalizeTime($data[3]);
        $course = trim($data[4]);
        $lecturer = trim($data[5]);

        $stmt->bind_param("ssssss", $classroom, $day, $start, $end, $course, $lecturer);
        $stmt->execute();
    }

    $stmt->close();
    fclose($handle);

    echo "<p>CSV uploaded successfully.</p>";
    echo "<p><a href='index.php'>Go back to Dashboard</a></p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Schedule</title>
</head>
<body>
    <h2>Upload Class Schedule (CSV)</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="csvFile" accept=".csv" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
