document.addEventListener("DOMContentLoaded", function () {
    let inputs = document.querySelectorAll(".otp-input input");

    // Auto-focus on next input
    inputs.forEach((input, index) => {
        input.addEventListener("input", function () {
            if (this.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        // Handle Backspace
        input.addEventListener("keydown", function (e) {
            if (e.key === "Backspace" && index > 0 && this.value === "") {
                inputs[index - 1].focus();
            }
        });

        // Add scale effect on focus
        input.addEventListener("focus", function () {
            this.style.transform = "scale(1.15)";
        });

        input.addEventListener("blur", function () {
            this.style.transform = "scale(1)";
        });
    });

    // Submit OTP as a single value
    document.getElementById("otpForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent default form submission

        let otpValue = "";
        inputs.forEach(input => otpValue += input.value);
        document.getElementById("otp").value = otpValue;

        this.submit(); // Submit the form
    });
});
