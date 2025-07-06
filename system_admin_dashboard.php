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

    $checkRole = $conn->prepare("SELECT role FROM users WHERE userID = ?");
    $checkRole->bind_param("i", $userId);
    $checkRole->execute();
    $result = $checkRole->get_result();
    $role = $result->fetch_assoc()['role'] ?? '';
    $checkRole->close();

    if ($role !== 'system_admin') {
        $stmt = $conn->prepare("DELETE FROM users WHERE userID = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        $userMessage = "User deleted.";
    } else {
        $userMessage = "Cannot delete a system admin.";
    }
}

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'], $_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
    $newRole = $_POST['change_role'];

    $checkCurrent = $conn->prepare("SELECT role FROM users WHERE userID = ?");
    $checkCurrent->bind_param("i", $userId);
    $checkCurrent->execute();
    $result = $checkCurrent->get_result();
    $currentRole = $result->fetch_assoc()['role'] ?? '';
    $checkCurrent->close();

    if ($currentRole !== 'system_admin') {
        $update = $conn->prepare("UPDATE users SET role = ? WHERE userID = ?");
        $update->bind_param("si", $newRole, $userId);
        $update->execute();
        $update->close();
        $userMessage = "User role updated.";
    } else {
        $userMessage = "Cannot change role of a system admin.";
    }
}

// Handle schedule deletion
if (isset($_GET['delete_schedule'])) {
    $scheduleId = intval($_GET['delete_schedule']);

    $getFilename = $conn->prepare("SELECT filename FROM uploaded_schedules WHERE id = ?");
    $getFilename->bind_param("i", $scheduleId);
    $getFilename->execute();
    $result = $getFilename->get_result();
    $filename = $result->fetch_assoc()['filename'] ?? '';
    $getFilename->close();

    if ($filename) {
        $deleteSchedules = $conn->prepare("DELETE FROM schedules WHERE classroom IN (
            SELECT classroom FROM schedules WHERE createdAt IN (
                SELECT uploaded_at FROM uploaded_schedules WHERE filename = ?
            )
        )");
        $deleteSchedules->bind_param("s", $filename);
        $deleteSchedules->execute();
        $deleteSchedules->close();
    }

    $deleteUploaded = $conn->prepare("DELETE FROM uploaded_schedules WHERE id = ?");
    $deleteUploaded->bind_param("i", $scheduleId);
    $deleteUploaded->execute();
    $deleteUploaded->close();
    $scheduleMessage = "Schedule and related entries deleted.";
}

// --- Analytics Data --- //
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalSchedules = $conn->query("SELECT COUNT(*) as count FROM uploaded_schedules")->fetch_assoc()['count'];

$roles = [];
$roleCounts = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
while ($row = $roleCounts->fetch_assoc()) {
    $roles[$row['role']] = $row['count'];
}

$classrooms = [];
$classroomData = $conn->query("SELECT classroom, COUNT(*) as count FROM schedules GROUP BY classroom ORDER BY count DESC LIMIT 5");
while ($row = $classroomData->fetch_assoc()) {
    $classrooms[$row['classroom']] = $row['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>

<h2>System Admin Dashboard</h2>
<p><a href="my_profile.php">My Profile</a></p>

<?php if (!empty($userMessage)) echo "<p>$userMessage</p>"; ?>
<?php if (!empty($scheduleMessage)) echo "<p>$scheduleMessage</p>"; ?>

<div id="reportSection">
    <h3>Analytics</h3>
    <p>Total Users: <?= $totalUsers ?></p>
    <p>Total Uploaded Schedules: <?= $totalSchedules ?></p>

    <canvas id="roleChart" width="400" height="200"></canvas>
    <canvas id="classroomChart" width="400" height="200"></canvas>
</div>

<button onclick="generatePDF()">Download Report (PDF)</button>

<h3>Registered Users</h3>
<table border="1" cellpadding="6">
<tr><th>User ID</th><th>Email</th><th>Role</th><th>Action</th></tr>
<?php
$users = $conn->query("SELECT userID, email, role FROM users");
while ($user = $users->fetch_assoc()): ?>
<tr>
    <td><?= $user['userID'] ?></td>
    <td><?= htmlspecialchars($user['email']) ?></td>
    <td>
        <?php if ($user['role'] !== 'system_admin'): ?>
            <form method="POST" style="display:inline">
                <input type="hidden" name="user_id" value="<?= $user['userID'] ?>">
                <select name="change_role" onchange="this.form.submit()">
                    <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </form>
        <?php else: ?>
            <?= $user['role'] ?>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($user['role'] !== 'system_admin'): ?>
            <a href="?delete_user=<?= $user['userID'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
        <?php else: ?>
            Protected
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

<h3>Uploaded Schedules</h3>
<table border="1" cellpadding="6">
<tr><th>ID</th><th>Filename</th><th>Uploaded By</th><th>Uploaded At</th><th>Action</th></tr>
<?php
$schedules = $conn->query("SELECT * FROM uploaded_schedules ORDER BY uploaded_at DESC");
while ($row = $schedules->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['filename']) ?></td>
    <td><?= htmlspecialchars($row['uploaded_by']) ?></td>
    <td><?= $row['uploaded_at'] ?></td>
    <td><a href="?delete_schedule=<?= $row['id'] ?>" onclick="return confirm('Delete this schedule?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>

<a href="logout.php">Logout</a>

<script>
const roleData = <?= json_encode($roles) ?>;
const classrooms = <?= json_encode($classrooms) ?>;

new Chart(document.getElementById('roleChart'), {
    type: 'pie',
    data: {
        labels: Object.keys(roleData),
        datasets: [{
            data: Object.values(roleData),
            backgroundColor: ['#7A0019', '#FFD700', '#cccccc']
        }]
    }
});

new Chart(document.getElementById('classroomChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(classrooms),
        datasets: [{
            label: 'Usage Count',
            data: Object.values(classrooms),
            backgroundColor: '#7A0019'
        }]
    }
});

function generatePDF() {
    const element = document.getElementById("reportSection");
    html2pdf().from(element).save("dashboard_report.pdf");
}
</script>

</body>
</html>
