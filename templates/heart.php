<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <title>Understanding Heart Donation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a; /* Deep Blue */
            --secondary-color: #000; /* Black */
            --background-light: #f0f9ff;
            --text-dark: #1f2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--background-light);
        }

        .container {
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
        }

        .sidebar {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .sidebar h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 0.5rem;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        .sidebar ul li a:hover {
            color: var(--primary-color);
        }

        .content {
            background-color: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .content header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .content h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 700;
        }

        .content h2 {
            color: var(--secondary-color);
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }

        .testimonial {
            background-color: var(--background-light);
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            margin: 1rem 0;
            font-style: italic;
        }

        .testimonial-author {
            text-align: right;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .statistics {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .stat-box {
            background: var(--background-light);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            width: 150px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-box h3 {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .stat-box p {
            font-size: 1rem;
        }

        .contact {
            background-color: var(--background-light);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 2rem;
            text-align: center;
        }

        .contact h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .contact-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h3>Navigate</h3>
            <ul>
                <li><a href="#why-donate">Why Donate?</a></li>
                <li><a href="#heart-donation-process">Donation Process</a></li>
                <li><a href="#statistics">Key Statistics</a></li>
                <li><a href="#success-stories">Success Stories</a></li>
                <li><a href="#contact">Get in Touch</a></li>
            </ul>
        </aside>

        <main class="content">
            <header>
                <h1>Understanding Heart Donation</h1>
            </header>

            <section id="why-donate">
                <h2>Why Register as a Heart Donor?</h2>
                <p>Heart donation is one of the most precious gifts one can give. A single heart donation can save a life and give someone a second chance at living fully.</p>
                
                <div class="testimonial">
                    <blockquote>
                        "After his death, Cameron left us the first of many blessings – he had taken all appropriate steps to be an organ donor. His dad and I took comfort in knowing we were upholding his decision to give the gift of life to others."
                    </blockquote>
                    <div class="testimonial-author">— Lori, donor mom</div>
                </div>
            </section>

            <section id="heart-donation-process">
                <h2>The Heart Donation Process</h2>
                <p>The process of heart donation is carefully managed to ensure the best possible outcomes:</p>
                <ul>
                    <li>Thorough medical evaluation</li>
                    <li>Careful matching with recipients</li>
                    <li>Coordinated surgical teams</li>
                    <li>Time-sensitive transportation</li>
                </ul>
            </section>

            <section id="statistics" class="statistics">
                <div class="stat-box">
                    <h3>3,500+</h3>
                    <p>People waiting for heart transplants</p>
                </div>
                <div class="stat-box">
                    <h3>75</h3>
                    <p>Lives can be impacted by one donor</p>
                </div>
                <div class="stat-box">
                    <h3>85%</h3>
                    <p>One-year survival rate</p>
                </div>
            </section>

            <section id="success-stories">
                <h2>Success Stories</h2>
                <div class="testimonial">
                    <blockquote>
                        "Every heartbeat reminds me of the gift I've been given."
                    </blockquote>
                    <div class="testimonial-author">— John, Heart Transplant Recipient</div>
                </div>

                <div class="testimonial">
                    <blockquote>
                        "I can now watch my grandchildren grow up."
                    </blockquote>
                    <div class="testimonial-author">— Jane, Heart Transplant Recipient</div>
                </div>
            </section>

            <section id="contact" class="contact">
                <h2>Have Questions?</h2>
                <div class="contact-info">
                    <p><strong>Email:</strong> <a href="mailto:info@lifelink.org">info@lifelink.org</a></p>
                    <p><strong>Phone:</strong> +233 123 456 789</p>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
