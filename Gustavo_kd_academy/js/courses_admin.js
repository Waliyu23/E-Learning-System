document.addEventListener("DOMContentLoaded", () => {
    fetchCourses();
    fetchTeachers();

    // Handle add course form submission
    const courseForm = document.getElementById("courseForm");
    courseForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const formData = new FormData(courseForm);
        fetch("../php/course.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "success") {
                    alert(data.message);
                    fetchCourses();
                    hideAddCourseForm();
                } else {
                    alert(data.message);
                }
            });
    });
});

// Fetch all courses and display them in the table
function fetchCourses() {
    fetch("../php/course.php")
        .then((response) => response.json())
        .then((courses) => {
            const tableBody = document.querySelector("#coursesTable tbody");
            tableBody.innerHTML = ""; // Clear previous table rows

            courses.forEach((course) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${course.id}</td>
                    <td>${course.name}</td>
                    <td>${course.description}</td>
                    <td>${course.teacher_name || "Unassigned"}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="deleteCourse(${course.id})">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch((error) => {
            console.error("Error fetching courses:", error);
        });
}

// Fetch all teachers and populate the dropdown
function fetchTeachers() {
    fetch("../php/users.php?role=teacher") // Replace with your backend endpoint for fetching teachers
        .then((response) => response.json())
        .then((teachers) => {
            const teacherDropdown = document.getElementById("teacherId");
            teacherDropdown.innerHTML = '<option value="">-- Select Teacher --</option>'; // Clear existing options
            teachers.forEach((teacher) => {
                const option = document.createElement("option");
                option.value = teacher.id;
                option.textContent = teacher.name;
                teacherDropdown.appendChild(option);
            });
        })
        .catch((error) => {
            console.error("Error fetching teachers:", error);
        });
}

// Delete a course
function deleteCourse(id) {
    if (confirm("Are you sure you want to delete this course?")) {
        fetch(`../php/course.php`, {
            method: "DELETE",
            body: new URLSearchParams({ id }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "success") {
                    alert(data.message);
                    fetchCourses();
                } else {
                    alert(data.message);
                }
            })
            .catch((error) => {
                console.error("Error deleting course:", error);
            });
    }
}

// Show/Hide add course form
function showAddCourseForm() {
    document.getElementById("addCourseForm").style.display = "block";
}

function hideAddCourseForm() {
    document.getElementById("addCourseForm").style.display = "none";
}
