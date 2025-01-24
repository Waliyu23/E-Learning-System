document.addEventListener("DOMContentLoaded", () => {
    // Initially fetch performance records
    fetchPerformance();

    // Show Add Performance button and form for Admin and Teacher
    fetch("../php/auth.php")
        .then((response) => response.json())
        .then((data) => {
            if (data.status === "success") {
                const role = data.role;
                if (role === "admin" || role === "teacher") {
                    // Show performance form and button
                    document.getElementById("addPerformanceButton").style.display = "block";
                    document.getElementById("actionsHeader").style.display = "table-cell";

                    // Handle form submission
                    const performanceFormElement = document.getElementById("performanceFormElement");
                    performanceFormElement.addEventListener("submit", (e) => {
                        e.preventDefault();

                        const formData = new FormData(performanceFormElement);
                        // Send the form data to PHP to add a performance record
                        fetch("../php/performance.php", {
                            method: "POST",
                            body: formData,
                        })
                            .then((response) => response.json())
                            .then((data) => {
                                alert(data.message); // Show success/error message
                                fetchPerformance(); // Refresh performance data
                                hidePerformanceForm(); // Close form after submission
                            })
                            .catch((error) => console.error("Error adding performance:", error));
                    });
                }
            }
        });

    // Fetch and display performance records (for admin, teacher, or student)
    function fetchPerformance() {
        fetch("../php/performance.php")
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "success" && Array.isArray(data.data)) {
                    const performanceRecords = data.data;
                    const tableBody = document.querySelector("#performanceTable tbody");
                    tableBody.innerHTML = ""; // Clear previous records

                    performanceRecords.forEach((record) => {
                        const row = document.createElement("tr");
                        row.innerHTML = `
                            <td>${record.id}</td>
                            <td>${record.student_name}</td>
                            <td>${record.course_name}</td>
                            <td>${record.grade}</td>
                            <td>${record.remarks}</td>
                            ${
                                record.deletable
                                    ? `<td><button onclick="deletePerformance(${record.id})">Delete</button></td>`
                                    : `<td></td>`
                            }
                        `;
                        tableBody.appendChild(row);
                    });
                } else {
                    alert("No performance records found or data format issue.");
                }
            })
            .catch((error) => {
                console.error("Error fetching performance records:", error);
                alert("Error fetching performance records. Please try again.");
            });
    }

    // Delete performance record
    function deletePerformance(id) {
        if (confirm("Are you sure you want to delete this performance record?")) {
            fetch(`../php/performance.php?id=${id}`, { method: "DELETE" })
                .then((response) => response.json())
                .then((data) => {
                    alert(data.message); // Show success/error message
                    fetchPerformance(); // Refresh performance data
                });
        }
    }

    // Show performance form modal
    window.showPerformanceForm = function () {
        document.getElementById("performanceForm").style.display = "block";
    };

    // Hide performance form modal
    window.hidePerformanceForm = function () {
        document.getElementById("performanceForm").style.display = "none";
    };
});
