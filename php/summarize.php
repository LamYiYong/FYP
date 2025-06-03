<?php
// summarize.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$abstract = $data['abstract'] ?? '';

if (!$abstract) {
    echo json_encode(['summary' => 'No abstract provided.']);
    exit;
}

$prompt = "Summarize the following abstract for a student in 1-2 sentences:\n\n" . $abstract;

$ch = curl_init('http://localhost:11434/api/generate');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'llama3.2',
        'prompt' => $prompt,
        'stream' => false
    ])
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo json_encode(['summary' => $data['response'] ?? 'Unable to summarize.']);
