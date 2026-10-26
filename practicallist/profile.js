let currentSlide = 0;
let slides = document.querySelectorAll(".slide");

function toggleMenu() {
    document.getElementById("navMenu").classList.toggle("active");
}

function closeNotification() {
    document.getElementById("notification").style.display = "none";
}

function toggleTheme() {
    document.body.classList.toggle("dark");

    let button = document.querySelector(".theme-btn");

    if (document.body.classList.contains("dark")) {
        button.innerHTML = "☀️";
    } else {
        button.innerHTML = "🌙";
    }
}

function changeSlide(direction) {
    slides[currentSlide].classList.remove("active");

    currentSlide = currentSlide + direction;

    if (currentSlide >= slides.length) {
        currentSlide = 0;
    }

    if (currentSlide < 0) {
        currentSlide = slides.length - 1;
    }

    slides[currentSlide].classList.add("active");
}

setInterval(function() {
    changeSlide(1);
}, 4000);

function openModal() {
    document.getElementById("profileModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("profileModal").style.display = "none";
}

function saveProfile() {
    let name = document.getElementById("nameInput").value;
    let email = document.getElementById("emailInput").value;

    document.getElementById("studentName").innerHTML = name;

    closeModal();

    let notification = document.getElementById("notification");

    notification.innerHTML = "Profile updated successfully! <button onclick='closeNotification()'>×</button>";
    notification.style.display = "block";
}

function toggleFAQ(button) {
    let item = button.parentElement;

    item.classList.toggle("active");
}

window.onclick = function(event) {
    let modal = document.getElementById("profileModal");

    if (event.target == modal) {
        closeModal();
    }
};

