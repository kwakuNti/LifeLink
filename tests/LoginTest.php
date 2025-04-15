<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/functions.php';

class LoginTest extends TestCase
{
    private $conn;
    private $testEmail = 'login_test@example.com';
    private $testPassword = 'testpass123';

    protected function setUp(): void
    {
        $this->conn = new mysqli('localhost', 'root', 'root', 'life_test');

        // Ensure the test user exists in the DB
        $hashed = password_hash($this->testPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT IGNORE INTO users (name, email, password, role, otp, is_verified) VALUES ('Test User', ?, ?, 'donor', '123456', TRUE)");
        $stmt->bind_param("ss", $this->testEmail, $hashed);
        $stmt->execute();
        $stmt->close();
    }

    public function testEmptyEmailOrPassword()
    {
        $this->assertEquals("Email and password are required!", validateLogin($this->conn, '', ''));
    }

    public function testWrongCredentials()
    {
        $this->assertEquals("Invalid email or password!", validateLogin($this->conn, 'wrong@example.com', 'wrongpass'));
    }

    public function testCorrectLogin()
    {
        $this->assertEquals("success", validateLogin($this->conn, $this->testEmail, $this->testPassword));
    }

    protected function tearDown(): void
    {
        // Optionally delete the test user
        $stmt = $this->conn->prepare("DELETE FROM users WHERE email = ?");
        $stmt->bind_param("s", $this->testEmail);
        $stmt->execute();
        $stmt->close();

        $this->conn->close();
    }
}
