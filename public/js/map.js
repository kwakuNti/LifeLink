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
            this.style.fill = "#1e3a8a"; // Highlight color            tooltip.style.display = "block";
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
                        showSnackbar("No cities found in this region. Try other regions", "error");
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
        let selectedCity = citySelect.value;
        let enteredLocation = manualLocation.value.trim();

        if (selectedRegion && !selectedCity && !enteredLocation) {
            showSnackbar("Please select a city", "error");
            return;
        }

        // Build query parameters
        const queryParams = `region=${selectedRegion}&city=${selectedCity}&location=${enteredLocation}`;

        fetch(`../includes/get-hospitals.php?${queryParams}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showSnackbar(data.error, "error");
                } else if (data.hospitals && Array.isArray(data.hospitals)) {
                    displayHospitals(data.hospitals);
                    // Store the user location from the response if provided
                    if (data.user_location) {
                        latestUserLat = data.user_location.latitude;
                        latestUserLng = data.user_location.longitude;
                    }
                    // Enable the "Save Information" button after successful hospital fetch
                    saveInfoButton.disabled = false;
                } else if (Array.isArray(data)) {
                    displayHospitals(data);
                    saveInfoButton.disabled = false;
                } else {
                    showSnackbar("Unexpected response from server.", "error");
                }
            })
            .catch(error => {
                console.error("Error fetching hospitals:", error);
                showSnackbar("Error fetching hospitals. Check console.", "error");
            });
    });

    // Save user search information when the "Save Information" button is clicked
    saveInfoButton.addEventListener("click", () => {
        // Validate that we have latest coordinates (from get-hospitals response)
        if (latestUserLat === null || latestUserLng === null) {
            showSnackbar("User location not available. Please search again.", "error");
            return;
        }

        const formData = new FormData();
        formData.append('region', selectedRegion);
        formData.append('city', citySelect.value);
        formData.append('latitude', latestUserLat);
        formData.append('longitude', latestUserLng);
        // Optionally: formData.append('selected_hospital', hospitalId);

        fetch('../actions/update-user-history.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showSnackbar("User location and search history saved!", "success");
            } else {
                showSnackbar(result.error, "error");
            }
        })
        .catch(error => {
            console.error("Error updating user history:", error);
            showSnackbar("Error updating user history.", "error");
        });
    });

    // Display hospital results in the left panel
    function displayHospitals(hospitals) {
        if (!hospitals || hospitals.length === 0) {
            hospitalResults.innerHTML = "<h3>No Hospitals Found</h3>";
            showSnackbar("No hospitals found for that location.", "error");
            return;
        }

        hospitalResults.innerHTML = "<h3>Nearest Hospitals</h3>";
        hospitals.forEach((hospital, index) => {
            hospitalResults.innerHTML += `
                <div class="hospital-card">
                    <h4>${index + 1}. ${hospital.name}</h4>
                    <p>${hospital.region}, ${hospital.city}</p>
                    <p>Specialization: ${hospital.organ_specialty}</p>
                    <p>Distance: ${hospital.distance?.toFixed(2) ?? 'N/A'} km</p>
                    <button onclick="openGoogleMaps(${hospital.latitude}, ${hospital.longitude})">Get Directions</button>
                </div>
            `;
        });

        showSnackbar("Hospitals loaded successfully!", "success");
        hospitalResults.scrollIntoView({ behavior: "smooth" });
    }

    // Open Google Maps for directions
    window.openGoogleMaps = function(lat, lng) {
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`, "_blank");
    };
});
