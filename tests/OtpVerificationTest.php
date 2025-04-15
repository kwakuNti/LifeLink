<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/functions.php';

class OtpVerificationTest extends TestCase
{
    private $conn;
    private $testEmail = 'otp_test@example.com';
    private $otp = '654321';

    protected function setUp(): void
    {
        $this->conn = new mysqli('localhost', 'root', 'root', 'life_test');

        // Insert test user with OTP and not verified
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, role, otp, is_verified) VALUES ('OTP Tester', ?, ?, 'donor', ?, FALSE)");
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $stmt->bind_param("sss", $this->testEmail, $password, $this->otp);
        $stmt->execute();
        $stmt->close();
    }

    public function testInvalidOtp()
    {
        $result = verifyOtp($this->conn, $this->testEmail, '000000');
        $this->assertEquals("Invalid OTP", $result);
    }

    public function testCorrectOtp()
    {
        $result = verifyOtp($this->conn, $this->testEmail, $this->otp);
        $this->assertEquals("success", $result);
    }

    public function testAlreadyVerified()
    {
        // Run once to verify
        verifyOtp($this->conn, $this->testEmail, $this->otp);
        // Try again
        $result = verifyOtp($this->conn, $this->testEmail, $this->otp);
        $this->assertEquals("Email not found or already verified", $result);
    }

    protected function tearDown(): void
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE email = ?");
        $stmt->bind_param("s", $this->testEmail);
        $stmt->execute();
        $stmt->close();

        $this->conn->close();
    }
}
