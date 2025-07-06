<?php
session_start();
require 'connection.php';

$email = '';
$error = '';

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
            if ((int)$user['is_verified'] === 0) {
                $_SESSION['pending_email'] = $user['email'];
                header("Location: verify.php");
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['firstName'] = $user['firstName'];

            // Remember me
            if (isset($_POST['remember'])) {
                setcookie(
                    "remember_me",
                    $user['email'],
                    [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );
            } else {
                setcookie("remember_me", "", time() - 3600, "/");
            }

            // Redirect based on role
            switch ($user['role']) {
                case 'student':
                    header("Location: student_classrooms.php");
                    break;
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'system_admin':
                    header("Location: system_admin_dashboard.php");
                    break;
            }
            exit;
        }
    }

    $error = "Invalid email or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Empty Classroom Finder</title>
</head>
<body>
    <h2>Login</h2>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="off">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label>
            <input type="checkbox" name="remember" <?php echo isset($_COOKIE['remember_me']) ? 'checked' : ''; ?>>
            Remember Me
        </label><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don’t have an account? <a href="register.php">Register here</a></p>
</body>
</html>
