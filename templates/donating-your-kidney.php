<!DOCTYPE html>
<html>
<head>
    <title>Donating Your Kidney</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a81368914c.js"></script>
    <link rel="stylesheet" href="../public/css/snackbar.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        .subtitle {
            text-align: center;
            font-size: 16px;
            margin-bottom: 40px;
            color: #666;
        }

        .info-section {
            margin-bottom: 40px;
        }

        .info-section h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }

        .info-section p {
            font-size: 16px;
            color: #666;
            line-height: 1.5;
        }

        .info-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 20px;
        }

        .link-card {
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: calc(33% - 20px);
            margin: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .link-card:hover {
            transform: translateY(-5px);
        }

        .link-card h4 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .link-card a {
            color: #007BFF;
            text-decoration: none;
            font-size: 14px;
        }

        .link-card a:hover {
            text-decoration: underline;
        }

        .video-section {
            margin-top: 40px;
            text-align: center;
        }

        .video-section h3 {
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            background-color: #333;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #555;
        }

        @media (max-width: 768px) {
            .link-card {
                width: calc(50% - 20px);
            }
        }

        @media (max-width: 480px) {
            .link-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Donating Your Kidney</h1>
        <p class="subtitle">A kidney transplant can transform the life of someone with kidney disease.</p>

        <!-- Informative Sections -->
        <div class="info-section">
            <h3>Why Donate a Kidney?</h3>
            <p>
                Kidneys are the most commonly donated organs by living people, and about a third of all kidney transplants carried out are from living donors.
            </p>
        </div>

        <!-- Information Links -->
        <div class="info-links">
            <div class="link-card">
                <h4>Living with One Kidney</h4>
                <a href="living-with-one-kidney.php" target="_blank">Learn More</a>
            </div>
            <div class="link-card">
                <h4>Donating to Someone You Don't Know</h4>
                <a href="https://example.com/donating-to-a-stranger" target="_blank">See How it Works</a>
            </div>
            <div class="link-card">
                <h4>Donating a Kidney to a Child</h4>
                <a href="https://example.com/donating-to-a-child" target="_blank">What You Need to Consider</a>
            </div>
            <div class="link-card">
                <h4>Donor Health Considerations</h4>
                <a href="https://example.com/donor-health" target="_blank">How Health is Assessed</a>
            </div>
            <div class="link-card">
                <h4>Surgery and Recovery</h4>
                <a href="https://example.com/surgery-and-recovery" target="_blank">What to Expect</a>
            </div>
            <div class="link-card">
                <h4>Practical and Cultural Considerations</h4>
                <a href="https://example.com/cultural-considerations" target="_blank">Things to Think About</a>
            </div>
        </div>

        <!-- Video Section -->
        <div class="video-section">
            <h3>Watch Videos About Living Organ Donation</h3>
            <p>We have partnered with Transplant TV to provide a range of informative videos about living organ donation.</p>
            <a class="btn" href="https://example.com/transplant-tv" target="_blank">Watch the Transplant TV Films</a>
        </div>

        <!-- Call to Action -->
        <div class="info-section" style="text-align: center; margin-top: 40px;">
            <h3>Become a Living Kidney Donor</h3>
            <a class="btn" href="https://example.com/register-interest" target="_blank">Register Your Interest</a>
        </div>
    </div>
</body>
</html>
