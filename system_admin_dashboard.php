<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'system_admin') {
    header('Location: login.php');
    exit;
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $userId = intval($_GET['delete_user']);
    $stmt = $conn->prepare("DELETE FROM users WHERE userID = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

// Handle schedule deletion
if (isset($_GET['delete_schedule'])) {
    $scheduleId = intval($_GET['delete_schedule']);

    // Get the filename to identify related schedules
    $getFilename = $conn->prepare("SELECT filename FROM uploaded_schedules WHERE id = ?");
    $getFilename->bind_param("i", $scheduleId);
    $getFilename->execute();
    $result = $getFilename->get_result();
    $filename = '';
    if ($row = $result->fetch_assoc()) {
        $filename = $row['filename'];
    }
    $getFilename->close();

    if ($filename) {
        // Delete related entries in schedules
        $deleteSchedules = $conn->prepare("DELETE FROM schedules WHERE classroom IN (
            SELECT classroom FROM schedules WHERE createdAt IN (
                SELECT uploaded_at FROM uploaded_schedules WHERE filename = ?
            )
        )");
        $deleteSchedules->bind_param("s", $filename);
        $deleteSchedules->execute();
        $deleteSchedules->close();
    }

    // Delete from uploaded_schedules
    $deleteUploaded = $conn->prepare("DELETE FROM uploaded_schedules WHERE id = ?");
    $deleteUploaded->bind_param("i", $scheduleId);
    $deleteUploaded->execute();
    $deleteUploaded->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Admin Dashboard</title>
</head>
<body>

    <h2>Registered Users</h2>
    <table border="1" cellpadding="6">
        <tr>
            <th>User ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
        <?php
        $users = $conn->query("SELECT userID, email, role FROM users");
        while ($user = $users->fetch_assoc()) {
            echo "<tr>
                <td>{$user['userID']}</td>
                <td>{$user['email']}</td>
                <td>{$user['role']}</td>
                <td><a href='?delete_user={$user['userID']}' onclick='return confirm(\"Delete this user?\")'>Delete</a></td>
            </tr>";
        }
        ?>
    </table>

    <h2>Uploaded Schedules</h2>
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Filename</th>
            <th>Uploaded By</th>
            <th>Uploaded At</th>
            <th>Action</th>
        </tr>
        <?php
        $schedules = $conn->query("SELECT * FROM uploaded_schedules ORDER BY uploaded_at DESC");
        while ($row = $schedules->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['filename']}</td>
                <td>{$row['uploaded_by']}</td>
                <td>{$row['uploaded_at']}</td>
                <td><a href='?delete_schedule={$row['id']}' onclick='return confirm(\"Delete this schedule?\")'>Delete</a></td>
            </tr>";
        }
        ?>
    </table>

    <br>
    <a href="logout.php">Logout</a>

</body>
</html>
