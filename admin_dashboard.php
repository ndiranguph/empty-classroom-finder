<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// --- DELETE SCHEDULE AND CASCADE DELETE ENTRIES ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];

    // Delete related schedules first
    $stmt1 = $conn->prepare("DELETE FROM schedules WHERE upload_id = ?");
    $stmt1->bind_param("i", $deleteId);
    $stmt1->execute();
    $stmt1->close();

    // Delete upload record
    $stmt2 = $conn->prepare("DELETE FROM uploaded_schedules WHERE id = ?");
    $stmt2->bind_param("i", $deleteId);
    $stmt2->execute();
    $stmt2->close();

    header("Location: admin_dashboard.php");
    exit;
}

// --- UPDATE EXISTING UPLOAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'], $_FILES['scheduleFile'])) {
    $updateId = (int)$_POST['update_id'];
    $tmpFile = $_FILES['scheduleFile']['tmp_name'];

    if (($handle = fopen($tmpFile, 'r')) !== false) {
        // Delete old schedules
        $delStmt = $conn->prepare("DELETE FROM schedules WHERE upload_id = ?");
        $delStmt->bind_param("i", $updateId);
        $delStmt->execute();
        $delStmt->close();

        fgetcsv($handle); // Skip header
        $insertStmt = $conn->prepare("INSERT INTO schedules (upload_id, classroom, dayOfWeek, startTime, endTime, courseCode, lecturer)
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 6) continue;
            list($classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer) = array_map('trim', $data);
            $insertStmt->bind_param("issssss", $updateId, $classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer);
            $insertStmt->execute();
        }

        fclose($handle);
        $insertStmt->close();
    }

    header("Location: admin_dashboard.php");
    exit;
}

// --- HANDLE NEW UPLOAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['scheduleFile']) && !isset($_POST['update_id'])) {
    $filename = basename($_FILES['scheduleFile']['name']);
    $tmpFile = $_FILES['scheduleFile']['tmp_name'];
    $uploadedBy = $_SESSION['email'];

    if (($handle = fopen($tmpFile, 'r')) !== false) {
        $uploadStmt = $conn->prepare("INSERT INTO uploaded_schedules (filename, uploaded_by) VALUES (?, ?)");
        $uploadStmt->bind_param("ss", $filename, $uploadedBy);

        if ($uploadStmt->execute()) {
            $uploadId = $uploadStmt->insert_id;
            $uploadStmt->close();

            fgetcsv($handle); // Skip header
            $insertStmt = $conn->prepare("INSERT INTO schedules (upload_id, classroom, dayOfWeek, startTime, endTime, courseCode, lecturer)
                                          VALUES (?, ?, ?, ?, ?, ?, ?)");

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) < 6) continue;
                list($classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer) = array_map('trim', $data);
                $insertStmt->bind_param("issssss", $uploadId, $classroom, $dayOfWeek, $startTime, $endTime, $courseCode, $lecturer);
                $insertStmt->execute();
            }

            fclose($handle);
            $insertStmt->close();
        } else {
            die("Failed to insert upload record: " . $uploadStmt->error);
        }
    }

    header("Location: admin_dashboard.php");
    exit;
}

// --- FETCH EXISTING UPLOADS ---
$uploads = $conn->query("SELECT * FROM uploaded_schedules ORDER BY uploaded_at DESC");
$updateId = isset($_GET['update']) && is_numeric($_GET['update']) ? (int)$_GET['update'] : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Welcome, Admin</h2>
    <p><a href="my_profile.php">My Profile</a></p>


    <h3>Upload New Class Schedule</h3>
    <form method="POST" enctype="multipart/form-data">
        <label>Select CSV File:</label>
        <input type="file" name="scheduleFile" accept=".csv" required>
        <button type="submit">Upload</button>
    </form>

    <p><a href="schedule_template.xlsx" download>Download Schedule Template</a></p>

    <?php if ($updateId): ?>
        <h3>Update Schedule ID <?= $updateId ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_id" value="<?= $updateId ?>">
            <label>Upload New CSV File:</label>
            <input type="file" name="scheduleFile" accept=".csv" required>
            <button type="submit">Update Schedule</button>
        </form>
    <?php endif; ?>

    <h3>Uploaded Schedules</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Filename</th>
            <th>Uploaded By</th>
            <th>Uploaded At</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $uploads->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['filename']) ?></td>
                <td><?= htmlspecialchars($row['uploaded_by']) ?></td>
                <td><?= $row['uploaded_at'] ?></td>
                <td>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this schedule and all related entries?');">Delete</a> |
                    <a href="?update=<?= $row['id'] ?>">Update</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
