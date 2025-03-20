<!DOCTYPE html>
<html>
<head>
    <title>Choose Organ to Donate</title>
    <link rel="stylesheet" type="text/css" href="../public/css/login.css">
    <link rel="stylesheet" href="../public/css/homepage.css">
    <link rel="stylesheet" href="../public/css/snackbar.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Basic styling for the organ selector page */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        .logo img {
            width: 300px;
        }
        .form-title {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
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
            content: '\f0d7';
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
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
        </div>
        <h2 class="form-title">Choose Organ to Donate</h2>
        <form action="../actions/organ-selector-action.php" method="POST">
            <!-- Modern Dropdown for Organ Selection -->
            <div class="modern-dropdown" id="modernDropdown">
                <div class="dropdown-selected" id="selectedOption">
                    Select an organ
                </div>
                <div class="dropdown-options" id="dropdownOptions">
                    <div data-value="Kidney">Kidney</div>
                    <div data-value="Liver">Liver</div>
                </div>
            </div>
            <!-- Hidden input to store the selected organ -->
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

        // Show snackbar for errors or confirmations
        function showSnackbar(message, type) {
            snackbar.textContent = message;
            snackbar.className = "snackbar show " + type;
            setTimeout(() => snackbar.className = snackbar.className.replace("show", ""), 3000);
        }

        // Handle form submission
        function handleOrganSelection() {
            if (!organInput.value) {
                showSnackbar("Please select an organ to donate.", "error");
                return false;
            }
            showSnackbar(`You have chosen to donate your ${organInput.value}. Thank you!`, "success");
            // In production, allow form submission to update the donor record.
            return true;
        }

        // Attach the form submission handler
        document.querySelector("form").addEventListener("submit", (event) => {
            if (!handleOrganSelection()) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>
