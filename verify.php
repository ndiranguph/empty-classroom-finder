<?php
session_start();
require 'connection.php';

$error = '';

// Check if coming from registration
if (!isset($_SESSION['pending_email'])) {
    header("Location: register.php");
    exit;
}

$email = $_SESSION['pending_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = trim($_POST['verification_code']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && $user['verification_code'] === $entered_code) {
        // Mark user as verified
        $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();
        $update->close();

        // Log the user in
        unset($_SESSION['pending_email']);
        $_SESSION['email'] = $user['email'];
        $_SESSION['firstName'] = $user['firstName'];
        $_SESSION['role'] = $user['role'];

        // Redirect by role
        switch ($user['role']) {
            case 'student':
                header("Location: student_classrooms.php");
                echo "<script>window.location.href = 'student_classrooms.php';</script>";
                break;
            case 'admin':
                header("Location: admin_dashboard.php");
                echo "<script>window.location.href = 'admin_dashboard.php';</script>";
                break;
            case 'system_admin':
                header("Location: system_admin_dashboard.php");
                echo "<script>window.location.href = 'system_admin_dashboard.php';</script>";
                break;
        }
        exit;
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>

<!-- OTP Verification Page -->
<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h2>Email Verification</h2>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <p>A verification code was sent to: <strong><?php echo htmlspecialchars($email); ?></strong></p>

    <form method="POST" action="">
        <label>Enter the 6-digit verification code:</label><br>
        <input type="text" name="verification_code" maxlength="6" required><br><br>
        <button type="submit">Verify</button>
    </form>

    <p><a href="register.php">Back to registration</a></p>
</body>
</html>
