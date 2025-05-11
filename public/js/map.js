document.addEventListener("DOMContentLoaded", () => {
  const regions = document.querySelectorAll("path"); // Select all path elements inside SVG
  const selectedRegionElement = document.getElementById("selected-region");
  const findHospitalsButton = document.getElementById("find-hospitals");
  const saveInfoButton = document.getElementById("save-info-button");
  const citySelect = document.getElementById("city-select");
  const manualLocation = document.getElementById("manual-location");
  const hospitalResults = document.getElementById("hospital-results");

  let selectedRegion = "";
  // We'll store the latest user latitude and longitude after a successful hospital fetch.
  let latestUserLat = null;
  let latestUserLng = null;
  // Add a variable to store the selected hospital ID
  let selectedHospitalId = null;

  // Custom snackbar function
  function showSnackbar(message, type) {
    const snackbar = document.getElementById("snackbar");
    snackbar.innerHTML = message;
    snackbar.className = "show " + type;
    setTimeout(() => {
      snackbar.className = snackbar.className.replace("show", "");
    }, 3000);
  }

  // Tooltip for region hovering
  let tooltip = document.createElement("div");
  tooltip.classList.add("tooltip");
  document.body.appendChild(tooltip);

  // Make each region clickable with hover effects
  regions.forEach(region => {
    const regionName = region.getAttribute("name");
    if (!regionName) return;

    region.addEventListener("mouseenter", function (event) {
      this.style.fill = "#1e3a8a"; // Highlight color
      tooltip.style.display = "block";
      tooltip.innerText = regionName;
      tooltip.style.left = event.pageX + "px";
      tooltip.style.top = (event.pageY - 30) + "px";
    });

    region.addEventListener("mouseleave", function () {
      if (!this.classList.contains("selected")) {
        this.style.fill = "#cccccc"; // Reset color
      }
      tooltip.style.display = "none";
    });

    region.addEventListener("click", function (event) {
      event.stopPropagation();
      // Remove previous selection
      regions.forEach(r => r.classList.remove("selected"));
      // Highlight chosen region
      this.classList.add("selected");
      selectedRegion = regionName;
      selectedRegionElement.innerText = selectedRegion;
      findHospitalsButton.disabled = false;
      showSnackbar(`Region "${regionName}" selected! Now pick a city.`, "success");

      // Fetch cities dynamically from DB
      fetch(`../includes/get-cities.php?region=${selectedRegion}`)
        .then(response => response.json())
        .then(cities => {
          citySelect.innerHTML = `<option value="">Choose a city...</option>`;
          if (cities.length === 0) {
            showSnackbar("No cities with Transplant hospitals found in this region. Try other regions", "error");
          }
          cities.forEach(city => {
            let option = document.createElement("option");
            option.value = city;
            option.innerText = city;
            citySelect.appendChild(option);
          });
        })
        .catch(error => {
          console.error("Error fetching cities:", error);
          showSnackbar("Could not load cities. Check console.", "error");
        });
    });

    region.style.pointerEvents = "all";
  });

  // On "Find Nearest Hospitals" button click
  findHospitalsButton.addEventListener("click", () => {
    const selectedCity = citySelect.value;
  
    if (!selectedRegion) {
      showSnackbar("Please select a region first.", "error");
      return;
    }
  
    if (!selectedCity) {
      showSnackbar("Please select a city.", "error");
      return;
    }
  
    if (!navigator.geolocation) {
      showSnackbar("Geolocation not supported by this browser.", "error");
      return;
    }
  
    navigator.geolocation.getCurrentPosition(
      position => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
  
        latestUserLat = lat;
        latestUserLng = lng;
  
        // Fetch hospitals using real-time user location
        const queryParams = `region=${selectedRegion}&city=${selectedCity}&lat=${lat}&lng=${lng}`;
  
        fetch(`../includes/get-hospitals.php?${queryParams}`)
          .then(response => response.json())
          .then(data => {
            if (data.error) {
              showSnackbar(data.error, "error");
            } else if (data.hospitals && Array.isArray(data.hospitals)) {
              displayHospitals(data.hospitals);
            } else if (Array.isArray(data)) {
              displayHospitals(data);
            } else {
              showSnackbar("Unexpected response from server.", "error");
            }
          })
          .catch(error => {
            console.error("Error fetching hospitals:", error);
            showSnackbar("Error fetching hospitals. Check console.", "error");
          });
      },
      error => {
        // If the user denies location access, show SweetAlert2 modal
        if (error.code === error.PERMISSION_DENIED) {
          Swal.fire({
            title: "Location Permission Required",
            text: "To find the nearest hospitals, we need access to your location. Please allow it in your browser settings.",
            icon: "warning",
            confirmButtonText: "Retry",
            showCancelButton: true,
            cancelButtonText: "Cancel",
          }).then((result) => {
            if (result.isConfirmed) {
              // Try again
              window.location.reload(); // Will prompt again if user changed settings
            }
          });
        } else {
          showSnackbar("Location access denied or unavailable.", "error");
        }
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  });
  

  // Save user search information when the "Save Information" button is clicked
  saveInfoButton.addEventListener("click", () => {
    // Validate that we have latest coordinates and a selected hospital
    if (latestUserLat === null || latestUserLng === null) {
      showSnackbar("User location not available. Please search again.", "error");
      return;
    }

    if (selectedHospitalId === null) {
      showSnackbar("Please select a hospital first by clicking 'Confirm Selection'", "warning");
      // Scroll to the hospital results to make it clear
      hospitalResults.scrollIntoView({ behavior: "smooth" });
      return;
    }

    const formData = new FormData();
    formData.append('region', selectedRegion);
    formData.append('city', citySelect.value);
    formData.append('latitude', latestUserLat);
    formData.append('longitude', latestUserLng);
    formData.append('selected_hospital', selectedHospitalId);

    fetch('../actions/update-user-history.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          showSnackbar("User location and selected hospital saved!", "success");
          // Redirect to match page after success
          setTimeout(() => {
            window.location.href = '../templates/match-page';
          }, 1500);
        } else {
          showSnackbar(result.error, "error");
        }
      })
      .catch(error => {
        console.error("Error updating user history:", error);
        showSnackbar("Error updating user history.", "error");
      });
  });

  // Function to handle hospital selection
  window.confirmHospitalSelection = function(hospitalId) {
    // Reset any previously selected hospitals
    const allHospitalCards = document.querySelectorAll('.hospital-card');
    allHospitalCards.forEach(card => {
      card.classList.remove('selected-hospital');
    });

    // Highlight the selected hospital card
    const selectedCard = document.querySelector(`.hospital-card[data-id="${hospitalId}"]`);
    if (selectedCard) {
      selectedCard.classList.add('selected-hospital');
    }

    // Store the selected hospital ID
    selectedHospitalId = hospitalId;
    
    // Enable the save button
    saveInfoButton.disabled = false;
    
    // Show a notification and scroll to the save button
    showSnackbar("Hospital selected! Now click 'Save Search Information' to proceed.", "success");
    
    // Scroll to the save button
    saveInfoButton.scrollIntoView({ behavior: "smooth" });
  };

  // Display hospital results in the left panel
  function displayHospitals(hospitals) {
    if (!hospitals || hospitals.length === 0) {
      hospitalResults.innerHTML = "<h3>No Hospitals Found</h3>";
      showSnackbar("No hospitals found for that location.", "error");
      return;
    }

    hospitalResults.innerHTML = "<h3>Nearest Hospitals</h3>";
    hospitals.forEach((hospital, index) => {
      // Make sure each hospital has an ID
      const hospitalId = hospital.id || (index + 1);
      
      hospitalResults.innerHTML += `
        <div class="hospital-card" data-id="${hospitalId}">
          <h4>${index + 1}. ${hospital.name}</h4>
          <p>${hospital.region}, ${hospital.city}</p>
          <p>Specialization: ${hospital.organ_specialty}</p>
          <p>Distance from current location: ${hospital.distance?.toFixed(2) ?? 'N/A'} km</p>
          <div class="hospital-buttons">
            <button onclick="openGoogleMaps(${hospital.latitude}, ${hospital.longitude})">Get Directions</button>
            <button class="confirm-button" onclick="confirmHospitalSelection(${hospitalId})">Confirm Selection</button>
          </div>
        </div>
      `;
    });

    showSnackbar("Hospitals loaded successfully! Please select one by clicking 'Confirm Selection'", "success");
    hospitalResults.scrollIntoView({ behavior: "smooth" });
  }

  // Open Google Maps for directions
  window.openGoogleMaps = function(lat, lng) {
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`, "_blank");
  };

  // Step-by-step tutorial using SweetAlert2
  function startTutorial() {
    Swal.fire({
      title: 'Tutorial: How to Use the Map',
      text: 'Transplant Hospitals are only in a few regions, Click next to continue',
      icon: 'info',
      confirmButtonText: 'Next',
    }).then((result) => {
      if (result.isConfirmed) {
        stepOne();
      }
    });
  }

  function stepOne() {
    Swal.fire({
      title: 'Step 1: Pick a Region',
      text: 'Click on one of the regions on the map to select it. Your Geometry must be good😉',
      icon: 'info',
      confirmButtonText: 'Next',
    }).then(() => {
      stepTwo();
    });
  }

  function stepTwo() {
    Swal.fire({
      title: 'Step 2: Select a City',
      text: 'If available, choose a city from the dropdown. If none appear, try another region.',
      icon: 'info',
      confirmButtonText: 'Next',
    }).then(() => {
      stepThree();
    });
  }

  function stepThree() {
    Swal.fire({
      title: 'Step 3: Find Hospitals',
      text: 'Click the "Find Nearest Hospitals" button to see a list of matches.',
      icon: 'info',
      confirmButtonText: 'Next',
    }).then(() => {
      stepFour();
    });
  }

  function stepFour() {
    Swal.fire({
      title: 'Step 4: Confirm Hospital Selection',
      text: 'After finding hospitals, click "Save Selection" to save your choice.',
      icon: 'info',
      confirmButtonText: 'Got It!',
    });
  }

  // Call the tutorial on page load
  startTutorial();
});