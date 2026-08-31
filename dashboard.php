<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$userId = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>

    <p>Welcome, <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>!</p>

    <p><a href="logout.php">Log out</a></p>

    <hr>

    <h2>Generate a video</h2>

    <form action="api/generate_video.php" method="post">
        <label for="prompt">Describe the video you want</label><br>
        <textarea
            id="prompt"
            name="prompt"
            rows="6"
            cols="60"
            placeholder="Example: A cinematic drone shot over a mountain lake at sunrise"
            required
        ></textarea><br><br>

        <button type="submit">Generate Video</button>
    </form>
</body>
</html>
