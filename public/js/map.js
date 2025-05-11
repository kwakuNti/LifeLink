/* -----------------------------------------------------------
 *  map.js  –  region ⇢ city ⇢ nearest-hospital selector
 *             now with IP-based geolocation fallback
 * ----------------------------------------------------------- */

document.addEventListener("DOMContentLoaded", () => {

  /* ---------- helpers, globals ---------- */

  // quick IP geolocator (± 10-40 km accuracy, good enough for a nearby search)
  async function getApproxLocationFromIP () {
    try {
      const r = await fetch("https://ipapi.co/json/");
      if (!r.ok) return null;
      const { latitude, longitude } = await r.json();
      return { lat: parseFloat(latitude), lng: parseFloat(longitude) };
    } catch { return null; }
  }

  // simple snackbar
  function showSnackbar (msg, type = "") {
    const el = document.getElementById("snackbar");
    el.textContent = msg;
    el.className = `show ${type}`.trim();
    setTimeout(() => (el.className = el.className.replace("show", "")), 3_000);
  }

  /* ---------- DOM refs ---------- */

  const regions              = document.querySelectorAll("svg path[name]");
  const selectedRegionLabel  = document.getElementById("selected-region");
  const citySelect           = document.getElementById("city-select");
  const findBtn              = document.getElementById("find-hospitals");
  const saveBtn              = document.getElementById("save-info-button");
  const hospitalResults      = document.getElementById("hospital-results");

  /* ---------- working state ---------- */

  let selectedRegion   = "";
  let latestUserLat    = null;
  let latestUserLng    = null;
  let selectedHospital = null;
  let geoAttempts      = 0;
  const MAX_GEO_ATTEMPTS = 3;

  /* ---------- map hover/select UI ---------- */

  // tooltip
  const tooltip = document.createElement("div");
  tooltip.classList.add("tooltip");
  document.body.appendChild(tooltip);

  regions.forEach(region => {
    const name = region.getAttribute("name");
    if (!name) return;

    region.addEventListener("mouseenter", e => {
      region.style.fill = "#1e3a8a";
      tooltip.textContent = name;
      tooltip.style.display = "block";
      tooltip.style.left = `${e.pageX}px`;
      tooltip.style.top  = `${e.pageY - 30}px`;
    });

    region.addEventListener("mouseleave", () => {
      if (!region.classList.contains("selected")) region.style.fill = "#cccccc";
      tooltip.style.display = "none";
    });

    region.addEventListener("click", e => {
      e.stopPropagation();
      regions.forEach(r => r.classList.remove("selected"));
      region.classList.add("selected");
      selectedRegion = name;
      selectedRegionLabel.textContent = name;
      findBtn.disabled = false;
      showSnackbar(`Region “${name}” selected. Choose a city.`, "success");

      // load cities
      fetch(`../includes/get-cities.php?region=${encodeURIComponent(name)}`)
        .then(r => r.json())
        .then(cities => {
          citySelect.innerHTML = `<option value="">Choose a city...</option>`;
          cities.forEach(c => {
            citySelect.insertAdjacentHTML("beforeend",
              `<option value="${c}">${c}</option>`);
          });
        })
        .catch(err => {
          console.error(err);
          showSnackbar("Couldn’t load cities.", "error");
        });
    });
  });

  /* ---------- geolocation flow ---------- */

  function handleGeoError (err) {
    console.warn("Geolocation error:", err);

    // try one-off IP fallback on POSITION_UNAVAILABLE or TIMEOUT
    if (err.code === err.POSITION_UNAVAILABLE || err.code === err.TIMEOUT) {
      getApproxLocationFromIP().then(loc => {
        if (loc) {
          ({ lat: latestUserLat, lng: latestUserLng } = loc);
          fetchHospitals(loc.lat, loc.lng);
          showSnackbar("Used approximate location via IP.", "warning");
          return;
        }
        escalate();
      });
    } else {
      escalate();
    }

    function escalate () {
      Swal.fire({
        title: "Location Error",
        text: "Unable to obtain precise location. Enter it manually?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Manual input"
      }).then(res => {
        if (res.isConfirmed) showManualLocationInput();
      });
    }
  }

  function getUserLocation () {
    if (!navigator.geolocation) {
      showSnackbar("Browser has no Geolocation.", "error");
      showManualLocationInput();
      return;
    }

    navigator.geolocation.getCurrentPosition(
      pos => {
        latestUserLat = pos.coords.latitude;
        latestUserLng = pos.coords.longitude;
        geoAttempts = 0;
        fetchHospitals(latestUserLat, latestUserLng);
      },
      err => {
        if (++geoAttempts <= MAX_GEO_ATTEMPTS) {
          return getUserLocation();          // retry
        }
        handleGeoError(err);
      },
      { enableHighAccuracy: true, timeout: 15_000, maximumAge: 0 }
    );
  }

  /* ---------- hospital search ---------- */

  function fetchHospitals (lat, lng) {
    const city = citySelect.value;
    if (!selectedRegion || !city) {
      showSnackbar("Select region & city first.", "error");
      return;
    }

    showSnackbar("Searching hospitals…", "info");

    fetch(`../includes/get-hospitals.php?region=${encodeURIComponent(selectedRegion)}&city=${encodeURIComponent(city)}&lat=${lat}&lng=${lng}`)
      .then(r => r.json())
      .then(hospitals => displayHospitals(Array.isArray(hospitals.hospitals) ? hospitals.hospitals : hospitals))
      .catch(e => {
        console.error(e);
        showSnackbar("Server error fetching hospitals.", "error");
      });
  }

  function displayHospitals (arr) {
    if (!arr || !arr.length) {
      hospitalResults.innerHTML = `<div class="empty-state"><span class="material-icons">sentiment_dissatisfied</span><p>No hospitals found.</p></div>`;
      return;
    }
    hospitalResults.innerHTML = `<h3>Nearest Hospitals (${arr.length})</h3>`;
    arr.forEach((h, i) => {
      const id = h.id ?? i + 1;
      hospitalResults.insertAdjacentHTML("beforeend", `
        <div class="hospital-card" data-id="${id}">
          <h4>${i + 1}. ${h.name}</h4>
          <p>${h.region}, ${h.city}</p>
          <p>Specialization: ${h.organ_specialty ?? "General Transplant"}</p>
          <p>Distance: ${(h.distance ?? 0).toFixed(2)} km</p>
          <div class="hospital-buttons">
            <button class="btn directions-btn" onclick="openGoogleMaps(${h.latitude},${h.longitude})"><span class="material-icons">directions</span>Directions</button>
            <button class="btn confirm-button" onclick="confirmHospitalSelection(${id})"><span class="material-icons">check_circle</span>Confirm</button>
          </div>
        </div>`);
    });
    hospitalResults.scrollIntoView({ behavior: "smooth" });
  }

  /* ---------- UI actions ---------- */

  findBtn.addEventListener("click", getUserLocation);

  window.openGoogleMaps = (lat,lng) => {
    const base = "https://www.google.com/maps/dir/";
    const url  = latestUserLat ? `${base}${latestUserLat},${latestUserLng}/${lat},${lng}` :
                                 `${base}?api=1&destination=${lat},${lng}`;
    window.open(url, "_blank");
  };

  window.confirmHospitalSelection = id => {
    document.querySelectorAll(".hospital-card").forEach(c => c.classList.remove("selected-hospital"));
    const card = document.querySelector(`.hospital-card[data-id="${id}"]`);
    if (card) card.classList.add("selected-hospital");
    selectedHospital = id;
    saveBtn.disabled = false;
    showSnackbar("Hospital selected → Save Selection & Continue", "success");
  };

  /* ---------- manual-location helper ---------- */

  function showManualLocationInput () {
    Swal.fire({
      title: "Enter Your Location",
      html: `
        <input id="mlat" class="swal2-input" placeholder="Latitude" type="number" step="0.0001">
        <input id="mlng" class="swal2-input" placeholder="Longitude" type="number" step="0.0001">
      `,
      preConfirm: () => {
        const lat = parseFloat(document.getElementById("mlat").value);
        const lng = parseFloat(document.getElementById("mlng").value);
        if (Number.isFinite(lat) && Number.isFinite(lng)) return { lat, lng };
        Swal.showValidationMessage("Please enter valid coordinates.");
        return false;
      }
    }).then(res => {
      if (res.isConfirmed) {
        latestUserLat = res.value.lat;
        latestUserLng = res.value.lng;
        fetchHospitals(latestUserLat, latestUserLng);
      }
    });
  }

  /* ---------- run once on load ---------- */

  navigator.permissions?.query({ name: "geolocation" })
    .then(p => { if (p.state === "denied") showSnackbar("Enable location for better results.", "warning"); })
    .catch(()=>{});

});
