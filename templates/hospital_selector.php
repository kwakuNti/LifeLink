<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Choose Hospital</title>
  <link rel="stylesheet" type="text/css" href="../public/css/hospital.css">
    <link rel="stylesheet" href="../public/css/homepage.css">
    <link rel="stylesheet" href="../public/css/snackbar.css">
  <style>
        /* Basic styling for the organ selector page */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
            margin-top: -20px;
        }
        .logo img {
            width: 300px;
            margin-bottom: 0px;
        }
        .form-title {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: bold;
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
      <img src="../assets/images/icon-hospital.png" alt="LifeLink Logo">
    </div>
    <h2 class="form-title">Choose Hospital</h2>
    <form id="hospitalForm" onsubmit="return handleHospitalSelection()">
      <!-- Modern Dropdown for Hospital Selection -->
      <div class="modern-dropdown" id="modernDropdown">
        <div class="dropdown-selected" id="selectedOption">
          Select a hospital
        </div>
        <div class="dropdown-options" id="dropdownOptions">
          <!-- Options will be loaded dynamically from the backend -->
        </div>
      </div>
      <!-- Hidden input to store the selected hospital ID -->
      <input type="hidden" id="hospital" name="hospital" value="">
      <!-- Submit Button -->
            <input type="submit" class="btn" value="Next">
    </form>
    <div id="snackbar"></div>
  </div>

  <script>
    // Get references to elements
    const dropdown = document.getElementById('modernDropdown');
    const dropdownOptions = document.getElementById('dropdownOptions');
    const selectedOption = document.getElementById('selectedOption');
    const hospitalInput = document.getElementById('hospital');
    const snackbar = document.getElementById('snackbar');

    // Toggle dropdown visibility when clicking the selected area
    selectedOption.addEventListener('click', () => {
      dropdownOptions.style.display = dropdownOptions.style.display === 'block' ? 'none' : 'block';
    });

    // Fetch hospital options from backend
    fetch('../includes/hospitals.php')
      .then(response => response.json())
      .then(data => {
        data.forEach(hospital => {
          const option = document.createElement('div');
          option.textContent = hospital.name;
          option.dataset.value = hospital.id; // store hospital id
          dropdownOptions.appendChild(option);
        });
      })
      .catch(error => {
        showSnackbar("Error loading hospitals.", "error");
        console.error(error);
      });

    // Handle selection of a hospital
    dropdownOptions.addEventListener('click', (event) => {
      const value = event.target.getAttribute('data-value');
      if (value) {
        selectedOption.textContent = event.target.textContent;
        hospitalInput.value = value;
        dropdownOptions.style.display = 'none';
      }
    });

    // Handle form submission: redirect to hospital_login.php with the chosen hospital id in the query string
    function handleHospitalSelection() {
      if (!hospitalInput.value) {
        showSnackbar("Please select a hospital.", "error");
        return false;
      }
      window.location.href = `hospital_login.php?hospital_id=${hospitalInput.value}`;
      return false; // Prevent normal form submission
    }

    // Snackbar function
    function showSnackbar(message, type) {
      snackbar.textContent = message;
      snackbar.className = "snackbar show " + type;
      setTimeout(() => {
        snackbar.className = snackbar.className.replace("show " + type, "");
      }, 3000);
    }
  </script>
</body>
</html>
