<!-- /templates/recipient_hospitals.php -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hospitals for Transplant Registration</title>
  <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
  <link rel="manifest" href="../favicon_io/site.webmanifest">
  <style>
    :root {
      --primary: #1e3a8a;
      --primary-hover: #153e7e;
      --text-dark: #333;
      --text-light: #555;
      --background: #f4f4f4;
      --card-bg: #fff;
      --shadow: rgba(0,0,0,0.1);
    }
    
    body {
      font-family: Arial, sans-serif;
      background: var(--background);
      margin: 0;
      padding: 0;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    header {
      background: var(--primary);
      color: white;
      padding: 2rem 0;
      text-align: center;
    }
    
    header h1 {
      margin: 0;
      font-size: 2rem;
    }
    
    header p {
      max-width: 700px;
      margin: 1rem auto 0;
      line-height: 1.6;
      font-size: 1.1rem;
      opacity: 0.9;
    }
    
    .filter-container {
      margin: 2rem 0;
      text-align: center;
    }
    
    .filter-button {
      background: var(--card-bg);
      border: 1px solid var(--primary);
      color: var(--primary);
      padding: 0.5rem 1rem;
      margin: 0 0.5rem;
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .filter-button.active, .filter-button:hover {
      background: var(--primary);
      color: white;
    }
    
    .hospital-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }
    
    .hospital-card {
      background: var(--card-bg);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px var(--shadow);
      transition: all 0.3s;
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    
    .hospital-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .hospital-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      display: block;
    }
    
    .hospital-info {
      padding: 1.5rem;
      text-align: center;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    
    .hospital-info h2 {
      font-size: 1.4rem;
      margin-bottom: 0.75rem;
      color: var(--text-dark);
    }
    
    .hospital-specialty {
      background: #edf2ff;
      color: var(--primary);
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      display: inline-block;
      margin-bottom: 1rem;
      font-size: 0.9rem;
      font-weight: bold;
    }
    
    .hospital-info p {
      margin-bottom: 0.75rem;
      color: var(--text-light);
      font-size: 1rem;
      line-height: 1.4;
    }
    
    .info-row {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 0.5rem;
    }
    
    .info-row svg {
      margin-right: 0.5rem;
      min-width: 20px;
      color: var(--primary);
    }
    
    .hospital-info a {
      color: var(--primary);
      text-decoration: none;
      transition: color 0.3s;
    }
    
    .hospital-info a:hover {
      color: var(--primary-hover);
      text-decoration: underline;
    }
    
    .contact-button {
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 6px;
      padding: 0.8rem 1rem;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s;
      margin-top: auto;
      width: 100%;
      font-weight: bold;
    }
    
    .contact-button:hover {
      background: var(--primary-hover);
    }
    
    .done-button {
      display: block;
      margin: 3rem auto 1rem;
      padding: 1rem 2rem;
      border: none;
      border-radius: 8px;
      background: var(--primary);
      color: white;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s;
      text-align: center;
      font-weight: bold;
    }
    
    .done-button:hover {
      background: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    @media (max-width: 768px) {
      .hospital-container {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      }
      
      .filter-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
      }
      
      .filter-button {
        margin-bottom: 0.5rem;
      }
    }
    
    @media (max-width: 480px) {
      .container {
        padding: 1rem;
      }
      
      header {
        padding: 1.5rem 1rem;
      }
      
      header h1 {
        font-size: 1.7rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>Contact a Hospital to Register for a Transplant</h1>
    <p>Reach out to one of these hospitals to complete your recipient registration. 
      They will guide you through the necessary paperwork and medical evaluations.</p>
  </header>

  <div class="container">
    <div class="filter-container">
      <button class="filter-button active" onclick="filterHospitals('all')">All Hospitals</button>
      <button class="filter-button" onclick="filterHospitals('Kidney')">Kidney Specialists</button>
      <button class="filter-button" onclick="filterHospitals('Both')">Full Transplant Centers</button>
      <button class="filter-button" onclick="filterHospitals('Greater Accra')">Greater Accra</button>
      <button class="filter-button" onclick="filterHospitals('Ashanti')">Ashanti Region</button>
    </div>

    <div class="hospital-container">
      <!-- Hospital Card 1 -->
      <div class="hospital-card" data-region="Greater Accra" data-specialty="Both">
        <img src="../assets/images/korlebu.jpeg" alt="Korle Bu Teaching Hospital">
        <div class="hospital-info">
          <h2>Korle Bu Teaching Hospital</h2>
          <span class="hospital-specialty">Full Transplant Center</span>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <p>Greater Accra Region, Accra</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
            <p>0302739510</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <p>P.O. Box MB 48, Korle Bu, Accra</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="2" y1="12" x2="22" y2="12"></line>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            <p><a href="https://kbth.gov.gh" target="_blank">kbth.gov.gh</a></p>
          </div>
          
          <button class="contact-button" onclick="window.open('https://kbth.gov.gh/contact', '_blank')">Contact Hospital</button>
        </div>
      </div>

      <!-- Hospital Card 2 -->
      <div class="hospital-card" data-region="Greater Accra" data-specialty="Kidney">
        <img src="../assets/images/bank.jpeg" alt="The Bank Hospital">
        <div class="hospital-info">
          <h2>The Bank Hospital</h2>
          <span class="hospital-specialty">Kidney Specialist</span>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <p>Greater Accra Region, Accra</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
            <p>+233 302 739 373</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <p>1st Floor, The Bank Building, Accra</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="2" y1="12" x2="22" y2="12"></line>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            <p><a href="https://thebankhospital.com" target="_blank">thebankhospital.com</a></p>
          </div>
          
          <button class="contact-button" onclick="window.open('https://thebankhospital.com/contact', '_blank')">Contact Hospital</button>
        </div>
      </div>

      <!-- Hospital Card 3 -->
      <div class="hospital-card" data-region="Ashanti" data-specialty="Kidney">
        <img src="../assets/images/komfo.jpeg" alt="Komfo Anokye Teaching Hospital">
        <div class="hospital-info">
          <h2>Komfo Anokye Teaching Hospital</h2>
          <span class="hospital-specialty">Kidney Specialist</span>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <p>Ashanti Region, Kumasi</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
            <p>+233 593 830 400</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <p>P.O. Box 1936, Kumasi</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="2" y1="12" x2="22" y2="12"></line>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            <p><a href="https://kath.gov.gh/" target="_blank">kath.gov.gh</a></p>
          </div>
          
          <button class="contact-button" onclick="window.open('https://kath.gov.gh/contact', '_blank')">Contact Hospital</button>
        </div>
      </div>

      <!-- Hospital Card 4 -->
      <div class="hospital-card" data-region="Greater Accra" data-specialty="Kidney">
        <img src="../assets/images/hospital_default.jpg" alt="37 Military Hospital">
        <div class="hospital-info">
          <h2>37 Military Hospital</h2>
          <span class="hospital-specialty">Kidney Specialist</span>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <p>Greater Accra Region, Accra</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <p>37 Military Hospital Rd, Accra</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="2" y1="12" x2="22" y2="12"></line>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            <p><a href="https://37militaryhospital.org.gh" target="_blank">37militaryhospital.org.gh</a></p>
          </div>
          
          <button class="contact-button" onclick="window.open('https://37militaryhospital.org.gh/contact', '_blank')">Contact Hospital</button>
        </div>
      </div>

      <!-- Hospital Card 5 -->
      <div class="hospital-card" data-region="Greater Accra" data-specialty="Kidney">
        <img src="../assets/images/hospital_default.jpg" alt="University of Ghana Medical Centre">
        <div class="hospital-info">
          <h2>University of Ghana Medical Centre</h2>
          <span class="hospital-specialty">Kidney Specialist</span>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <p>Greater Accra Region, Accra</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
            <p>+233 302 550843</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <p>Legon, Accra</p>
          </div>
          
          <div class="info-row">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="2" y1="12" x2="22" y2="12"></line>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            <p><a href="https://ugmc.ug.edu.gh" target="_blank">ugmc.ug.edu.gh</a></p>
          </div>
          
          <button class="contact-button" onclick="window.open('https://ugmc.ug.edu.gh/contact', '_blank')">Contact Hospital</button>
        </div>
      </div>
    </div>

    <!-- Done button that returns user to homepage -->
    <button class="done-button" onclick="window.location.href='../index.php'">
      Return to Homepage
    </button>
  </div>

  <script>
    function filterHospitals(filter) {
      // Update active button
      const buttons = document.querySelectorAll('.filter-button');
      buttons.forEach(button => {
        button.classList.remove('active');
        if (button.innerText.includes(filter) || (filter === 'all' && button.innerText.includes('All'))) {
          button.classList.add('active');
        }
      });
      
      // Filter hospitals
      const hospitals = document.querySelectorAll('.hospital-card');
      hospitals.forEach(hospital => {
        if (filter === 'all') {
          hospital.style.display = 'block';
        } else if (filter === 'Kidney' || filter === 'Both') {
          if (hospital.getAttribute('data-specialty') === filter) {
            hospital.style.display = 'block';
          } else {
            hospital.style.display = 'none';
          }
        } else {
          if (hospital.getAttribute('data-region') === filter) {
            hospital.style.display = 'block';
          } else {
            hospital.style.display = 'none';
          }
        }
      });
    }
  </script>
</body>
</html>