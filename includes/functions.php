<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use mysqli;

function findCompatibleMatches(array $donor): array {
    $client = new Client(['base_uri' => 'http://localhost:5000']);
    
    try {
        $response = $client->post('/api/matches', [
            'json' => ['donor' => $donor],
            'headers' => ['Content-Type' => 'application/json']
        ]);
        
        return json_decode($response->getBody()->getContents(), true)['matches'] ?? [];
    } catch (Exception $e) {
        error_log("Match API Error: " . $e->getMessage());
        return [];
    }
}

function predictTransplantSuccess(array $donor, array $recipient): array {
    $client = new Client(['base_uri' => 'http://localhost:5000']);
    
    try {
        $response = $client->post('/api/predict', [
            'json' => [
                'donor' => $donor,
                'recipient' => $recipient
            ],
            'headers' => ['Content-Type' => 'application/json']
        ]);
        
        return json_decode($response->getBody()->getContents(), true);
    } catch (Exception $e) {
        error_log("Prediction API Error: " . $e->getMessage());
        return ['probability' => 0.0, 'message' => 'Error'];
    }
}

function getDonorProfile(mysqli $conn, int $userId): array {
    $stmt = $conn->prepare("SELECT * FROM donors WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: [];
}

function getRecipient(mysqli $conn, int $id): array {
    $stmt = $conn->prepare("SELECT * FROM recipients WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: [];
}