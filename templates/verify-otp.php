<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <title>Verify OTP</title>
    <link rel="stylesheet" type="text/css" href="../public/css/otp.css">
    <link rel="stylesheet" href="../public/css/snackbar.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <p>Enter the 6-digit OTP sent to your email.</p>
        
        <form id="otpForm" action="../actions/verify-otp.php" method="POST">
            <div class="otp-input">
                <input type="text" maxlength="1" name="digit1" required>
                <input type="text" maxlength="1" name="digit2" required>
                <input type="text" maxlength="1" name="digit3" required>
                <input type="text" maxlength="1" name="digit4" required>
                <input type="text" maxlength="1" name="digit5" required>
                <input type="text" maxlength="1" name="digit6" required>
            </div>
            <input type="hidden" name="otp" id="otp">
            <input type="submit" value="Verify">
        </form>
    </div>
    <script type="text/javascript">
        function checkForMessage() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('status') && params.has('message')) {
                const message = params.get('message');
                const status = params.get('status');
                showSnackbar(message, status);
            }
        }

        function showSnackbar(message, type) {
            let snackbar = document.getElementById("snackbar");
            snackbar.innerHTML = message;
            snackbar.className = "show " + type;
            setTimeout(() => {
                snackbar.className = snackbar.className.replace("show", "");
            }, 3000);
        }
    </script>
    <script src="../public/js/otp.js"></script>
</body>
</html>
