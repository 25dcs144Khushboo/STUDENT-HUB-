document.getElementById("subjects").addEventListener("input", function() {

    let n = parseInt(this.value);
    let marks = document.getElementById("marks");

    marks.innerHTML = "";

    for (let i = 1; i <= n; i++) {
        marks.innerHTML +=
            "<label>Marks in Subject " + i + "</label>" +
            "<input type='number' class='mark' min='0' max='100' placeholder='Enter marks'>";
    }
});

function calculateGrade() {

    let name = document.getElementById("name").value.trim();
    let markInputs = document.querySelectorAll(".mark");
    let result = document.getElementById("result");

    if (name == "") {
        result.innerHTML = "Please enter student name";
        return;
    }

    if (markInputs.length == 0) {
        result.innerHTML = "Please enter number of subjects";
        return;
    }

    let total = 0;
    let failed = false;

    for (let i = 0; i < markInputs.length; i++) {

        let mark = Number(markInputs[i].value);

        if (mark < 0 || mark > 100 || markInputs[i].value == "") {
            result.innerHTML = "Please enter valid marks";
            return;
        }

        total = total + mark;

        if (mark < 40) {
            failed = true;
        }
    }

    let average = total / markInputs.length;
    let grade;

    if (failed) {
        grade = "F";
    } else if (average >= 90) {
        grade = "A+";
    } else if (average >= 80) {
        grade = "A";
    } else if (average >= 70) {
        grade = "B";
    } else if (average >= 60) {
        grade = "C";
    } else if (average >= 50) {
        grade = "D";
    } else {
        grade = "F";
    }

    result.innerHTML =
        "<h3>Result</h3>" +
        "<p>Name: " + name + "</p>" +
        "<p>Total Marks: " + total + "</p>" +
        "<p>Average: " + average.toFixed(2) + "</p>" +
        "<p>Grade: " + grade + "</p>";
}