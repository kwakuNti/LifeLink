<?php
header('Content-Type: application/json');
include '../config/connection.php';

$sql = "SELECT id, name FROM hospitals ORDER BY name ASC";
$result = $conn->query($sql);

$hospitals = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hospitals[] = [
            'id'   => $row['id'],
            'name' => $row['name']
        ];
    }
}

echo json_encode($hospitals);
$conn->close();
?>
