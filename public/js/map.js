document.addEventListener("DOMContentLoaded", () => {
    const regions = document.querySelectorAll("path"); // Select all path elements inside SVG

    const selectedRegionElement = document.getElementById("selected-region");
    const findHospitalsButton = document.getElementById("find-hospitals");
    const citySelect = document.getElementById("city-select");
    const manualLocation = document.getElementById("manual-location");
    const hospitalResults = document.getElementById("hospital-results");

    let selectedRegion = "";

    // If you already have a "showSnackbar(message, type)" function from your homepage,
    // be sure it's accessible or re-declare it here.
    // Example:
    function showSnackbar(message, type) {
        const snackbar = document.getElementById("snackbar");
        snackbar.innerHTML = message;
        snackbar.className = "show " + type;
        setTimeout(() => {
            snackbar.className = snackbar.className.replace("show", "");
        }, 3000);
    }

    // -- Tooltip for region hovering --
    let tooltip = document.createElement("div");
    tooltip.classList.add("tooltip");
    document.body.appendChild(tooltip);

    // -- Make each region clickable + hover effect --
    regions.forEach(region => {
        const regionName = region.getAttribute("name");
        if (!regionName) return;

        // Hover Effect
        region.addEventListener("mouseenter", function (event) {
            this.style.fill = "#900C3F"; // Highlight color
            tooltip.style.display = "block";
            tooltip.innerText = regionName;
            tooltip.style.left = event.pageX + "px";
            tooltip.style.top = (event.pageY - 30) + "px";
        });

        region.addEventListener("mouseleave", function () {
            if (!this.classList.contains("selected")) {
                this.style.fill = "#cccccc"; // Default color
            }
            tooltip.style.display = "none";
        });

        // Click to Select Region
        region.addEventListener("click", function (event) {
            event.stopPropagation();

            // Remove previous selection
            regions.forEach(r => r.classList.remove("selected"));

            // Highlight the chosen region
            this.classList.add("selected");
            selectedRegion = regionName;
            selectedRegionElement.innerText = selectedRegion;

            // Enable the button if region is selected
            findHospitalsButton.disabled = false;

            // Prompt user to select a city
            showSnackbar(`Region "${regionName}" selected! Now pick a city.`, "success");

            // Fetch cities dynamically from DB
            fetch(`../includes/get-cities.php?region=${selectedRegion}`)
                .then(response => response.json())
                .then(cities => {
                    citySelect.innerHTML = `<option value="">Choose a city...</option>`;
                    if (cities.length === 0) {
                        showSnackbar("No cities found in this region. Try manual location?", "error");
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

        // Ensure entire region is clickable
        region.style.pointerEvents = "all";
    });

    // -- On "Find Nearest Hospitals" Click --
    findHospitalsButton.addEventListener("click", () => {
        let selectedCity = citySelect.value;
        let enteredLocation = manualLocation.value.trim();

        // Step 1: Validate that a region or location is chosen
        if (!selectedRegion && !selectedCity && !enteredLocation) {
            showSnackbar("Please select a region or enter a location first!", "error");
            return;
        }

        // Step 2: If user selected region but not city or location
        // encourage them to pick a city or type a location
        if (selectedRegion && !selectedCity && !enteredLocation) {
            showSnackbar("Please select a city or type a more specific location.", "error");
            return;
        }

        // Step 3: Make the fetch call
        fetch(`../includes/get-hospitals.php?region=${selectedRegion}&city=${selectedCity}&location=${enteredLocation}`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data)) {
                    // Means we got an error object
                    if (data.error) {
                        showSnackbar(data.error, "error");
                    } else {
                        showSnackbar("Unexpected response from server.", "error");
                    }
                } else {
                    displayHospitals(data);
                }
            })
            .catch(error => {
                console.error("Error fetching hospitals:", error);
                showSnackbar("Error fetching hospitals. Check console.", "error");
            });
    });

    // -- Displaying Hospital Data --
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

        // Show success message & scroll to results
        showSnackbar("Hospitals loaded successfully!", "success");
        hospitalResults.scrollIntoView({ behavior: "smooth" });
    }

    // -- Opening Google Maps for Directions --
    window.openGoogleMaps = function(lat, lng) {
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`, "_blank");
    };
});
