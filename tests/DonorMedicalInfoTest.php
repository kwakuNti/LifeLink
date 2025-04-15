<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/functions.php';

class DonorMedicalInfoTest extends TestCase
{
    private $conn;
    private $testUserId;

    protected function setUp(): void
    {
        $this->conn = new mysqli('127.0.0.1', 'root', 'root', 'life_test', 3306);

        // Create a dummy user and get ID
        $email = 'donor_test_' . rand(1000, 9999) . '@example.com';
        $pass = password_hash('testpass', PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, role, otp, is_verified) VALUES ('Donor Test', ?, ?, 'donor', '111111', TRUE)");
        $stmt->bind_param("ss", $email, $pass);
        $stmt->execute();
        $this->testUserId = $stmt->insert_id;
        $stmt->close();
    }

    public function testMissingRequiredFields()
    {
        $data = [
            'blood_type' => '',  // missing
            'init_age' => 30,
            'bmi_tcr' => 21.5,
            'dayswait_alloc' => 100
        ];

        $result = saveDonorMedicalInfo($this->conn, $this->testUserId, $data);
        $this->assertEquals("Missing or invalid required fields.", $result);
    }

    public function testSuccessfulInsert()
    {
        $data = [
            'blood_type' => 'A',
            'init_age' => 30,
            'bmi_tcr' => 21.5,
            'dayswait_alloc' => 100,
            'kidney_cluster' => 1,
            'on_dialysis' => 'Y'
        ];

        $result = saveDonorMedicalInfo($this->conn, $this->testUserId, $data);
        $this->assertEquals("success", $result);
    }

    public function testSuccessfulUpdate()
    {
        $data = [
            'blood_type' => 'O',
            'init_age' => 35,
            'bmi_tcr' => 23.0,
            'dayswait_alloc' => 120,
            'kidney_cluster' => 2,
            'on_dialysis' => 'N'
        ];

        $result = saveDonorMedicalInfo($this->conn, $this->testUserId, $data);
        $this->assertEquals("success", $result);
    }

    protected function tearDown(): void
    {
        $stmt = $this->conn->prepare("DELETE FROM donors WHERE user_id = ?");
        $stmt->bind_param("i", $this->testUserId);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->testUserId);
        $stmt->execute();
        $stmt->close();

        $this->conn->close();
    }
}
