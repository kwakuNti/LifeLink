
    document.addEventListener("DOMContentLoaded", function () {
        const text = "Make a Difference Today";
        let index = 0;
        let isDeleting = false;
        const speed = 100; // Speed of typing (in milliseconds)
        const delay = 1000; // Delay after the full text is written before deleting starts
        const typewriterElement = document.getElementById("typewriter");

        function typeWriter() {
            // Adjust speed for deleting vs typing
            let currentSpeed = isDeleting ? speed / 2 : speed;

            if (!isDeleting && index < text.length) {
                // Type the text
                typewriterElement.innerHTML = text.substring(0, index + 1);
                index++;
                setTimeout(typeWriter, currentSpeed);
            } else if (isDeleting && index > 0) {
                // Delete the text
                typewriterElement.innerHTML = text.substring(0, index - 1);
                index--;
                setTimeout(typeWriter, currentSpeed);
            } else if (!isDeleting && index === text.length) {
                // Pause at full text
                setTimeout(() => {
                    isDeleting = true;
                    setTimeout(typeWriter, speed);
                }, delay);
            } else if (isDeleting && index === 0) {
                // Start typing again
                isDeleting = false;
                setTimeout(typeWriter, speed);
            }
        }

        // Start the typewriter effect
        typeWriter();
    });

