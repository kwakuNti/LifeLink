<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <title>One Kidney, Full Life: A Journey of Hope and Healing</title>
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
            border-bottom: 2px solid var(--secondary-color);
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

        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
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
                <li><a href="#living-with-one-kidney">Living with One Kidney</a></li>
                <li><a href="#long-term-risks">Long-Term Risks</a></li>
                <li><a href="#side-effects">Side Effects</a></li>
                <li><a href="#quality-of-life">Quality of Life</a></li>
                <li><a href="#donor-stories">Donor Stories</a></li>
                <li><a href="#video">Kidney Donation Journey</a></li>
                <li><a href="#contact">Get in Touch</a></li>
            </ul>
        </aside>

        <main class="content">
            <header>
                <h1>One Kidney, Full Life</h1>
            </header>

            <section id="living-with-one-kidney">
                <h2>Living with One Kidney</h2>
                <p>Thousands of individuals live fulfilling lives with just one kidney. Whether through birth, medical necessity, or a selfless donation, having a single kidney doesn't define your potential.</p>
                
                <div class="testimonial">
                    <blockquote>
                        "Donating my kidney wasn't just an act of kindness—it was a transformative journey that showed me the incredible resilience of the human body and spirit."
                    </blockquote>
                    <div class="testimonial-author">— Sarah M., Kidney Donor</div>
                </div>
            </section>

            <section id="long-term-risks">
                <h2>Understanding the Risks</h2>
                <p>While kidney donation is a significant decision, medical research consistently shows that donors maintain excellent health. Regular check-ups and a healthy lifestyle are key to long-term well-being.</p>
            </section>

            <section id="video">
                <h2>Kidney Donation Journey</h2>
                <div class="video-container">
                    <iframe 
                        src="https://www.youtube.com/embed/Dd5vy55ksjQ" 
                        title="Living with a Single Kidney"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            </section>

            <section id="donor-stories">
                <h2>Real Donor Stories</h2>
                <div class="testimonial">
                    <blockquote>
                        "When I learned I could save a life by donating a kidney, I didn't hesitate. The joy of knowing someone else gets a second chance is priceless."
                    </blockquote>
                    <div class="testimonial-author">— Michael T., Living Donor</div>
                </div>
            </section>

            <section id="contact" class="contact">
                <h2>Have Questions?</h2>
                <div class="contact-info">
                    <p><strong>Email:</strong> <a href="mailto:enquiries@nhsbt.nhs.uk">enquiries@nhsbt.nhs.uk</a></p>
                    <p><strong>Phone:</strong> 0300 123 23 23</p>
                </div>
            </section>
        </main>
    </div>
</body>
</html>