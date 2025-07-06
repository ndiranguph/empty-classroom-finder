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

$searchDay  = $_GET['day'] ?? $currentDay;
$searchFrom = $_GET['from'] ?? $currentTime;
$searchTo   = $_GET['to'] ?? null;

$allRooms = [];
$result = $conn->query("SELECT roomName, capacity, building FROM classrooms");
while ($row = $result->fetch_assoc()) {
    $roomName = trim(preg_replace('/\s+/', '', $row['roomName']));
    $allRooms[$roomName] = $row;
}

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

// Filter available
$availableRooms = [];
foreach ($allRooms as $normalizedName => $details) {
    if (!in_array($normalizedName, $occupied)) {
        $availableRooms[$normalizedName] = $details;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Classrooms</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #7A0019;
            --accent: #FFD700;
            --bg: #f5f5f5;
            --text: #212121;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: var(--bg);
        }

        header {
            background-color: var(--primary);
            color: white;
            padding: 1rem 2rem;
            text-align: center;
        }

        main {
            padding: 2rem;
            max-width: 900px;
            margin: auto;
        }

        h2 {
            color: var(--primary);
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        label {
            display: flex;
            flex-direction: column;
            font-weight: bold;
            flex: 1 1 200px;
        }

        select {
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: var(--primary);
            color: white;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 1.5rem;
        }

        button:hover {
            background-color: #5a0013;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary);
            color: white;
        }

        .notice {
            margin-top: 1rem;
            font-weight: bold;
        }

        footer {
            margin-top: 2rem;
            text-align: center;
        }

        @media (max-width: 600px) {
            form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Strathmore Class Finder</h1>
    </header>

    <main>
        <h2>Available Classrooms</h2>
        <p><a href="my_profile.php">My Profile</a></p>


        <form method="GET" action="student_classrooms.php">
            <label>
                Day of Week:
                <select name="day" required>
                    <?php
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                    foreach ($days as $day) {
                        echo "<option value=\"$day\" " . ($searchDay === $day ? 'selected' : '') . ">$day</option>";
                    }
                    ?>
                </select>
            </label>

            <label>
                Start Time:
                <select name="from" required>
                    <?php
                    for ($hour = 8; $hour <= 17; $hour++) {
                        $formatted = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':15';
                        $selected = (substr($searchFrom, 0, 5) === $formatted) ? 'selected' : '';
                        echo "<option value=\"$formatted\" $selected>$formatted</option>";
                    }
                    ?>
                </select>
            </label>

            <label>
                End Time (optional):
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
            </label>

            <button type="submit">Check Availability</button>
        </form>

        <div class="notice">
            Showing available classrooms on <strong><?= htmlspecialchars($searchDay) ?></strong> at 
            <strong><?= htmlspecialchars(substr($searchFrom, 0, 5)) ?></strong>
            <?php if ($searchTo): ?>
                to <strong><?= htmlspecialchars(substr($searchTo, 0, 5)) ?></strong>
            <?php endif; ?>
        </div>

        <?php if (empty($availableRooms)): ?>
            <p>No classrooms available at this time.</p>
        <?php else: ?>
            <table aria-label="Available Classrooms">
                <thead>
                    <tr>
                        <th>Room Name</th>
                        <th>Capacity</th>
                        <th>Building</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availableRooms as $room): ?>
                        <tr>
                            <td><?= htmlspecialchars($room['roomName']) ?></td>
                            <td><?= htmlspecialchars($room['capacity']) ?></td>
                            <td><?= htmlspecialchars($room['building']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <footer>
            <p><a href="logout.php">Logout</a></p>
        </footer>
    </main>
</body>
</html>
