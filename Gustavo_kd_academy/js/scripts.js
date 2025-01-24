document.addEventListener("DOMContentLoaded", () => {
    const dashboardContent = document.getElementById("dashboardContent");

    // Fetch user role from backend
    fetch("../php/auth.php")
        .then((response) => response.json())
        .then((data) => {
            if (data.status === "success") {
                const role = data.role;

                // Load dashboard content based on user role
                if (role === "admin") {
                    renderAdminDashboard();
                } else if (role === "teacher") {
                    renderTeacherDashboard();
                } else if (role === "student") {
                    renderStudentDashboard();
                } else {
                    dashboardContent.innerHTML = `
                        <h3>Error: Unknown Role</h3>
                    `;
                }
            } else {
                dashboardContent.innerHTML = `
                    <h3>Error loading dashboard: ${data.message}</h3>
                `;
            }
        })
        .catch((error) => {
            console.error("Error fetching role:", error);
            dashboardContent.innerHTML = `
                <h3>Failed to load dashboard</h3>
            `;
        });

    // Admin Dashboard
    function renderAdminDashboard() {
        dashboardContent.innerHTML = `
            <h2>Welcome, Admin</h2>
            <ul class="list-group">
                <li class="list-group-item"><a href="courses_admin.html">Manage Courses</a></li>
                <li class="list-group-item"><a href="users_admin.html">Manage Users</a></li>
                <li class="list-group-item"><a href="attendance_admin.html">View Attendance Records</a></li>
                <li class="list-group-item"><a href="performance_admin.html">View Performance Records</a></li>
            </ul>
        `;
    }

    // Teacher Dashboard
    function renderTeacherDashboard() {
        dashboardContent.innerHTML = `
            <h2>Welcome, Teacher</h2>
            <ul class="list-group">
                <h3>Your Assigned Courses</h3>
                <ul id="teacherCoursesList" class="list-group mb-3"></ul>
                <li class="list-group-item"><a href="attendance_teacher.html">Mark Attendance</a></li>
                <li class="list-group-item"><a href="performance_teacher.html">Record Grades</a></li>
            </ul>
        `;
        fetchTeacherCourses();
    }

    function fetchTeacherCourses() {
        fetch("../php/course.php?teacher_id=current")
            .then((response) => response.json())
            .then((courses) => {
                const coursesList = document.getElementById("teacherCoursesList");
                coursesList.innerHTML = "";
                courses.forEach((course) => {
                    const listItem = document.createElement("li");
                    listItem.className = "list-group-item";
                    listItem.textContent = `${course.name} - ${course.description}`;
                    coursesList.appendChild(listItem);
                });
            })
            .catch((error) => {
                console.error("Error fetching teacher's courses:", error);
            });
    }

    // Student Dashboard
    function renderStudentDashboard() {
        dashboardContent.innerHTML = `
            <h2>Welcome, Student</h2>
            <ul class="list-group">
                <h3>Available Courses</h3>
                <ul id="availableCoursesList" class="list-group mb-3"></ul>
            </ul>
        `;
        fetchAvailableCourses();
    }

    function fetchAvailableCourses() {
        fetch("../php/course.php")
            .then((response) => response.json())
            .then((courses) => {
                const coursesList = document.getElementById("availableCoursesList");
                coursesList.innerHTML = "";
                courses.forEach((course) => {
                    const listItem = document.createElement("li");
                    listItem.className = "list-group-item d-flex justify-content-between align-items-center";
                    listItem.innerHTML = `
                        ${course.name} - ${course.description}
                        <button class="btn btn-success btn-sm" onclick="enrollCourse(${course.id})">Enroll</button>
                    `;
                    coursesList.appendChild(listItem);
                });
            })
            .catch((error) => {
                console.error("Error fetching available courses:", error);
            });
    }

    function enrollCourse(courseId) {
        fetch("../php/enrollment.php", {
            method: "POST",
            body: JSON.stringify({ course_id: courseId }),
            headers: { "Content-Type": "application/json" },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "success") {
                    alert(data.message);
                    fetchAvailableCourses();
                } else {
                    alert(data.message);
                }
            })
            .catch((error) => {
                console.error("Error enrolling in course:", error);
            });
    }
});
