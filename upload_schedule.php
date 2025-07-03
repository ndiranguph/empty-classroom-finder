<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$adminEmail = $_SESSION['email'];
$message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    $file = $_FILES['csvFile'];

    if ($file['error'] === 0 && pathinfo($file['name'], PATHINFO_EXTENSION) === 'csv') {
        $fileTmpPath = $file['tmp_name'];
        $handle = fopen($fileTmpPath, 'r');

        if ($handle !== false) {
            // Clear existing schedules
            $conn->query("DELETE FROM schedules");

            // Skip header
            fgetcsv($handle);

            $stmt = $conn->prepare("INSERT INTO schedules (classroom, dayOfWeek, startTime, endTime, courseCode, lecturer, createdAt) VALUES (?, ?, ?, ?, ?, ?, NOW())");

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $classroom = trim(str_replace(' ', '', $data[0]));
                $day       = trim($data[1]);
                $start     = trim($data[2]);
                $end       = trim($data[3]);
                $code      = trim($data[4]);
                $lecturer  = trim($data[5]);

                $stmt->bind_param("ssssss", $classroom, $day, $start, $end, $code, $lecturer);
                $stmt->execute();
            }

            fclose($handle);

            // Save info about the uploaded file
            $filename = $file['name'];
            $insertLog = $conn->prepare("INSERT INTO uploaded_schedules (filename, uploaded_by, uploaded_at) VALUES (?, ?, NOW())");
            $insertLog->bind_param("ss", $filename, $adminEmail);
            $insertLog->execute();

            $message = "Schedule uploaded successfully.";
        } else {
            $message = "Failed to read the uploaded file.";
        }
    } else {
        $message = "Invalid file. Please upload a CSV file.";
    }
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);

    // Delete from log
    $conn->query("DELETE FROM uploaded_schedules WHERE id = $deleteId");

    // Also clear the actual schedules (if desired; optional logic)
    $conn->query("DELETE FROM schedules");

    header("Location: upload_schedules.php");
    exit;
}

// Fetch uploaded schedules
$schedules = $conn->query("SELECT * FROM uploaded_schedules ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Class Schedule</title>
</head>
<body>
    <h2>Upload Class Schedule (CSV)</h2>

    <?php if ($message): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="csvFile" accept=".csv" required>
        <button type="submit">Upload</button>
    </form>

    <br>
    <a href="index.php">Back to Dashboard</a>
    <br><br>

    <h3>Uploaded Schedules</h3>
    <table border="1" cellpadding="6">
        <tr>
            <th>Filename</th>
            <th>Uploaded By</th>
            <th>Uploaded At</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $schedules->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['filename']) ?></td>
                <td><?= htmlspecialchars($row['uploaded_by']) ?></td>
                <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
                <td><a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this schedule?')">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
