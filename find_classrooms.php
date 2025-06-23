<?php
session_start();
require 'connection.php';

// Redirect if not student
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

// Default values
$availableRooms = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day = $_POST['day'];
    $time = $_POST['startTime'];

    // Step 1: Get all classrooms
    $allQuery = $conn->query("SELECT name FROM classrooms");
    $allClassrooms = [];
    while ($row = $allQuery->fetch_assoc()) {
        $allClassrooms[] = $row['name'];
    }

    // Step 2: Get all classrooms in use during that time on that day
    $stmt = $conn->prepare("
        SELECT DISTINCT classroom FROM schedules 
        WHERE dayOfWeek = ? 
        AND ? BETWEEN startTime AND endTime
    ");
    $stmt->bind_param("ss", $day, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    $occupied = [];
    while ($row = $result->fetch_assoc()) {
        $occupied[] = $row['classroom'];
    }

    // Step 3: Filter available rooms
    $availableRooms = array_diff($allClassrooms, $occupied);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Find Empty Classrooms</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f5f5f5;
        }
        .form-section, .table-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 700px;
            margin: auto;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
        }
        form {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
        }
        .no-data {
            text-align: center;
            margin-top: 20px;
            color: red;
        }
    </style>
</head>
<body>

<div class="form-section">
    <h2>Find Empty Classrooms</h2>
    <form method="POST">
        <label>Day of Week:</label>
        <select name="day" required>
            <option value="">Select Day</option>
            <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday'] as $dayOption): ?>
                <option value="<?= $dayOption ?>" <?= isset($day) && $day == $dayOption ? 'selected' : '' ?>><?= $dayOption ?></option>
            <?php endforeach; ?>
        </select>

        <label style="margin-left: 20px;">Time:</label>
        <input type="time" name="startTime" required value="<?= $time ?? '' ?>">

        <button type="submit" style="margin-left: 20px;">Search</button>
    </form>
</div>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="table-section">
        <h3>Available Classrooms at <?= htmlspecialchars($time) ?> on <?= htmlspecialchars($day) ?></h3>

        <?php if (count($availableRooms) > 0): ?>
            <table>
                <tr><th>Classroom Name</th></tr>
                <?php foreach ($availableRooms as $room): ?>
                    <tr><td><?= htmlspecialchars($room) ?></td></tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="no-data">No classrooms available at the selected time.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>
