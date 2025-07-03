<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM uploaded_schedules WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: upload_schedules.php");
    exit;
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['scheduleFile'])) {
    $filename = basename($_FILES['scheduleFile']['name']);
    $tmpFile = $_FILES['scheduleFile']['tmp_name'];
    $uploadedBy = $_SESSION['email'];

    if (($handle = fopen($tmpFile, 'r')) !== false) {
        // Insert upload record
        $stmt = $conn->prepare("INSERT INTO uploaded_schedules (filename, uploaded_by) VALUES (?, ?)");
        $stmt->bind_param("ss", $filename, $uploadedBy);
        $stmt->execute();
        $uploadId = $stmt->insert_id;
        $stmt->close();

        // Skip header
        fgetcsv($handle);

        $stmt = $conn->prepare("INSERT INTO schedules (upload_id, classroom, dayOfWeek, startTime, endTime, courseCode, lecturer) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 6) continue;
            list($classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer) = array_map('trim', $data);
            $stmt->bind_param("issssss", $uploadId, $classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer);
            $stmt->execute();
        }

        fclose($handle);
        $stmt->close();
    }

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

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="scheduleFile" accept=".csv" required>
        <button type="submit">Upload</button>
    </form>

    <p><a href="schedule_template.xlsx" download>Download CSV Template</a></p>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>

    <h3>Uploaded Schedules</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Filename</th>
            <th>Uploaded By</th>
            <th>Uploaded At</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $schedules->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['filename']) ?></td>
                <td><?= htmlspecialchars($row['uploaded_by']) ?></td>
                <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
                <td><a href="upload_schedules.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this schedule and its data?');">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
