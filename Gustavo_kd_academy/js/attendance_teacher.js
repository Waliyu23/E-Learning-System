document.addEventListener("DOMContentLoaded", () => {
    fetchTeacherAttendance();

    const attendanceFormElement = document.getElementById("attendanceFormElement");

    // Handle form submission for adding attendance
    attendanceFormElement.addEventListener("submit", (e) => {
        e.preventDefault();

        const formData = new FormData(attendanceFormElement);
        fetch("../php/attendance.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "success") {
                    alert(data.message);
                    fetchTeacherAttendance();
                    hideAttendanceForm();
                } else {
                    alert(data.message);
                }
            })
            .catch((error) => console.error("Error adding attendance:", error));
    });
});

// Fetch attendance records for the teacher
function fetchTeacherAttendance() {
    fetch("../php/attendance.php")
        .then((response) => response.json())
        .then((attendanceRecords) => {
            const tableBody = document.querySelector("#attendanceTable tbody");
            tableBody.innerHTML = "";

            attendanceRecords.forEach((record) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${record.id}</td>
                    <td>${record.student_name}</td>
                    <td>${record.course_name}</td>
                    <td>${record.date}</td>
                    <td>${record.status}</td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch((error) => console.error("Error fetching attendance records:", error));
}

// Show and hide the attendance form
function showAttendanceForm() {
    document.getElementById("attendanceForm").style.display = "block";
}

function hideAttendanceForm() {
    document.getElementById("attendanceForm").style.display = "none";
}
