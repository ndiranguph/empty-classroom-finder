<?php
session_start();
require 'connection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name']);
    $email     = trim($_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Detect role based on email format
    if (preg_match('/^[a-z]+\.[a-z]+@strathmore\.edu$/i', $email)) {
        $role = 'student';
    } elseif (preg_match('/^[a-z]{1}[a-z]+@strathmore\.edu$/i', $email)) {
        $role = 'admin';
    } else {
        $error = "Only @strathmore.edu emails are allowed.";
    }

    if (empty($error)) {
        // Check for existing account
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            $error = "An account already exists with that email.";
        } else {
            // Generate 6-digit OTP
            $verification_code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password, role, is_verified, verification_code)
                                    VALUES (?, ?, ?, ?, ?, 0, ?)");
            $stmt->bind_param("ssssss", $firstName, $lastName, $email, $password, $role, $verification_code);

            if ($stmt->execute()) {
                $stmt->close();

                // Send OTP via PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = '@gmail.com';        // Your Gmail address
                    $mail->Password   = '';         // Your App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    $mail->setFrom('@gmail.com', 'Empty Classroom Finder');
                    $mail->addAddress($email); // Recipient = user
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Verification Code';
                    $mail->Body    = "Hi $firstName,<br><br>Your verification code is: <strong>$verification_code</strong><br><br>Please enter this code to complete your registration.";

                    $mail->send();

                    // Redirect to verify page
                    $_SESSION['pending_email'] = $email;
                    header("Location: verify.php");
                    exit;
                } catch (Exception $e) {
                    $error = "Verification email failed to send. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Failed to register. Please try again.";
            }
        }
    }
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>
    <h2>Register</h2>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" required><br><br>

        <label>Email (@strathmore.edu only):</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>
