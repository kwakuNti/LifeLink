document.addEventListener("DOMContentLoaded", function () {
    /*** 🖋️ Typewriter Effect ***/
    const text = "Make a Difference Today";
    let index = 0;
    let isDeleting = false;
    const speed = 100; // Speed of typing
    const delay = 1000; // Pause before deleting
    const typewriterElement = document.getElementById("typewriter");

    function typeWriter() {
        let currentSpeed = isDeleting ? speed / 2 : speed;

        if (!isDeleting && index < text.length) {
            typewriterElement.innerHTML = text.substring(0, index + 1);
            index++;
            setTimeout(typeWriter, currentSpeed);
        } else if (isDeleting && index > 0) {
            typewriterElement.innerHTML = text.substring(0, index - 1);
            index--;
            setTimeout(typeWriter, currentSpeed);
        } else if (!isDeleting && index === text.length) {
            setTimeout(() => {
                isDeleting = true;
                setTimeout(typeWriter, speed);
            }, delay);
        } else if (isDeleting && index === 0) {
            isDeleting = false;
            setTimeout(typeWriter, speed);
        }
    }
    typeWriter(); // Start typewriter effect

    /*** 🔢 Counter Animation - Numbers Count Up When Visible ***/
    const counters = document.querySelectorAll('.counter');
    const speedCounter = 50; // Adjust speed (lower is faster)

    function animateCounters() {
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const increment = target / speedCounter;

                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(updateCount, 20);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });
    }

    /*** 🎬 Fade-in Animation for Sections When Scrolling ***/
    const fadeInElements = document.querySelectorAll('.fade-in');

    function fadeInOnScroll(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); // Run only once per element
            }
        });
    }

    const fadeInObserver = new IntersectionObserver(fadeInOnScroll, { threshold: 0.4 });
    fadeInElements.forEach(element => fadeInObserver.observe(element));

    /*** 🎥 Intersection Observer for Counter Animation ***/
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.disconnect(); // Stops running after first view
            }
        });
    }, { threshold: 0.5 }); // Runs when 50% of the section is visible

    observer.observe(document.querySelector('.facts-section'));

    /*** 🖱️ Hover Effects for Sections ***/
    const hoverSections = document.querySelectorAll('.hover-effect');

    hoverSections.forEach(section => {
        section.addEventListener('mouseover', () => {
            section.style.transform = "scale(1.05)";
            section.style.boxShadow = "0 10px 30px rgba(0,0,0,0.3)";
        });
        section.addEventListener('mouseout', () => {
            section.style.transform = "scale(1)";
            section.style.boxShadow = "0 5px 15px rgba(0,0,0,0.2)";
        });
    });
});
