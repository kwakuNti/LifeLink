document.getElementById('searchBar').addEventListener('input', function (e) {
    const term = e.target.value.toLowerCase();
    const sections = document.querySelectorAll('section');

    sections.forEach(section => {
        if (section.textContent.toLowerCase().includes(term)) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
});
