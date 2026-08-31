<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim((string)($_POST['username_or_email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($usernameOrEmail === '' || $password === '') {
        $_SESSION['error'] = 'Please enter both your username/email and password.';
        header('Location: login.php');
        exit;
    }

    $stmt = $pdo->prepare('
        SELECT id, username, email, password_hash
        FROM users
        WHERE username = :username_or_email
           OR email = :username_or_email
        LIMIT 1
    ');
    $stmt->execute([
        ':username_or_email' => $usernameOrEmail,
    ]);

    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['error'] = 'Invalid login details.';
        header('Location: login.php');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];

    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Log in</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <p style="color: green;">
            <?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="post" action="login.php" novalidate>
        <label for="username_or_email">Username or Email</label><br>
        <input type="text" id="username_or_email" name="username_or_email" required><br><br>

        <label for="password">Password</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Log in</button>
    </form>

    <p>Need an account? <a href="register.php">Register</a></p>
</body>
</html>