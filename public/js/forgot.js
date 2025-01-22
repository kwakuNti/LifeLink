const inputs = document.querySelectorAll(".input");


function addcl(){
	let parent = this.parentNode.parentNode;
	parent.classList.add("focus");
}

function remcl(){
	let parent = this.parentNode.parentNode;
	if(this.value == ""){
		parent.classList.remove("focus");
	}
}


inputs.forEach(input => {
	input.addEventListener("focus", addcl);
	input.addEventListener("blur", remcl);
});

function showSnackbar(message) {
    // Get the snackbar DIV
    var snackbar = document.getElementById("snackbar");

    // Set the message
    snackbar.innerHTML = message;

    // Add the "show" class to the snackbar
    snackbar.className = "show";

    // After 3 seconds, remove the show class
    setTimeout(function() {
        snackbar.className = snackbar.className.replace("show", "");
    }, 3000);
}

function validateForm() {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirmPassword').value;

    // Password pattern for at least 8 characters, one uppercase, one number, one special character, and allows dot
    var passwordPattern = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*\.])[A-Za-z0-9!@#\$%\^&\*\.]{8,}$/;

    // Validate if the password matches the pattern
    if (!passwordPattern.test(password)) {
        showSnackbar("Password must be at least 8 characters, an uppercase letter, a number, and a special character.");
        return false;
    }

    // Check if the password and confirm password match
    if (password !== confirmPassword) {
        showSnackbar("Passwords do not match.");
        return false;
    }

    // If validation passes, allow form submission
    return true;
}
