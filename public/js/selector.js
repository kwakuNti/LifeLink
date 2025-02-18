function selectRole(role) {
    let container = document.querySelector(".container");
    container.classList.add("fade-out"); // Add fade-out animation

    setTimeout(() => {
        if (role === "donor") {
            window.location.href = "../templates/donor-signup.php";
        } else if (role === "recipient") {
            window.location.href = "../templates/recipient-signup.php";
        }
    }, 500); // Allow time for animation
}
