<?php
// chatbot.php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (!$userMessage) {
    echo json_encode(['reply' => 'No input received.']);
    exit;
}

// Setup the cURL request to Ollama
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'http://localhost:11434/api/generate',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'llama3.2',
        'prompt' => $userMessage,
        'stream' => false
    ])
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo json_encode(['reply' => 'Error contacting model: ' . curl_error($curl)]);
    curl_close($curl);
    exit;
}

curl_close($curl);
$data = json_decode($response, true);

echo json_encode(['reply' => $data['response'] ?? 'No response from model.']);