<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        $_SESSION['error'] = 'Please complete all fields.';
        header('Location: register.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please use a valid email address.';
        header('Location: register.php');
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        header('Location: register.php');
        exit;
    }

    $stmt = $pdo->prepare('
        SELECT id
        FROM users
        WHERE username = :username OR email = :email
        LIMIT 1
    ');
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
    ]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = 'That username or email is already in use.';
        header('Location: register.php');
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare('
        INSERT INTO users (username, email, password_hash)
        VALUES (:username, :email, :password_hash)
    ');
    $insert->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $passwordHash,
    ]);

    $_SESSION['success'] = 'Registration was successful. Please log in.';
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Create an account</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="post" action="register.php" novalidate>
        <label for="username">Username</label><br>
        <input type="text" id="username" name="username" required><br><br>

        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Log in</a></p>
</body>
</html>