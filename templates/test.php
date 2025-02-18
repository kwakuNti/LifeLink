<?php
// test-cities.php
include '../includes/get-cities.php';

// Set a test region – ensure this matches a region in your hospitals table (e.g., "Greater Accra")
$testRegion = "Greater Accra";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test City Options</title>
</head>
<body>
    <h2>Cities for <?php echo htmlspecialchars($testRegion); ?></h2>
    <select>
        <?php echo getCityOptions($testRegion); ?>
    </select>
</body>
</html>
