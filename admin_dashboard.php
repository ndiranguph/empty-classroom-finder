<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $fileTmp = $_FILES['csv_file']['tmp_name'];
    $fileName = $_FILES['csv_file']['name'];
    $uploadedBy = $_SESSION['email'];

    if (($handle = fopen($fileTmp, 'r')) !== FALSE) {
        fgetcsv($handle); // skip header
        while (($data = fgetcsv($handle)) !== FALSE) {
            [$classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer] = $data;
            $stmt = $conn->prepare("INSERT INTO schedules (classroom, dayOfWeek, startTime, endTime, courseCode, lecturer) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer);
            $stmt->execute();
        }
        fclose($handle);

        $stmt = $conn->prepare("INSERT INTO uploaded_schedules (filename, uploaded_by) VALUES (?, ?)");
        $stmt->bind_param("ss", $fileName, $uploadedBy);
        $stmt->execute();
    }
    header('Location: admin_dashboard.php');
    exit;
}

if (isset($_GET['delete'])) {
    $scheduleID = $_GET['delete'];
    $conn->query("DELETE FROM schedules WHERE scheduleID = $scheduleID");
    $conn->query("DELETE FROM uploaded_schedules WHERE id = $scheduleID");
    header('Location: admin_dashboard.php');
    exit;
}

$result = $conn->query("SELECT * FROM uploaded_schedules ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html>
<head><title>Admin Dashboard</title></head>
<body>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['firstName']) ?></h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Upload Schedule (CSV):</label>
        <input type="file" name="csv_file" required>
        <button type="submit">Upload</button>
    </form>

    <h3>Uploaded Schedules</h3>
    <a href="download_template.php" style="margin-bottom: 10px; display: inline-block;">Download Schedule Template</a>
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th><th>Filename</th><th>Uploaded By</th><th>Uploaded At</th><th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['filename']) ?></td>
            <td><?= htmlspecialchars($row['uploaded_by']) ?></td>
            <td><?= $row['uploaded_at'] ?></td>
            <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this schedule?')">Delete</a></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
