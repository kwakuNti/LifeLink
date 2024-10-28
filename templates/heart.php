<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <link rel="stylesheet" href="../public/css/navbar.css">
    <title>Heart</title>
</head>
<body>
<header>
    <nav class="navbar">
        <div class="logo">
            <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
        </div>
        <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="donate.html">Donate</a></li>
            <li><a href="contact.html">Contact</a></li>
        </ul>
    </nav>
</header>
<section class="intro">
        <h1>Why Register Your Decision to Be a Donor?</h1>
        <p>By registering your decision to be an organ, eye, and tissue donor, you are helping to save lives and give hope to those currently waiting for lifesaving organ transplants. One donor can save and heal more than 75 lives.</p>
    </section>

    <section class="quote">
        <blockquote>
            “After his death, Cameron left us the first of many blessings – he had taken all appropriate steps to be an organ, eye, and tissue donor. His dad and I took comfort in knowing we were upholding his decision to give the gift of life to others.”<br><span>– Lori, donor mom</span>
        </blockquote>
    </section>

    <section class="how-it-works">
        <h2>How It Works</h2>
        <p>You can register in the National Donate Life Registry. Any adult age 18 or older can register to be an organ, eye, and tissue donor. Donors aged 15-17 can register with parental consent.</p>
        <ul>
            <li>Opt in to donate for research</li>
            <li>Update your donor profile</li>
            <li>Specify any donation preferences</li>
            <li>Print a document of gift for your records</li>
        </ul>
    </section>

    <section class="stats">
        <h2>Statistics</h2>
        <p>Over 100,000 people are currently waiting for organ transplants in the U.S. alone. One donor can make a lasting impact on multiple lives.</p>
    </section>

    <section class="success-stories">
        <h2>Success Stories</h2>
        <div class="card" onclick="openModal('john')">
            <img src="path/to/john.jpg" alt="John">
            <p><strong>John</strong> – California</p>
            <p>Kidney Transplant</p>
        </div>
        <div class="card" onclick="openModal('jane')">
            <img src="path/to/jane.jpg" alt="Jane">
            <p><strong>Jane</strong> – Texas</p>
            <p>Liver Transplant</p>
        </div>
    </section>

    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="modal-text"></p>
        </div>
    </div>


</body>
</html>
