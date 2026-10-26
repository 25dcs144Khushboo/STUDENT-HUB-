function toggleTheme() {
    let body = document.body;
    body.classList.toggle("dark-mode");
    let button = document.getElementById("themeButton");
    if (body.classList.contains("dark-mode")) 
    {
        button.innerHTML = "☀️ Light Mode";
    } else 
    {
         button.innerHTML = "🌙 Dark Mode";
    }
}