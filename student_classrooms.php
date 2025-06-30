<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

// Set timezone
$now = new DateTime('now', new DateTimeZone('Africa/Nairobi'));
$currentTime = $now->format('H:i:s');
$currentDay  = $now->format('l');

// Handle search input or use current time
$searchDay  = $_GET['day'] ?? $currentDay;
$searchFrom = $_GET['from'] ?? $currentTime;
$searchTo   = $_GET['to'] ?? null;

// Get all classrooms
$allRooms = [];
$result = $conn->query("SELECT roomName, capacity, building FROM classrooms");
while ($row = $result->fetch_assoc()) {
    $allRooms[trim($row['roomName'])] = $row;
}

// Get occupied rooms
if ($searchTo) {
    $stmt = $conn->prepare("SELECT DISTINCT classroom FROM schedules 
                            WHERE dayOfWeek = ? 
                            AND (
                                (TIME(startTime) <= TIME(?) AND TIME(endTime) > TIME(?)) OR
                                (TIME(startTime) < TIME(?) AND TIME(endTime) >= TIME(?)) OR
                                (TIME(startTime) >= TIME(?) AND TIME(endTime) <= TIME(?))
                            )");
    $stmt->bind_param("sssssss", $searchDay, $searchFrom, $searchFrom, $searchTo, $searchTo, $searchFrom, $searchTo);
} else {
    $stmt = $conn->prepare("SELECT DISTINCT classroom FROM schedules 
                            WHERE dayOfWeek = ? 
                            AND TIME(?) >= TIME(startTime) 
                            AND TIME(?) < TIME(endTime)");
    $stmt->bind_param("sss", $searchDay, $searchFrom, $searchFrom);
}

$stmt->execute();
$res = $stmt->get_result();

$occupied = [];
while ($row = $res->fetch_assoc()) {
    $occupied[] = trim(preg_replace('/\s+/', '', $row['classroom']));
}

// Filter available rooms
$availableRooms = [];
foreach ($allRooms as $roomName => $details) {
    $normalized = str_replace(' ', '', $roomName);
    if (!in_array($normalized, $occupied)) {
        $availableRooms[$roomName] = $details;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Classrooms</title>
</head>
<body>
    <h2>Available Classrooms</h2>

    <form method="GET" action="student_classrooms.php">
        <label>Day of Week:</label>
        <select name="day" required>
            <?php
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            foreach ($days as $day) {
                echo "<option value=\"$day\" " . ($searchDay === $day ? 'selected' : '') . ">$day</option>";
            }
            ?>
        </select>

        <label>Start Time:</label>
        <select name="from" required>
            <?php
            for ($hour = 8; $hour <= 17; $hour++) {
                $formatted = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':15';
                $selected = (substr($searchFrom, 0, 5) === $formatted) ? 'selected' : '';
                echo "<option value=\"$formatted\" $selected>$formatted</option>";
            }
            ?>
        </select>

        <label>End Time (optional):</label>
        <select name="to">
            <option value="">--</option>
            <?php
            for ($hour = 8; $hour <= 17; $hour++) {
                $formatted = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':15';
                $selected = ($searchTo && substr($searchTo, 0, 5) === $formatted) ? 'selected' : '';
                echo "<option value=\"$formatted\" $selected>$formatted</option>";
            }
            ?>
        </select>

        <button type="submit">Check Availability</button>
    </form>

    <h4>Showing available classrooms on <strong><?= htmlspecialchars($searchDay) ?></strong> at 
        <strong><?= htmlspecialchars(substr($searchFrom, 0, 5)) ?></strong>
        <?php if ($searchTo): ?>
            to <strong><?= htmlspecialchars(substr($searchTo, 0, 5)) ?></strong>
        <?php endif; ?>
    </h4>

    <?php if (empty($availableRooms)): ?>
        <p>No classrooms available at this time.</p>
    <?php else: ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>Room Name</th>
                <th>Capacity</th>
                <th>Building</th>
            </tr>
            <?php foreach ($availableRooms as $roomName => $room): ?>
                <tr>
                    <td><?= htmlspecialchars($roomName) ?></td>
                    <td><?= $room['capacity'] ?></td>
                    <td><?= $room['building'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
