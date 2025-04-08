<?php
session_start();
include '../config/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $name = trim($_POST['patient_name']);
    $email = trim($_POST['patient_email']);
    $password = $_POST['patient_password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone = trim($_POST['patient_phone']);
    $location = trim($_POST['patient_location']); // Location string from the form
    $description = trim($_POST['patient_description']);

    // Validate required fields
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        header("Location: ../templates/patient-info.php?status=error&message=Please fill in all required fields.");
        exit();
    }

    // Validate password match
    if ($password !== $confirmPassword) {
        header("Location: ../templates/patient-info.php?status=error&message=Passwords do not match.");
        exit();
    }

    // Check if the email is already registered
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: ../templates/patient-info.php?status=error&message=Email already exists.");
        exit();
    }
    $stmt->close();

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the 'users' table (role: recipient)
    $role = 'recipient';
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
    if (!$stmt->execute()) {
        header("Location: ../templates/patient-info.php?status=error&message=Error registering user.");
        exit();
    }
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Redirect to add patient page, passing the new user's id as patient_id
    header("Location: ../templates/add-patient.php?patient_id=" . $user_id . "&status=success&message=Successful");
    exit();
}
?>
