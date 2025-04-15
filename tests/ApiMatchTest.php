<?php
use PHPUnit\Framework\TestCase;

class ApiMatchTest extends TestCase
{
    private $baseUrl = 'http://localhost:5000/api';

    public function testFindMatchesWithValidDonor()
    {
        $payload = json_encode(['donor_id' => 1]); // Assumes donor ID 1 exists in life_test DB

        $response = $this->sendPostRequest("/find_matches", $payload);
        $this->assertArrayHasKey('matches', $response);
        $this->assertIsArray($response['matches']);
    }

    public function testFindMatchesWithMissingDonor()
    {
        $payload = json_encode(['donor_id' => null]);

        $response = $this->sendPostRequest("/find_matches", $payload, $statusCode);
        $this->assertEquals(400, $statusCode);
        $this->assertArrayHasKey('error', $response);
    }

    public function testPredictSuccessWithValidPair()
    {
        $payload = json_encode([
            'donor_id' => 1,
            'recipient_id' => 1
        ]);
    
        $response = $this->sendPostRequest("/predict_success", $payload, $statusCode);
        
        // Check that we got a valid response before checking keys
        $this->assertIsArray($response, "API did not return a valid array response");
        
        if (is_array($response)) {
            $this->assertArrayHasKey('prediction', $response);
        }
    }
    

    private function sendPostRequest($endpoint, $payload, &$statusCode = null)
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return json_decode($response, true);
    }
}
