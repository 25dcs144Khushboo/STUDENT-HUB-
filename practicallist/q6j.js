let button = document.getElementById("themeButton");

button.addEventListener("click", function() {

    document.body.classList.toggle("dark");

    if (document.body.classList.contains("dark")) {
        button.innerText = "Light Mode";
    } else {
        button.innerText = "Dark Mode";
    }
});