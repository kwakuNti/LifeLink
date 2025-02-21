<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <title>Organ Donation Stories - A Blog of Hope</title>
    <link rel="stylesheet" href="../public/css/blog.css">

</head>
<body>
    <header>
        <h1>Organ Donation Stories</h1>
        <nav>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="stories">Stories</a></li>
                <li><a href="about">About</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="featured-post">
            <h2>Featured Story</h2>
            <article>
                <h3>A New Lease on Life: Sarah's Heart Transplant Journey</h3>
                <p>Sarah was only 25 when she received the gift of life through a heart transplant. This is her inspiring story of hope and gratitude...</p>
                <a href="#" class="read-more">Read More</a>
            </article>
        </section>

        <section id="recent-posts">
            <h2>Recent Stories</h2>
            <div class="post-grid">
                <article>
                    <h3>The Ripple Effect of Organ Donation</h3>
                    <p>How one donor's decision touched the lives of multiple recipients and their families...</p>
                    <a href="#" class="read-more">Read More</a>
                </article>
                <article>
                    <h3>Living Donors: A Special Kind of Hero</h3>
                    <p>Meet John, who donated a kidney to a stranger and started a chain of life-saving donations...</p>
                    <a href="#" class="read-more">Read More</a>
                </article>
            </div>
        </section>

        <section id="comments">
            <h2>Comments</h2>
            <div id="comment-list">
                <!-- Comments will be dynamically added here -->
            </div>
            <form id="comment-form">
                <textarea id="comment-text" placeholder="Share your thoughts..." required></textarea>
                <button type="submit">Post Comment</button>
            </form>
        </section>
    </main>

    <footer>
        <section id="signup">
            <h2>Join Our Community</h2>
            <p>Sign up to receive updates on organ donation stories and news.</p>
            <form id="signup-form">
                <input type="email" id="email" placeholder="Enter your email" required>
                <button type="submit">Subscribe</button>
            </form>
        </section>
        <p>&copy; 2025 Organ Donation Stories. All rights reserved.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>

