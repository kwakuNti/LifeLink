<!-- /templates/recipient_hospitals.php -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hospitals for Transplant Registration</title>
  <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
  <link rel="manifest" href="../favicon_io/site.webmanifest">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 2rem;
    }
    h1 {
      text-align: center;
      color: #1e3a8a;
      margin-bottom: 1rem;
    }
    .hospital-container {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      justify-content: center;
      margin-top: 2rem;
    }
    .hospital-card {
      width: 300px;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      transition: all 0.3s;
    }
    .hospital-card:hover {
      transform: translateY(-2px);
    }
    .hospital-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      display: block;
    }
    .hospital-info {
      padding: 1rem;
      text-align: center;
    }
    .hospital-info h2 {
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
      color: #333;
    }
    .hospital-info p {
      margin-bottom: 0.5rem;
      color: #555;
      font-size: 0.95rem;
    }
    .done-button {
      display: block;
      margin: 2rem auto 0;
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 6px;
      background: #1e3a8a;
      color: #fff;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s;
      text-align: center;
    }
    .done-button:hover {
      background: #153e7e;
    }
  </style>
</head>
<body>
  <h1>Contact a Hospital to Register for a Transplant</h1>
  <p style="text-align:center; max-width:600px; margin:0 auto;">
    We recommend that you reach out to one of these hospitals to complete your recipient registration. 
    They will guide you through the necessary paperwork and medical evaluations. 
  </p>

  <div class="hospital-container">
    <!-- EXAMPLE hospital card 1 -->
    <div class="hospital-card">
      <img src="../assets/images/korlebu.jpeg" alt="Korle Bu Hospital">
      <div class="hospital-info">
        <h2>Korle Bu Teaching Hospital</h2>
        <p>Region: Greater Accra</p>
        <p>Phone: 0302739510</p>
        <p>Address: P.O. Box MB 48, Korle Bu, Accra</p>
        <p>Website: <a href="https://kbth.gov.gh" target="_blank">kbth.gov.gh</a></p>
      </div>
    </div>

    <!-- EXAMPLE hospital card 2 -->
    <div class="hospital-card">
      <img src="../assets/images/bank.jpeg" alt="The Bank Hospital">
      <div class="hospital-info">
        <h2>The Bank Hospital</h2>
        <p>Region: Greater Accra</p>
        <p>Phone: +233 302 739 373</p>
        <p>Address: 1st Floor, The Bank Building, Accra</p>
        <p>Website: <a href="https://thebankhospital.com" target="_blank">thebankhospital.com</a></p>
      </div>
    </div>

    <!-- EXAMPLE hospital card 3 -->
    <div class="hospital-card">
      <img src="../assets/images/komfo.jpeg" alt="Komfo Anokye Hospital">
      <div class="hospital-info">
        <h2>Komfo Anokye Teaching Hospital</h2>
        <p>Region: Ashanti</p>
        <p>Phone: +233 593 830 400</p>
        <p>Address: P.O. Box 1936, Kumasi</p>
        <p>Website: <a href="https://kath.gov.gh/" target="_blank">komfoanokyehospital.org</a></p>
      </div>
    </div>

    <!-- Add more hospitals as needed -->
  </div>

  <!-- Done button that returns user to homepage -->
  <button class="done-button" onclick="window.location.href='../index.php'">
    Done
  </button>
</body>
</html>
