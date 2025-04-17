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
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --text-color: #374151;
            --light-text: #6b7280;
            --bg-color: #f9fafb;
            --input-bg: #ffffff;
            --input-border: #e5e7eb;
            --input-focus: #a5b4fc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .container {
            background-color: white;
            width: 100%;
            max-width: 480px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            padding: 2.5rem;
            text-align: center;
        }
        
        .email-icon {
            width: 64px;
            height: 64px;
            background-color: rgba(79, 70, 229, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .email-icon svg {
            width: 32px;
            height: 32px;
            color: var(--primary-color);
        }
        
        h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #111827;
        }
        
        p {
            color: var(--light-text);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .otp-input {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        
        .otp-input input {
            width: 3rem;
            height: 3rem;
            border: 1px solid var(--input-border);
            border-radius: 12px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 600;
            background-color: var(--input-bg);
            color: var(--text-color);
            transition: all 0.2s ease;
        }
        
        .otp-input input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        
        .submit-button {
            width: 100%;
            padding: 0.875rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .submit-button:hover {
            background-color: var(--primary-hover);
        }
        
        .timer {
            display: block;
            margin-top: 1.5rem;
            color: var(--light-text);
            font-size: 0.875rem;
        }
        
        .resend {
            margin-top: 0.75rem;
            display: block;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .resend:hover {
            text-decoration: underline;
        }
        
        /* Snackbar styling */
        #snackbar {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #323232;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
            font-size: 0.9rem;
        }

        #snackbar.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }
        
        #snackbar.success {
            background-color: #4ade80;
        }
        
        #snackbar.error {
            background-color: #f87171;
        }

        @keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }

        @keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <h2>Verify Your Email</h2>
        <p>We've sent a 6-digit verification code to your email address. Enter the code below to confirm your email.</p>
        
        <form id="otpForm" action="../actions/verify-otp.php" method="POST">
            <div class="otp-input">
                <input type="text" maxlength="1" name="digit1" autocomplete="off" required>
                <input type="text" maxlength="1" name="digit2" autocomplete="off" required>
                <input type="text" maxlength="1" name="digit3" autocomplete="off" required>
                <input type="text" maxlength="1" name="digit4" autocomplete="off" required>
                <input type="text" maxlength="1" name="digit5" autocomplete="off" required>
                <input type="text" maxlength="1" name="digit6" autocomplete="off" required>
            </div>
            <input type="hidden" name="otp" id="otp">
            <button type="submit" class="submit-button">Verify Email</button>
        </form>
        
        <span class="timer" id="timer">Code expires in: <span id="countdown">05:00</span></span>
        <a href="#" class="resend" id="resendLink">Didn't receive the code? Resend</a>
    </div>
    
    <div id="snackbar"></div>

    <script>
        // Auto-focus and move to next input
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.otp-input input');
            
            // Focus first input on page load
            inputs[0].focus();
            
            inputs.forEach((input, index) => {
                // Move to next input after entering a digit
                input.addEventListener('input', function() {
                    if (this.value.length === 1) {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    }
                });
                
                // Handle backspace to go to previous input
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
                
                // Only allow numbers
                input.addEventListener('keypress', function(e) {
                    if (!/^\d$/.test(e.key)) {
                        e.preventDefault();
                    }
                });
            });
            
            // Countdown timer
            let timeLeft = 300; // 5 minutes in seconds
            const countdownEl = document.getElementById('countdown');
            const timerInterval = setInterval(function() {
                const minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                seconds = seconds < 10 ? '0' + seconds : seconds;
                
                countdownEl.textContent = `${minutes}:${seconds}`;
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    countdownEl.textContent = '0:00';
                    document.getElementById('resendLink').classList.add('active');
                }
                
                timeLeft--;
            }, 1000);
            
            // Form submission
            document.getElementById('otpForm').addEventListener('submit', function(e) {
                const otpValue = Array.from(inputs).map(input => input.value).join('');
                document.getElementById('otp').value = otpValue;
            });
            
            // Check for message params
            checkForMessage();
        });
        
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
        
        // Resend functionality (placeholder)
        document.getElementById('resendLink').addEventListener('click', function(e) {
            e.preventDefault();
            showSnackbar('Resending verification code...', 'success');
            // Here you would add the actual AJAX call to resend the OTP
        });
    </script>
    <script src="../public/js/otp.js"></script>
</body>
</html>