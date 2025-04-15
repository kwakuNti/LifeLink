<?php
use PHPUnit\Framework\TestCase;

class ApiMatchTest extends TestCase
{
    private $baseUrl = 'http://localhost:5000/api';

    public function testFindMatchesWithValidDonor()
    {
        $payload = json_encode(['donor_id' => 1]);
        
        $response = $this->sendPostRequest("/find_matches", $payload, $statusCode, $rawResponse);
        
        // Debug output
        echo "Raw response: " . $rawResponse . PHP_EOL;
        echo "Status code: " . $statusCode . PHP_EOL;
        
        $this->assertEquals(200, $statusCode);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('matches', $response);
    }

    public function testFindMatchesWithMissingDonor()
    {
        $payload = json_encode(['other_field' => 'value']);
        
        $response = $this->sendPostRequest("/find_matches", $payload, $statusCode, $rawResponse);
        
        // Debug output
        echo "Raw response: " . $rawResponse . PHP_EOL;
        echo "Status code: " . $statusCode . PHP_EOL;
        
        $this->assertEquals(400, $statusCode);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('Missing donor_id parameter', $response['error']);
    }

    public function testPredictSuccessWithValidPair()
    {
        $payload = json_encode([
            'donor_id' => 1,
            'recipient_id' => 1
        ]);
    
        $response = $this->sendPostRequest("/predict_success", $payload, $statusCode, $rawResponse);
        
        // Debug output
        echo "Raw response: " . $rawResponse . PHP_EOL;
        echo "Status code: " . $statusCode . PHP_EOL;
        
        $this->assertEquals(200, $statusCode);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('prediction', $response);
        $this->assertArrayHasKey('probability', $response);
    }

    public function testHealthCheck()
    {
        $ch = curl_init($this->baseUrl . "/health");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $rawResponse = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $response = json_decode($rawResponse, true);
        
        echo "Health check response: " . $rawResponse . PHP_EOL;
        
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('ok', $response['status']);
    }
    
    private function sendPostRequest($endpoint, $payload, &$statusCode = null, &$rawResponse = null)
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $rawResponse = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch) . PHP_EOL;
        }
        
        curl_close($ch);
        
        return json_decode($rawResponse, true);
    }
}