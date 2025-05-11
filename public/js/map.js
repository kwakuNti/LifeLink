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
  // Track geolocation attempts
  let geoLocationAttempts = 0;
  const maxGeoLocationAttempts = 3;

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

  // Check for geolocation permission status and pre-request if needed
  function checkGeolocationPermission() {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject("Geolocation not supported by this browser.");
        return;
      }

      // Some browsers implement permission API
      if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' })
          .then(permissionStatus => {
            if (permissionStatus.state === 'granted') {
              resolve(true);
            } else if (permissionStatus.state === 'prompt') {
              // Pre-request permission to avoid timing issues
              navigator.geolocation.getCurrentPosition(
                () => resolve(true),
                (err) => {
                  console.warn("Geolocation permission check failed:", err.message);
                  resolve(false);
                },
                { timeout: 5000, enableHighAccuracy: false }
              );
            } else {
              resolve(false);
            }
          })
          .catch(err => {
            console.warn("Permission API not fully supported:", err);
            // Fall back to testing geolocation directly
            testGeolocation().then(resolve).catch(reject);
          });
      } else {
        // Permissions API not available, test geolocation directly
        testGeolocation().then(resolve).catch(reject);
      }
    });
  }

  // Test if geolocation works by making a simple request
  function testGeolocation() {
    return new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(
        () => resolve(true),
        (err) => {
          console.warn("Geolocation test failed:", err.message);
          resolve(false);
        },
        { timeout: 5000, enableHighAccuracy: false, maximumAge: 60000 }
      );
    });
  }

  // Handle geolocation errors with better user experience
  function handleGeoLocationError(error) {
    let errorMessage = "";
    switch (error.code) {
      case error.PERMISSION_DENIED:
        errorMessage = "Location permission was denied. Please enable location services in your browser settings.";
        break;
      case error.POSITION_UNAVAILABLE:
        errorMessage = "Location information is unavailable. Please try again or use manual location.";
        break;
      case error.TIMEOUT:
        errorMessage = "Location request timed out. Please check your connection and try again.";
        break;
      default:
        errorMessage = "An unknown error occurred while retrieving your location.";
    }

    Swal.fire({
      title: "Location Error",
      html: `
        <p>${errorMessage}</p>
        <p>Would you like to:</p>
      `,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Try Again",
      cancelButtonText: "Enter Location Manually",
    }).then((result) => {
      if (result.isConfirmed) {
        // Try geolocation again if under max attempts
        if (geoLocationAttempts < maxGeoLocationAttempts) {
          geoLocationAttempts++;
          getUserLocation();
        } else {
          showManualLocationInput();
        }
      } else {
        showManualLocationInput();
      }
    });
  }

  // Show manual location input
  function showManualLocationInput() {
    Swal.fire({
      title: 'Enter Your Location',
      html: `
        <div style="margin-bottom: 15px;">
          <label for="manual-lat" style="display:block; margin-bottom:5px; text-align:left;">Latitude:</label>
          <input id="manual-lat" type="number" class="swal2-input" placeholder="e.g. 5.6037" step="0.0001" min="-90" max="90">
        </div>
        <div>
          <label for="manual-lng" style="display:block; margin-bottom:5px; text-align:left;">Longitude:</label>
          <input id="manual-lng" type="number" class="swal2-input" placeholder="e.g. -0.1870" step="0.0001" min="-180" max="180">
        </div>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'Submit',
      preConfirm: () => {
        const lat = document.getElementById('manual-lat').value;
        const lng = document.getElementById('manual-lng').value;
        
        if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
          Swal.showValidationMessage('Please enter valid coordinates');
          return false;
        }
        
        return { lat: parseFloat(lat), lng: parseFloat(lng) };
      }
    }).then((result) => {
      if (result.isConfirmed) {
        latestUserLat = result.value.lat;
        latestUserLng = result.value.lng;
        fetchHospitals(latestUserLat, latestUserLng);
      }
    });
  }

  // Get user location with better error handling
  function getUserLocation() {
    if (!navigator.geolocation) {
      showSnackbar("Geolocation not supported by this browser.", "error");
      showManualLocationInput();
      return;
    }

    showSnackbar("Detecting your location...", "info");
    
    navigator.geolocation.getCurrentPosition(
      position => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        latestUserLat = lat;
        latestUserLng = lng;
        geoLocationAttempts = 0; // Reset attempts on success
        
        // Fetch hospitals using real-time user location
        fetchHospitals(lat, lng);
      },
      error => {
        console.error("Geolocation error:", error.message);
        handleGeoLocationError(error);
      },
      {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0
      }
    );
  }

  // Fetch hospitals from API
  function fetchHospitals(lat, lng) {
    const selectedCity = citySelect.value;

    if (!selectedRegion) {
      showSnackbar("Please select a region first.", "error");
      return;
    }
  
    if (!selectedCity) {
      showSnackbar("Please select a city.", "error");
      return;
    }

    showSnackbar("Searching for hospitals near you...", "info");
    
    const queryParams = `region=${selectedRegion}&city=${selectedCity}&lat=${lat}&lng=${lng}`;
  
    fetch(`../includes/get-hospitals.php?${queryParams}`)
      .then(response => {
        if (!response.ok) {
          throw new Error(`Server responded with status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.error) {
          showSnackbar(data.error, "error");
        } else if (data.hospitals && Array.isArray(data.hospitals)) {
          displayHospitals(data.hospitals);
        } else if (Array.isArray(data)) {
          displayHospitals(data);
        } else {
          showSnackbar("Unexpected response from server.", "error");
          console.error("Unexpected data structure:", data);
        }
      })
      .catch(error => {
        console.error("Error fetching hospitals:", error);
        showSnackbar(`Error fetching hospitals: ${error.message}`, "error");
      });
  }

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

    // First check permission to avoid timing issues
    checkGeolocationPermission()
      .then(hasPermission => {
        if (hasPermission) {
          getUserLocation();
        } else {
          // Permission already denied or not available
          showManualLocationInput();
        }
      })
      .catch(error => {
        console.error("Permission check error:", error);
        showManualLocationInput();
      });
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
      .then(response => {
        if (!response.ok) {
          throw new Error(`Server responded with status: ${response.status}`);
        }
        return response.json();
      })
      .then(result => {
        if (result.success) {
          showSnackbar("User location and selected hospital saved!", "success");
          // Redirect to match page after success
          setTimeout(() => {
            window.location.href = '../templates/match-page';
          }, 1500);
        } else {
          showSnackbar(result.error || "Unknown error occurred", "error");
        }
      })
      .catch(error => {
        console.error("Error updating user history:", error);
        showSnackbar(`Error updating user history: ${error.message}`, "error");
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
      // Scroll to the selected card
      selectedCard.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    // Store the selected hospital ID
    selectedHospitalId = hospitalId;
    
    // Enable the save button
    saveInfoButton.disabled = false;
    
    // Show a notification and scroll to the save button
    showSnackbar("Hospital selected! Now click 'Save Search Information' to proceed.", "success");
    
    // Scroll to the save button after a short delay
    setTimeout(() => {
      saveInfoButton.scrollIntoView({ behavior: "smooth" });
    }, 500);
  };

  // Display hospital results in the left panel
  function displayHospitals(hospitals) {
    if (!hospitals || hospitals.length === 0) {
      hospitalResults.innerHTML = `
        <div class="empty-state">
          <span class="material-icons">sentiment_dissatisfied</span>
          <p>No hospitals found for that location.</p>
        </div>`;
      showSnackbar("No hospitals found for that location.", "error");
      return;
    }

    hospitalResults.innerHTML = `<h3>Nearest Hospitals (${hospitals.length})</h3>`;
    hospitals.forEach((hospital, index) => {
      // Make sure each hospital has an ID
      const hospitalId = hospital.id || (index + 1);
      
      hospitalResults.innerHTML += `
        <div class="hospital-card" data-id="${hospitalId}">
          <h4>${index + 1}. ${hospital.name}</h4>
          <p>${hospital.region}, ${hospital.city}</p>
          <p>Specialization: ${hospital.organ_specialty || 'General Transplant'}</p>
          <p>Distance from current location: ${hospital.distance?.toFixed(2) ?? 'N/A'} km</p>
          <div class="hospital-buttons">
            <button class="btn directions-btn" onclick="openGoogleMaps(${hospital.latitude}, ${hospital.longitude})">
              <span class="material-icons">directions</span> Get Directions
            </button>
            <button class="btn confirm-button" onclick="confirmHospitalSelection(${hospitalId})">
              <span class="material-icons">check_circle</span> Confirm Selection
            </button>
          </div>
        </div>
      `;
    });

    showSnackbar("Hospitals loaded successfully! Please select one by clicking 'Confirm Selection'", "success");
    hospitalResults.scrollIntoView({ behavior: "smooth" });
  }

  // Open Google Maps for directions
  window.openGoogleMaps = function(lat, lng) {
    if (latestUserLat && latestUserLng) {
      // If we have user coordinates, use them as origin
      window.open(`https://www.google.com/maps/dir/${latestUserLat},${latestUserLng}/${lat},${lng}`, "_blank");
    } else {
      // Otherwise, just set the destination
      window.open(`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`, "_blank");
    }
  };

  // Initialize by checking geolocation permission on page load
  checkGeolocationPermission()
    .then(hasPermission => {
      if (!hasPermission) {
        showSnackbar("Please allow location access for accurate results.", "warning");
      }
    })
    .catch(error => {
      console.warn("Initial permission check failed:", error);
    });

  // Step-by-step tutorial using SweetAlert2
  function startTutorial() {
    Swal.fire({
      title: 'Welcome to Transplant Hospital Finder',
      text: 'Transplant Hospitals are only in a few regions. Click next to continue.',
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
      text: 'Click on one of the regions on the map to select it.',
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
      text: 'After finding hospitals, click "Confirm Selection" and then "Save Selection & Continue".',
      icon: 'info',
      confirmButtonText: 'Got It!',
    });
  }

  // Call the tutorial on page load
  setTimeout(startTutorial, 1000);
});