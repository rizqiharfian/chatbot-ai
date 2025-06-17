<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$message = $input['message'] ?? '';

if (trim($message) === '') {
    echo json_encode(['reply' => 'Pesan kosong']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO messages (sender, message) VALUES (?, ?)");
    $stmt->execute(['user', $message]);

    $client = new Client();
    $apiKey = $_ENV['GEMINI_API_KEY'];
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $message]
                ]
            ]
        ]
    ];

    $response = $client->post($url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode($payload)
    ]);

    $result = json_decode($response->getBody(), true);
    $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Bot tidak membalas apa-apa.';

    $stmt = $pdo->prepare("INSERT INTO messages (sender, message) VALUES (?, ?)");
    $stmt->execute(['bot', $reply]);

    echo json_encode(['reply' => $reply]);

} catch (Exception $e) {
    echo json_encode(['reply' => 'Error: ' . $e->getMessage()]);
}
