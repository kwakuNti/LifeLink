window.addEventListener("DOMContentLoaded", () => {
    const splash = document.getElementById("splash");
    const selector = document.getElementById("selector");
    const typewriterText = document.getElementById("typewriter");
  
    // Ensure LifeLink is fully visible before transitioning
    setTimeout(() => {
      splash.classList.add("hidden"); // Hide splash
      selector.classList.remove("hidden"); // Show selector
      loopTypewriterEffect(["Select Your Role", "Be a Hero, Save a Life", "Choose Your Path","Ubuntu"], typewriterText);
    }, 2500); // Keep splash visible for 2.5 seconds
  
    // Typewriter Effect that Loops (Typing & Deleting)
    function loopTypewriterEffect(texts, element) {
      let textIndex = 0;
      let charIndex = 0;
      let isDeleting = false;
  
      function type() {
        let currentText = texts[textIndex];
        let displayText = isDeleting ? currentText.substring(0, charIndex--) : currentText.substring(0, charIndex++);
  
        element.innerHTML = displayText;
  
        if (!isDeleting && charIndex === currentText.length) {
          setTimeout(() => { isDeleting = true; }, 1000); // Wait before deleting
        } else if (isDeleting && charIndex === 0) {
          isDeleting = false;
          textIndex = (textIndex + 1) % texts.length; // Cycle through messages
        }
  
        setTimeout(type, isDeleting ? 50 : 100); // Typing speed
      }
  
      type();
    }
  });
  