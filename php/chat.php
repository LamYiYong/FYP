<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$title = $data['title'] ?? '';

if (!$title) {
    echo json_encode(["summary" => "Missing title."]);
    exit;
}

$prompt = "Please summarize the research paper titled: \"$title\"";

// Prepare request to local Ollama
$chatData = [
    "model" => "llama3.2",
    "messages" => [
        ["role" => "user", "content" => $prompt]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:11434/api/chat");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($chatData));

$response = curl_exec($ch);
curl_close($ch);

// Collect streamed response
$lines = explode("\n", trim($response));
$summary = '';

foreach ($lines as $line) {
    $json = json_decode($line, true);
    if (isset($json['message']['content'])) {
        $summary .= $json['message']['content'];
    }
}

echo json_encode(["summary" => $summary ?: "No summary returned."]);
