<?php
session_start();
require 'connection.php';

$email = '';
if (isset($_COOKIE['remember_me'])) {
    $email = $_COOKIE['remember_me'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['firstName'] = $user['firstName'];

            if (isset($_POST['remember'])) {
                setcookie("remember_me", $user['email'], time() + (86400 * 30), "/"); // 30 days
            } else {
                setcookie("remember_me", "", time() - 3600, "/"); // clear if not checked
            }

            if ($user['role'] === 'student') {
                header('Location: student_classrooms.php');
            } elseif ($user['role'] === 'admin') {
                header('Location: admin_dashboard.php');
            } elseif ($user['role'] === 'system_admin') {
                header('Location: system_admin_dashboard.php');
            }
            exit;
        }
    }

    $error = "Invalid email or password.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Empty Classroom Finder</title>
</head>
<body>
    <h2>Login</h2>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" action="login.php" autocomplete="off">
        <label>Email:</label><br>
        <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>" autocomplete="email"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required autocomplete="new-password"><br><br>

        <label><input type="checkbox" name="remember" <?= isset($_COOKIE['remember_me']) ? 'checked' : '' ?>> Remember Me</label><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>
</body>
</html>
