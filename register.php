<?php
session_start();
require 'connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name']);
    $email     = trim($_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate strathmore email
    if (preg_match('/^[a-z]+\.[a-z]+@strathmore\.edu$/i', $email)) {
        $role = 'student';
    } elseif (preg_match('/^[a-z]{1}[a-z]+@strathmore\.edu$/i', $email)) {
        $role = 'admin';
    } else {
        $error = "Only @strathmore.edu emails are allowed.";
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $firstName, $lastName, $email, $password, $role);

        try {
            $stmt->execute();

            // Set session for immediate login
            $_SESSION['userID'] = $stmt->insert_id;
            $_SESSION['email']  = $email;
            $_SESSION['role']   = $role;
            $_SESSION['firstName'] = $firstName;

            header("Location: index.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $error = "An account already exists with that email.";
            } else {
                $error = "Registration failed: " . $e->getMessage();
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Empty Classroom Finder</title>
</head>
<body>
    <h2>Register</h2>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" required><br><br>

        <label>Email (must be @strathmore.edu):</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</body>
</html>
