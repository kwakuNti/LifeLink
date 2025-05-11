// public/js/map.js – full file with robust geolocation patch

// Wait for DOM
document.addEventListener("DOMContentLoaded", () => {
  /* ------------------------------------------------------------------ */
  /*  Cache DOM elements                                                */
  /* ------------------------------------------------------------------ */
  const regions              = document.querySelectorAll("svg path"); // gh‑map paths
  const selectedRegionEl     = document.getElementById("selected-region");
  const findBtn              = document.getElementById("find-hospitals");
  const saveBtn              = document.getElementById("save-info-button");
  const citySelect           = document.getElementById("city-select");
  const hospitalResults      = document.getElementById("hospital-results");

  /* ------------------------------------------------------------------ */
  /*  State                                                             */
  /* ------------------------------------------------------------------ */
  let selectedRegion   = "";
  let latestUserLat    = null;
  let latestUserLng    = null;
  let selectedHospital = null;

  /* ------------------------------------------------------------------ */
  /*  Snackbar helper                                                   */
  /* ------------------------------------------------------------------ */
  function showSnackbar (msg, type = "info") {
    const sb = document.getElementById("snackbar");
    sb.textContent = msg;
    sb.className   = `show ${type}`;
    setTimeout(() => sb.className = sb.className.replace("show", ""), 3000);
  }

  /* ------------------------------------------------------------------ */
  /*  TOOLTIP for region hovering                                       */
  /* ------------------------------------------------------------------ */
  const tooltip = document.createElement("div");
  tooltip.className = "tooltip";
  document.body.appendChild(tooltip);

  /* ------------------------------------------------------------------ */
  /*  Region hover / click mapping                                      */
  /* ------------------------------------------------------------------ */
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
    region.addEventListener("click", () => {
      regions.forEach(r => r.classList.remove("selected"));
      region.classList.add("selected");
      selectedRegion          = name;
      selectedRegionEl.textContent = name;
      findBtn.disabled        = false;
      showSnackbar(`Region \"${name}\" selected. Choose a city.`, "success");
      // load cities via AJAX
      fetch(`../includes/get-cities.php?region=${encodeURIComponent(name)}`)
        .then(r => r.json())
        .then(list => {
          citySelect.innerHTML = '<option value="">Choose a city...</option>';
          list.forEach(c => {
            const opt = document.createElement("option");
            opt.value = opt.textContent = c;
            citySelect.appendChild(opt);
          });
        })
        .catch(err => {
          console.error(err);
          showSnackbar("Could not load cities", "error");
        });
    });
    region.style.pointerEvents = "all";
  });

  /* ------------------------------------------------------------------ */
  /*  💡  Robust Geolocation helpers                                    */
  /* ------------------------------------------------------------------ */
  const GEO_TIMEOUT_MS = 10000;
  const GEO_MAX_AGE_MS = 2 * 60e3; // 2‑min cache ok
  let   geoTries       = 0;
  const GEO_MAX_TRIES  = 3;

  // Check permission; resolves true / false
  async function checkGeolocationPermission () {
    if (!navigator.permissions || !navigator.permissions.query) return true; // fallback : prompt
    try {
      const { state } = await navigator.permissions.query({ name: "geolocation" });
      return state !== "denied";
    } catch { return true; }
  }

  // Core – obtains user location with retry / fallback
  function getUserLocation () {
    if (!("geolocation" in navigator)) {
      showSnackbar("Geolocation not supported", "error");
      showManualLocationInput();
      return;
    }

    const opts = (high) => ({ enableHighAccuracy: high, timeout: GEO_TIMEOUT_MS, maximumAge: GEO_MAX_AGE_MS });

    navigator.geolocation.getCurrentPosition(success, attemptFail, opts(true));

    function success (pos) {
      latestUserLat = pos.coords.latitude;
      latestUserLng = pos.coords.longitude;
      geoTries = 0;
      fetchHospitals(latestUserLat, latestUserLng);
    }

    function attemptFail (err) {
      geoTries++;
      if (geoTries < GEO_MAX_TRIES) {
        // retry once with low accuracy (quicker) then once more
        navigator.geolocation.getCurrentPosition(success, finalFail, opts(false));
      } else {
        finalFail(err);
      }
    }

    function finalFail (err) {
      handleGeoLocationError(err); // existing SweetAlert fallback
    }
  }

  /* ------------------------------------------------------------------ */
  /*  Event: Find Nearest Hospitals                                     */
  /* ------------------------------------------------------------------ */
  findBtn.addEventListener("click", () => {
    if (!selectedRegion) { showSnackbar("Select a region first", "error"); return; }
    if (!citySelect.value) { showSnackbar("Select a city", "error"); return; }

    checkGeolocationPermission().then(ok => ok ? getUserLocation() : showManualLocationInput());
  });

  /* ------------------------------------------------------------------ */
  /*  Existing helper functions (displayHospitals, openGoogleMaps, etc.)
      … keep unchanged …                                               */
  /* ------------------------------------------------------------------ */

  // ---- dummy stubs so file is self‑contained (replace with originals) ----
  function fetchHospitals() { /* original logic here */ }
  function handleGeoLocationError(e){ console.warn(e); showSnackbar("Location error", "warning"); }
  function showManualLocationInput(){ /* SweetAlert manual input from original */ }
  window.openGoogleMaps = function(){}; // etc.
  // -----------------------------------------------------------------------

  // Tutorial etc. – keep as‑is
});
