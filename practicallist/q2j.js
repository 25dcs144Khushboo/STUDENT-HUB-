document.getElementById("feedbackForm").addEventListener("submit", function(event) {

    event.preventDefault();

    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let product = document.getElementById("product").value.trim();
    let rating = document.getElementById("rating").value;
    let message = document.getElementById("message").value.trim();
    let result = document.getElementById("result");

    if (name == "") {
        result.innerHTML = "Please enter your name";
        result.style.color = "red";
        return;
    }

    if (email == "") {
        result.innerHTML = "Please enter your email";
        result.style.color = "red";
        return;
    }

    if (!email.includes("@") || !email.includes(".")) {
        result.innerHTML = "Please enter a valid email";
        result.style.color = "red";
        return;
    }

    if (product == "") {
        result.innerHTML = "Please enter product name";
        result.style.color = "red";
        return;
    }

    if (rating == "") {
        result.innerHTML = "Please select a rating";
        result.style.color = "red";
        return;
    }

    if (message == "") {
        result.innerHTML = "Please enter your feedback";
        result.style.color = "red";
        return;
    }

    result.innerHTML = "Feedback submitted successfully!";
    result.style.color = "green";

    document.getElementById("feedbackForm").reset();
});