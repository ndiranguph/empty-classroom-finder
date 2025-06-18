<?php
require 'connection.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstName']);
    $lastName  = trim($_POST['lastName']);
    $email     = trim($_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate strathmore email
    if (preg_match('/^[a-z]+\.[a-z]+@strathmore\.edu$/i', $email)) {
        $role = 'student';
    } elseif (preg_match('/^[a-z]{1}[a-z]+@strathmore\.edu$/i', $email)) {
        $role = 'admin';
    } else {
        $error = "Only strathmore.edu emails are allowed.";
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $firstName, $lastName, $email, $password, $role);

        if ($stmt->execute()) {
            $success = "Registration successful. You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Registration failed: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Empty Classroom Finder</title>
</head>
<body>
    <h2>Register</h2>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>First Name:</label><br>
        <input type="text" name="firstName" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="lastName" required><br><br>

        <label>Email (must be @strathmore.edu):</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</body>
</html>
