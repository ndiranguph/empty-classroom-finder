<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['email'];
$message = "";

// Get user info
$stmt = $conn->prepare("SELECT userID, firstName, lastName, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

$userId = $user['userID'];

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new !== $confirm) {
        $message = "New passwords do not match.";
    } elseif (!password_verify($current, $user['password'])) {
        $message = "Current password is incorrect.";
    } else {
        $newHashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE userID = ?");
        $update->bind_param("si", $newHashed, $userId);
        $update->execute();
        $update->close();
        $message = "Password updated successfully.";
    }
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $confirmDelete = $_POST['confirm_delete'] ?? '';

    if ($confirmDelete === 'DELETE') {
        $delete = $conn->prepare("DELETE FROM users WHERE userID = ?");
        $delete->bind_param("i", $userId);
        $delete->execute();
        $delete->close();

        session_destroy();
        header('Location: login.php');
        exit;
    } else {
        $message = "You must type DELETE exactly to confirm account deletion.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
</head>
<body>

<h2>My Profile</h2>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<table border="1" cellpadding="6">
    <tr>
        <th>First Name</th>
        <td><?= htmlspecialchars($user['firstName']) ?></td>
    </tr>
    <tr>
        <th>Last Name</th>
        <td><?= htmlspecialchars($user['lastName']) ?></td>
    </tr>
    <tr>
        <th>Email</th>
        <td><?= htmlspecialchars($user['email']) ?></td>
    </tr>
</table>

<h3>Change Password</h3>
<form method="POST">
    <input type="hidden" name="change_password" value="1">
    <label>Current Password:</label><br>
    <input type="password" name="current_password" required><br><br>

    <label>New Password:</label><br>
    <input type="password" name="new_password" required><br><br>

    <label>Confirm New Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit">Change Password</button>
</form>

<h3>Delete My Account</h3>
<form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
    <input type="hidden" name="delete_account" value="1">
    <p>Type <strong>DELETE</strong> below to confirm:</p>
    <input type="text" name="confirm_delete" required><br><br>
    <button type="submit">Delete My Account</button>
</form>

<br>
<a href="logout.php">Logout</a>

</body>
</html>
