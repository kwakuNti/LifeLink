<?php
use PHPUnit\Framework\TestCase;

class ApiMatchTest extends TestCase
{
    private $baseUrl = 'http://localhost:5000/api';

    public function testFindMatchesWithValidDonor()
    {
        $payload = json_encode(['donor_id' => 1]);
        
        $response = $this->sendPostRequest("/find_matches", $payload, $statusCode, $rawResponse);
        
        // For debugging
        $this->addToAssertionCount(1); // Count this as a passed assertion to see output
        echo "Raw response: " . $rawResponse . PHP_EOL;
        echo "Status code: " . $statusCode . PHP_EOL;
        
        // Check if response is valid JSON and an array
        $this->assertNotNull($response, "API returned null response");
        $this->assertIsArray($response, "API response is not an array");
        
        if (is_array($response)) {
            $this->assertArrayHasKey('matches', $response);
        }
    }

    public function testFindMatchesWithMissingDonor()
    {
        $payload = json_encode(['donor_id' => null]);

        $response = $this->sendPostRequest("/find_matches", $payload, $statusCode, $rawResponse);
        
        // For debugging
        echo "Raw response: " . $rawResponse . PHP_EOL;
        echo "Status code: " . $statusCode . PHP_EOL;

        // Change assertions to match your actual API response structure
        $this->assertNotNull($response, "API returned null response");
        $this->assertIsArray($response, "API response is not an array");
        if (is_array($response)) {
            $this->assertArrayHasKey('error', $response);
            $this->assertEquals('Invalid donor_id', $response['error']);
        }
        
        // We expect a 400 status code for invalid input
        $this->assertEquals(400, $statusCode, "Expected status 400 for invalid donor_id");
    }

    public function testPredictSuccessWithValidPair()
    {
        $payload = json_encode([
            'donor_id' => 1,
            'recipient_id' => 1
        ]);
    
        $response = $this->sendPostRequest("/predict_success", $payload, $statusCode, $rawResponse);
        
        // For debugging
        echo "Raw response: " . $rawResponse . PHP_EOL;
        echo "Status code: " . $statusCode . PHP_EOL;
        
        // Check if response is valid
        $this->assertNotNull($response, "API returned null response");
        $this->assertIsArray($response, "API did not return a valid array response");
        
        if (is_array($response)) {
            $this->assertArrayHasKey('prediction', $response);
        }
    }
    
    private function sendPostRequest($endpoint, $payload, &$statusCode = null, &$rawResponse = null)
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        // Set timeout to prevent tests from hanging
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $rawResponse = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Check for curl errors
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch) . PHP_EOL;
        }
        
        curl_close($ch);
        
        // Try to decode JSON, but handle errors
        $decodedResponse = json_decode($rawResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON Error: " . json_last_error_msg() . PHP_EOL;
        }
        
        return $decodedResponse;
    }
}