<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use mysqli as MySQLi;
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

function validateAndCreateUser($conn, $firstName, $lastName, $email, $password, $confirmPassword) {
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        return "All fields are required!";
    }

    if ($password !== $confirmPassword) {
        return "Passwords do not match!";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format!";
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        return "Email is already registered!";
    }
    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $otp = strval(rand(100000, 999999));
    $fullName = $firstName . " " . $lastName;

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, otp, is_verified) VALUES (?, ?, ?, 'donor', ?, FALSE)");
    $stmt->bind_param("ssss", $fullName, $email, $hashedPassword, $otp);

    if ($stmt->execute()) {
        $stmt->close();
        return "success";
    } else {
        return "Registration failed. Try again.";
    }

}

function validateLogin($conn, $email, $password) {
    if (empty($email) || empty($password)) {
        return "Email and password are required!";
    }

    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($userId, $fullName, $dbEmail, $hashedPassword);
    $stmt->fetch();

    if ($stmt->num_rows == 1 && password_verify($password, $hashedPassword)) {
        return "success";
    } else {
        return "Invalid email or password!";
    }
}

function saveDonorMedicalInfo($conn, $userId, $data) {
    // Required fields
    $bloodType = $data['blood_type'] ?? '';
    $initAge = (int) ($data['init_age'] ?? 0);
    $bmiTcr = (float) ($data['bmi_tcr'] ?? 0);
    $dayswaitAlloc = (int) ($data['dayswait_alloc'] ?? 0);

    if (empty($bloodType) || $initAge <= 0 || $bmiTcr <= 0.0 || $dayswaitAlloc < 0) {
        return "Missing or invalid required fields.";
    }

    // Optional fields
    $kidneyCluster = (int) ($data['kidney_cluster'] ?? 0);
    $dgnTcr = (float) ($data['dgn_tcr'] ?? 0.0);
    $wgtKgTcr = (float) ($data['wgt_kg_tcr'] ?? 0.0);
    $hgtCmTcr = (float) ($data['hgt_cm_tcr'] ?? 0.0);
    $gfr = (float) ($data['gfr'] ?? 0.0);
    $onDialysisBool = ($data['on_dialysis'] ?? 'N') === 'Y' ? 1 : 0;

    // Check if donor already exists
    $stmt = $conn->prepare("SELECT id FROM donors WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update existing
        $stmt->close();
        $sql = "UPDATE donors SET blood_type = ?, init_age = ?, bmi_tcr = ?, dayswait_alloc = ?, kidney_cluster = ?, dgn_tcr = ?, wgt_kg_tcr = ?, hgt_cm_tcr = ?, gfr = ?, on_dialysis = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sidiidddiii", $bloodType, $initAge, $bmiTcr, $dayswaitAlloc, $kidneyCluster, $dgnTcr, $wgtKgTcr, $hgtCmTcr, $gfr, $onDialysisBool, $userId);
    } else {
        // Insert new
        $stmt->close();
        $sql = "INSERT INTO donors (user_id, blood_type, init_age, bmi_tcr, dayswait_alloc, kidney_cluster, dgn_tcr, wgt_kg_tcr, hgt_cm_tcr, gfr, on_dialysis) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isidiidddii", $userId, $bloodType, $initAge, $bmiTcr, $dayswaitAlloc, $kidneyCluster, $dgnTcr, $wgtKgTcr, $hgtCmTcr, $gfr, $onDialysisBool);
    }

    if ($stmt->execute()) {
        $stmt->close();
        return "success";
    } else {
        return "Database operation failed.";
    }
}
function verifyOtp($conn, $email, $enteredOtp) {
    $stmt = $conn->prepare("SELECT otp FROM users WHERE email = ? AND is_verified = FALSE");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($dbOtp);
    if ($stmt->fetch()) {
        $stmt->close();
        if ($dbOtp === $enteredOtp) {
            $stmt = $conn->prepare("UPDATE users SET is_verified = TRUE WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->close();
            return "success";
        } else {
            return "Invalid OTP";
        }
    } else {
        return "Email not found or already verified";
    }
}
