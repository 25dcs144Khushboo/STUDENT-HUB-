let taskInput = document.getElementById("taskInput");
let addButton = document.getElementById("addButton");
let taskList = document.getElementById("taskList");

addButton.addEventListener("click", addTask);

taskInput.addEventListener("keypress", function(event) {
    if (event.key == "Enter") {
        addTask();
    }
});

function addTask() {

    let task = taskInput.value.trim();

    if (task == "") {
        alert("Please enter a task");
        return;
    }

    let li = document.createElement("li");

    let taskText = document.createElement("span");
    taskText.innerText = task;

    taskText.addEventListener("click", function() {
        taskText.classList.toggle("completed");
    });

    let deleteButton = document.createElement("button");
    deleteButton.innerText = "Delete";
    deleteButton.className = "deleteButton";

    deleteButton.addEventListener("click", function() {
        li.remove();
    });

    li.appendChild(taskText);
    li.appendChild(deleteButton);

    taskList.appendChild(li);

    taskInput.value = "";
    taskInput.focus();
}