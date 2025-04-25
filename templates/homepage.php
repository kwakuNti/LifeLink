<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <title>LifeLink - Smart Matches, Saved Lives</title>
    <style>
        /* Base Styles */
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap');

        :root {
            --primary: #3056D3;
            --secondary: #4B7BFB;
            --accent: #FEFEE3;
            --dark: #2F2F2F;
            --light: #FFFFFF;
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto Mono', monospace; 
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            line-height: 1.6;
            color: var(--dark);
            background-color: #FAFAFA;
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            text-align: center;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--light);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-secondary:hover {
            background-color: var(--primary);
            color: var(--light);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Header & Navigation */
        header {
            position: relative;
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/api/placeholder/1200/800') center/cover no-repeat;
            color: var(--light);
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            position: absolute;
            width: 100%;
            top: 0;
            z-index: 10;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 50px;
            margin-left: 20px;
        }
        
        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 80%;
            max-width: 800px;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        /* Typewriter Section */
        .mid-section {
            padding: 80px 0;
            text-align: center;
            background-color: var(--primary);
            color: var(--light);
        }
        
        .mid-section h1 {
            font-size: 2.5rem;
            font-weight: 300;
        }
        
        /* Video Section */
        .video-section {
            padding: 80px 0;
            text-align: center;
            background-color: var(--light);
        }
        
        .video-section h2 {
            margin-bottom: 40px;
            font-size: 2rem;
            color: var(--primary);
        }
        
        .video-container {
            position: relative;
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .video-container iframe {
            width: 100%;
            aspect-ratio: 16/9;
            border: none;
            display: block;
        }
        
        /* Facts Section */
        .facts-section {
            padding: 80px 0;
            background-color: #F5F5F5;
        }
        
        .facts-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .facts-image {
            width: 100%;
            max-width: 450px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .facts-text {
            flex: 1;
            min-width: 300px;
        }
        
        .facts-text h2 {
            margin-bottom: 30px;
            font-size: 2rem;
            color: var(--primary);
        }
        
        .facts-text p {
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .highlight {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.4rem;
        }
        
        /* Services Section */
        .services-section {
            padding: 80px 0;
            text-align: center;
            background-color: var(--light);
        }
        
        .services-section h2 {
            margin-bottom: 50px;
            font-size: 2rem;
            color: var(--primary);
        }
        
        .services-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .service {
            background-color: var(--primary);
            color: var(--light);
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 350px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            cursor: pointer;
        }
        
        .service:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }
        
        .service img {
            width: 60px;
            height: 60px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: var(--secondary);
            border-radius: 50%;
        }
        
        .service h3 {
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 100;
        }
        
        .modal-content {
            background-color: var(--light);
            padding: 40px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        
        .modal-content h2 {
            margin-bottom: 20px;
            color: var(--primary);
        }
        
        .modal-content ul {
            list-style: none;
        }
        
        .modal-content li {
            margin-bottom: 10px;
        }
        
        .modal-content a {
            color: var(--primary);
            font-weight: 600;
            transition: var(--transition);
        }
        
        .modal-content a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color: var(--light);
            padding: 60px 0 30px;
        }
        
        .footer-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-logo {
            flex: 1;
            min-width: 200px;
        }
        
        .logo-img {
            height: 60px;
            margin-bottom: 15px;
        }
        
        .footer-column {
            flex: 1;
            min-width: 150px;
        }
        
        .footer-column h4 {
            margin-bottom: 20px;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h4::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 2px;
            background-color: var(--primary);
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column li {
            margin-bottom: 10px;
        }
        
        .footer-column a {
            color: #CCC;
            transition: var(--transition);
        }
        
        .footer-column a:hover {
            color: var(--primary);
            padding-left: 5px;
        }
        
        /* Counter Animation */
        .counter {
            display: inline-block;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .hero-content h1 {
                font-size: 3rem;
            }
            
            .facts-container {
                justify-content: center;
                text-align: center;
            }
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .mid-section h1 {
                font-size: 2rem;
            }
            
            .facts-text, .facts-image {
                max-width: 100%;
            }
            
            .service {
                max-width: 400px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
            }
            
            .footer-column, .footer-logo {
                flex: 100%;
                text-align: center;
            }
            
            .footer-column h4::after {
                left: 50%;
                transform: translateX(-50%);
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
                </div>
            </nav>
        </div>
        <div class="hero-content">
            <h1>Smart Matches, Saved Lives</h1>
            <p>Empower Africa's future by partnering with us today. Your support is vital in creating lasting community transformation.</p>
            <div class="hero-buttons">
                <a href="sign-up" class="btn btn-primary">Act Now</a>
                <a href="#services-section" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
    </header>

    <div class="mid-section">
        <div class="container">
            <h1 id="typewriter">Every donation is a chance for life</h1>
        </div>
    </div>

    <div class="video-section">
        <div class="container">
            <h2>Why Organ Donation Matters</h2>
            <div class="video-container">
                <iframe src="https://www.youtube.com/embed/igTmwJQutFM" title="Why Organ Donation Matters" allowfullscreen></iframe>
            </div>
        </div>
    </div>

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
        <div class="container">
            <h2>Want to know more?</h2>
            <div class="services-container">
                <div class="service" onclick="showModal()">
                    <img src="../assets/images/school_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.png" alt="Educational Programs Icon">
                    <h3>Understand Donation</h3>
                    <p>Learn more about organ donations, including heart, kidney, liver, lungs, and pancreas transplants.</p>
                </div>

                <a href="blog" class="service">
                    <img src="../assets/images/live_help_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.png" alt="Community Outreach Icon">
                    <h3>How can you help</h3>
                    <p>Join our community outreach events to support and uplift local families in need.</p>
                </a>

                <a href="#" class="service">
                    <img src="../assets/images/health_and_safety_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.png" alt="Health Workshops Icon">
                    <h3>Health Workshops</h3>
                    <p>Attend our health workshops to learn about wellness, nutrition, and maintaining a healthy lifestyle.</p>
                </a>
            </div>
        </div>
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

    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <img src="../assets/images/logo-removebg-preview.png" alt="Logo" class="logo-img">
                <p>© 2024 LifeLink, Inc.<br>All rights reserved.</p>
            </div>
            
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Donate</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Policies</h4>
                <ul>
                    <li><a href="privacy-policy">Privacy Policy</a></li>
                    <li><a href="terms-of-service">Terms of service</a></li>
                </ul>
            </div>
            
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

    <script>
        // Modal functionality
        function showModal() {
            document.getElementById('donationModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('donationModal').style.display = 'none';
        }
        
        // Typewriter effect
        const typewriterText = "Every donation is a chance for life";
        const typewriterElement = document.getElementById('typewriter');
        let i = 0;
        
        function typeWriter() {
            if (i < typewriterText.length) {
                typewriterElement.textContent += typewriterText.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            }
        }
        
        // Remove existing text before typing
        typewriterElement.textContent = '';
        
        // Start typewriter effect after a delay
        setTimeout(typeWriter, 1000);
        
        // Counter animation
        const counters = document.querySelectorAll('.counter');
        
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            
            function updateCounter() {
                const count = +counter.innerText;
                const increment = target / 20;
                
                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(updateCounter, 50);
                } else {
                    counter.innerText = target;
                }
            }
            
            // Start the counter animation when the element is in view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCounter();
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            observer.observe(counter);
        });
    </script>
</body>
</html>