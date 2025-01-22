<!DOCTYPE html>
<html>
<head>
    <title>Choose Organ to Donate</title>
    <link rel="stylesheet" type="text/css" href="../public/css/login.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a81368914c.js"></script>
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <link rel="stylesheet" href="../public/css/homepage.css">
    <link rel="stylesheet" href="../public/css/snackbar.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Main Container */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        /* Title */
        .form-title {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Modern Dropdown Wrapper */
        .modern-dropdown {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .dropdown-selected {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f0f0f0;
            padding: 10px 15px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }

        .dropdown-selected:after {
            content: '\f0d7'; /* FontAwesome down arrow */
            font-family: FontAwesome;
            font-size: 16px;
        }

        .dropdown-options {
            position: absolute;
            top: 110%;
            left: 0;
            width: 100%;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: none;
            z-index: 100;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dropdown-options div {
            padding: 10px 15px;
            font-size: 16px;
            color: #333;
            cursor: pointer;
        }

        .dropdown-options div:hover {
            background: #f5f5f5;
        }

        .btn {
            margin-top: 20px;
            background-color: #333;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
            width: 100%;
        }

        .btn:hover {
            background-color: #555;
        }

        .logo img {
    width: 300px; /* Adjust the logo size as needed */
}
    
    </style>
</head>
<body>
    <div class="container">
    <div class="logo">
                    <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
        </div>
        <h2 class="form-title">Choose Organ to Donate</h2>
        <form onsubmit="return handleOrganSelection()">
            <!-- Modern Dropdown -->
            <div class="modern-dropdown" id="modernDropdown">
                <div class="dropdown-selected" id="selectedOption">
                    Select an organ
                </div>
                <div class="dropdown-options" id="dropdownOptions">
                    <div data-value="Kidney">Kidney</div>
                    <div data-value="Liver">Liver</div>
                </div>
            </div>

            <!-- Hidden input to store selected value -->
            <input type="hidden" id="organ" name="organ" value="">

            <!-- Submit Button -->
            <input type="submit" class="btn" value="Confirm">
        </form>

        <!-- Snackbar for feedback messages -->
        <div id="snackbar"></div>
    </div>

    <script>
        const dropdown = document.getElementById('modernDropdown');
        const dropdownOptions = document.getElementById('dropdownOptions');
        const selectedOption = document.getElementById('selectedOption');
        const organInput = document.getElementById('organ');
        const snackbar = document.getElementById('snackbar');

        // Toggle dropdown visibility
        selectedOption.addEventListener('click', () => {
            dropdownOptions.style.display = dropdownOptions.style.display === 'block' ? 'none' : 'block';
        });

        // Handle dropdown option selection
        dropdownOptions.addEventListener('click', (event) => {
            const value = event.target.getAttribute('data-value');
            if (value) {
                selectedOption.textContent = value;
                organInput.value = value;
                dropdownOptions.style.display = 'none';
            }
        });

        // Handle form submission
        function handleOrganSelection() {
            if (!organInput.value) {
                snackbar.textContent = "Please select an organ to donate.";
                snackbar.className = "snackbar show";
                setTimeout(() => snackbar.className = snackbar.className.replace("show", ""), 3000);
                return false;
            }

            // Display confirmation message in snackbar
            snackbar.textContent = `You have chosen to donate your ${organInput.value}. Thank you!`;
            snackbar.className = "snackbar show";

            // Prevent actual form submission for demo purposes
            setTimeout(() => snackbar.className = snackbar.className.replace("show", ""), 3000);

            return false;
        }
    </script>
</body>
</html>
