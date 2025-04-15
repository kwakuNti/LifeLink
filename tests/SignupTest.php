<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/functions.php';

class SignupTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli('127.0.0.1', 'root', 'root', 'life_test', 3306);
    }

    public function testMissingFields()
    {
        $result = validateAndCreateUser($this->conn, '', 'Doe', 'jane@example.com', '123456', '123456');
        $this->assertEquals("All fields are required!", $result);
    }

    public function testPasswordMismatch()
    {
        $result = validateAndCreateUser($this->conn, 'Jane', 'Doe', 'jane@example.com', '123456', '654321');
        $this->assertEquals("Passwords do not match!", $result);
    }

    public function testInvalidEmail()
    {
        $result = validateAndCreateUser($this->conn, 'Jane', 'Doe', 'not-an-email', '123456', '123456');
        $this->assertEquals("Invalid email format!", $result);
    }

    public function testSuccessfulSignup()
    {
        // You might need to delete the test email from DB before this
        $email = 'jane'.rand(1000,9999).'@example.com'; // unique email
        $result = validateAndCreateUser($this->conn, 'Jane', 'Doe', $email, '123456', '123456');
        $this->assertEquals("success", $result);
    }

    protected function tearDown(): void
    {
        $this->conn->close();
    }
}
