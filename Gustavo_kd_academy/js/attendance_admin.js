document.addEventListener("DOMContentLoaded", () => {
    fetchAttendance();

    document.getElementById("attendanceFormElement").addEventListener("submit", (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        fetch("../php/attendance.php", {
            method: "POST",
            body: formData,
        })
            .then((res) => res.json())
            .then((data) => {
                alert(data.message);
                fetchAttendance();
                hideAttendanceForm();
            });
    });
});

function fetchAttendance() {
    fetch("../php/attendance.php")
        .then((res) => res.json())
        .then((data) => {
            const tbody = document.querySelector("#attendanceTable tbody");
            tbody.innerHTML = "";
            data.forEach((record) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${record.id}</td>
                    <td>${record.student_name}</td>
                    <td>${record.course_name}</td>
                    <td>${record.date}</td>
                    <td>${record.status}</td>
                    <td><button onclick="deleteAttendance(${record.id})">Delete</button></td>
                `;
                tbody.appendChild(tr);
            });
        });
}

function deleteAttendance(id) {
    fetch(`../php/attendance.php?id=${id}`, { method: "DELETE" })
        .then((res) => res.json())
        .then((data) => {
            alert(data.message);
            fetchAttendance();
        });
}

function showAttendanceForm() {
    document.getElementById("attendanceForm").style.display = "block";
}

function hideAttendanceForm() {
    document.getElementById("attendanceForm").style.display = "none";
}
