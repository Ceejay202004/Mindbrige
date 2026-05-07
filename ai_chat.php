<?php
session_start();
header('Content-Type: application/json');

// Optional: only let logged-in users use AI
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login first']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Say something?']);
    exit;
}

// DeepSeek API (free, easy)
$apiKey = 'YOUR_DEEPSEEK_API_KEY';  // Replace later
$url = 'https://api.deepseek.com/v1/chat/completions';

$data = [
    'model' => 'deepseek-chat',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a kind mental health companion. Keep answers short and warm.'],
        ['role' => 'user', 'content' => $userMessage]
    ],
    'temperature' => 0.7,
    'max_tokens' => 300
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['reply' => 'AI is busy. Try again later.']);
    exit;
}

$result = json_decode($response, true);
$aiReply = $result['choices'][0]['message']['content'] ?? 'Sorry, no response.';
echo json_encode(['reply' => $aiReply]);
?>