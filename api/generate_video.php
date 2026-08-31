<?php
declare(strict_types=1);

session_start();

$config = require __DIR__ . '/../private/config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorised']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$prompt = trim((string)($_POST['prompt'] ?? ''));

if ($prompt === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt is required.']);
    exit;
}

$apiUrl = $config['video']['api_url'];
$apiKey = $config['video']['api_key'];

$data = [
    'prompt' => $prompt,
    'user_id' => $_SESSION['user_id'],
    'model' => 'video-model-v1',
];

$payload = json_encode($data);

$ch = curl_init($apiUrl);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'Accept: application/json',
    ],
]);

$response = curl_exec($ch);

if ($response === false) {
    $curlError = curl_error($ch);
    curl_close($ch);

    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to contact AI video API.',
        'details' => $curlError,
    ]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode >= 400) {
    http_response_code($httpCode);
    echo json_encode([
        'error' => 'AI API request failed.',
        'response' => $result,
    ]);
    exit;
}

header('Content-Type: application/json');

echo json_encode([
    'status' => 'accepted',
    'message' => 'Video generation request sent.',
    'response' => $result,
]);
