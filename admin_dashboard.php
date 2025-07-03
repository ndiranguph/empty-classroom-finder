<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle deletion of an upload (and cascade delete its schedules)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM uploaded_schedules WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit;
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['scheduleFile'])) {
    $filename = basename($_FILES['scheduleFile']['name']);
    $tmpFile = $_FILES['scheduleFile']['tmp_name'];
    $uploadedBy = $_SESSION['email'];

    if (($handle = fopen($tmpFile, 'r')) !== false) {
        // 1. Insert into uploaded_schedules
        $insertUpload = $conn->prepare("INSERT INTO uploaded_schedules (filename, uploaded_by) VALUES (?, ?)");
        $insertUpload->bind_param("ss", $filename, $uploadedBy);

        if ($insertUpload->execute()) {
            $uploadId = $insertUpload->insert_id;
            $insertUpload->close();

            // 2. Parse CSV and insert each schedule with the correct upload_id
            fgetcsv($handle); // skip header

            $insertSchedule = $conn->prepare("INSERT INTO schedules (upload_id, classroom, dayOfWeek, startTime, endTime, courseCode, lecturer)
                                              VALUES (?, ?, ?, ?, ?, ?, ?)");

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) < 6) continue;
                list($classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer) = array_map('trim', $data);
                $insertSchedule->bind_param("issssss", $uploadId, $classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer);
                $insertSchedule->execute();
            }

            fclose($handle);
            $insertSchedule->close();
        } else {
            die("Error inserting upload record: " . $insertUpload->error);
        }
    }

    header("Location: admin_dashboard.php");
    exit;
}

// Fetch uploaded schedule info
$result = $conn->query("SELECT * FROM uploaded_schedules ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Admin Dashboard</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Upload Class Schedule (CSV):</label>
        <input type="file" name="scheduleFile" accept=".csv" required>
        <button type="submit">Upload</button>
    </form>

    <p><a href="schedule_template.xlsx" download>Download Schedule Template</a></p>

    <h3>Uploaded Schedules</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Filename</th>
            <th>Uploaded By</th>
            <th>Uploaded At</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['filename']) ?></td>
                <td><?= htmlspecialchars($row['uploaded_by']) ?></td>
                <td><?= $row['uploaded_at'] ?></td>
                <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this schedule and all its classes?');">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
