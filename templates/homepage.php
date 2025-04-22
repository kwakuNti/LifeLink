<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <link rel="stylesheet" href="../public/css/homepage.css">
    <title>Welcome Page</title>
</head>
<body>
<header>
        <div class="hero-section">
            <nav>
                <div class="logo">
                    <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
                </div>
            </nav>
            <div class="overlay"></div>
            <div class="hero-content">
                <h1>Smart Matches, Saved Lives</h1>
                <p>Empower Africa's future by partnering with us today. Your support is vital in creating lasting community transformation.</p>
            </div>
            <div class="hero-buttons">
                <a href="sign-up" class="btn act-now">Act Now</a>
                <a href="#services-section" class="btn learn-more">Learn More</a>
                </div>
        </div>
    </header>
    <div class="mid-section">
    <h1 id="typewriter"></h1> <!-- Changed to have an id -->
</div>
<!-- Video Section -->
<div class="video-section">
    <h2>Why Organ Donation Matters</h2>
    <div class="video-container">
    <iframe width="560" height="315" src="https://www.youtube.com/embed/igTmwJQutFM" ...></iframe>
    </div>
</div>

<!-- Ghana Organ Donation Facts Section -->
<!-- Ghana Organ Donation Facts Section -->
<div class="facts-section">
    <div class="facts-container">
        <img src="../assets/images/waving.png" alt="People Waiting" class="facts-image">
        <div class="facts-text">
            <h2>Organ Donation in Ghana</h2>
            <p><span class="highlight counter" data-target="2500">0</span> Ghanaians are in need of organ transplants.</p>
            <p><span class="highlight counter" data-target="10">0</span>% of patients receive a transplant annually.</p>
            <p><span class="highlight counter" data-target="90">0</span>% of donations are live kidney transplants.</p>
        </div>
    </div>
</div>


<div id="services-section" class="services-section">
    <h2>Want to know?</h2>
    <div class="services-container">
    <div class="service" onclick="showModal()">
    <img src="../assets/images/school_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.png" alt="Educational Programs Icon">
    <h3>Understand Donation</h3>
    <p>Learn more about organ donations, including heart, kidney, liver, lungs, and pancreas transplants.</p>
</div>

<!-- Modal Structure -->
<div id="donationModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Choose Organ to Learn About</h2>
        <ul>
            <li><a href="heart">Heart</a></li>
            <li><a href="kidney">Kidney</a></li>
            <li><a href="liver">Liver</a></li>
            <li><a href="lungs">Lungs</a></li>
            <li><a href="pancreas">Pancreas</a></li>
        </ul>
    </div>
</div>
<a href="blog" style="text-decoration: none; color: white;" >
        <div class="service">
            <img src="../assets/images/live_help_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.png" alt="Community Outreach Icon">
            <h3>How can you help</h3>
            <p>Join our community outreach events to support and uplift local families in need.</p>
        </div>
    </a>
    <a href="#" style="text-decoration: none; color: white;" >
        <div class="service">
            <img src="../assets/images/health_and_safety_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.png" alt="Health Workshops Icon">
            <h3>Health Workshops</h3>
            <p>Attend our health workshops to learn about wellness, nutrition, and maintaining a healthy lifestyle.</p>
        </div>
        </a>
    </div>
</div>
<!-- Footer Section -->
<footer>
    <div class="footer-container">
        <!-- Logo and Copyright -->
        <div class="footer-logo">
            <img src="../assets/images/logo-removebg-preview.png" alt="Logo" class="logo-img">
            <p>© 2024 LifeLink, Inc.<br>All rights reserved.</p>
        </div>
        
        <!-- Quick Links -->
        <div class="footer-column">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Donate</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
        
        <!-- Resources -->
        <div class="footer-column">
            <h4>Policies</h4>
            <ul>
                <li><a href="privacy-policy">Privacy Policy</a></li>
                <li><a href="terms-of-service">Terms of service</a></li>

            </ul>
        </div>
        
        <!-- Connect -->
        <div class="footer-column">
            <h4>Connect</h4>
            <ul>
                <li><a href="#">Facebook</a></li>
                <li><a href="#">Twitter</a></li>
                <li><a href="#">Instagram</a></li>
                <li><a href="#">LinkedIn</a></li>
            </ul>
        </div>
    </div>
</footer>
</body>
<script>
    function showModal() {
            document.getElementById('donationModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('donationModal').style.display = 'none';
        }
</script>
<script src="../public/js/homepage.js"></script>
</html>