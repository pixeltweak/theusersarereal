<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');

if ($rawInput === false || $rawInput === '') {
    http_response_code(400);
    echo json_encode(['error' => 'No payload received']);
    exit;
}

$data = json_decode($rawInput, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$jobId = $data['job_id'] ?? null;
$videoUrl = $data['video_url'] ?? null;
$status = $data['status'] ?? null;

if ($jobId === null || $status === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required webhook data']);
    exit;
}

$logLine = json_encode([
    'job_id' => $jobId,
    'status' => $status,
    'video_url' => $videoUrl,
    'received_at' => date('c'),
]) . PHP_EOL;

file_put_contents(__DIR__ . '/webhook_log.txt', $logLine, FILE_APPEND);

header('Content-Type: application/json');
echo json_encode([
    'status' => 'received',
    'message' => 'Webhook processed successfully.',
    'job_id' => $jobId,
]);
