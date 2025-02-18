<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <title>Organ Donation | Select Role</title>
    <link rel="stylesheet" href="../public/css/selector.css">
</head>
<body>
    <div class="container">
        <div class="option donor" onclick="selectRole('donor')">
            <div class="overlay"></div>
            <h2>I Want to Donate</h2>
            <p>Give the gift of life. Become a donor today.</p>
        </div>

        <div class="option recipient" onclick="selectRole('recipient')">
            <div class="overlay"></div>
            <h2>I Need a Transplant</h2>
            <p>Find hope and a second chance at life.</p>
        </div>
    </div>

    <!-- Floating Organ Animations -->
    <img src="../assets/images/heart.png" class="floating organ heart">
    <img src="../assets/images/kidney.png" class="floating organ kidney">
    <img src="../assets/images/liver.png" class="floating organ liver">

    <script src="../public/js/selector.js"></script>
</body>
</html>
