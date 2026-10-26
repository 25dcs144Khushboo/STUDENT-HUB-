let students = [];

function addStudent() {

    let name = document.getElementById("name").value;
    let marks1 = Number(document.getElementById("marks1").value);
    let marks2 = Number(document.getElementById("marks2").value);
    let marks3 = Number(document.getElementById("marks3").value);

    if (name == "" || marks1 == "" || marks2 == "" || marks3 == "") {
        alert("Please enter all details");
        return;
    }

    let marks = [marks1, marks2, marks3];

    let student = {
        name: name,
        marks: marks
    };

    students.push(student);

    alert("Student added successfully");

    document.getElementById("name").value = "";
    document.getElementById("marks1").value = "";
    document.getElementById("marks2").value = "";
    document.getElementById("marks3").value = "";
}

function calculateTotal(marks) {

    let total = 0;

    for (let i = 0; i < marks.length; i++) {
        total = total + marks[i];
    }

    return total;
}

function calculatePercentage(total) {
    return total / 3;
}

function calculateGrade(percentage) {

    if (percentage >= 90) {
        return "A+";
    } else if (percentage >= 80) {
        return "A";
    } else if (percentage >= 70) {
        return "B";
    } else if (percentage >= 60) {
        return "C";
    } else if (percentage >= 50) {
        return "D";
    } else {
        return "F";
    }
}

function showResults() {

    let result = document.getElementById("result");

    if (students.length == 0) {
        result.innerHTML = "<p>No student records found.</p>";
        return;
    }

    let output = "<table>";
    output += "<tr>";
    output += "<th>Name</th>";
    output += "<th>Total</th>";
    output += "<th>Percentage</th>";
    output += "<th>Grade</th>";
    output += "</tr>";

    for (let i = 0; i < students.length; i++) {

        let total = calculateTotal(students[i].marks);
        let percentage = calculatePercentage(total);
        let grade = calculateGrade(percentage);

        output += "<tr>";
        output += "<td>" + students[i].name + "</td>";
        output += "<td>" + total + "</td>";
        output += "<td>" + percentage.toFixed(2) + "%</td>";
        output += "<td>" + grade + "</td>";
        output += "</tr>";
    }

    output += "</table>";

    result.innerHTML = output;
}