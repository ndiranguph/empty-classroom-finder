<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$now = new DateTime('now', new DateTimeZone('Africa/Nairobi'));
$currentTime = $now->format('H:i:s');
$currentDay  = $now->format('l');

$searchDay = $_GET['day'] ?? $currentDay;
$searchTime = $_GET['time'] ?? $currentTime;

$allRooms = [];
$result = $conn->query("SELECT roomName, capacity, building FROM classrooms");
while ($row = $result->fetch_assoc()) {
    $allRooms[trim($row['roomName'])] = $row;
}

$stmt = $conn->prepare("SELECT DISTINCT classroom FROM schedules 
                        WHERE dayOfWeek = ? 
                        AND TIME(?) >= TIME(startTime) 
                        AND TIME(?) < TIME(endTime)");
$stmt->bind_param("sss", $searchDay, $searchTime, $searchTime);
$stmt->execute();
$res = $stmt->get_result();

$occupied = [];
while ($row = $res->fetch_assoc()) {
    $occupied[] = trim(preg_replace('/\s+/', '', $row['classroom']));
}

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

        <label>Time (HH:MM):</label>
        <input type="time" name="time" value="<?= htmlspecialchars(substr($searchTime, 0, 5)) ?>" required>

        <button type="submit">Check Availability</button>
    </form>

    <h4>Showing available classrooms on <strong><?= htmlspecialchars($searchDay) ?></strong> at <strong><?= htmlspecialchars(substr($searchTime, 0, 5)) ?></strong></h4>

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
</body>
</html>
