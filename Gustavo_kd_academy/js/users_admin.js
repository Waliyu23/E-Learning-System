document.addEventListener("DOMContentLoaded", () => {
    // Check and fetch data for different sections if required elements exist
    if (document.querySelector("#usersTable")) fetchUsers();

    const userForm = document.getElementById("userFormElement");
    if (userForm) {
        userForm.addEventListener("submit", handleUserFormSubmit);
    }
});

// Fetch users and populate table
function fetchUsers() {
    fetch("../php/user.php")
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data.status !== "success" || !Array.isArray(data.data)) {
                throw new Error("Unexpected response format: Expected an array in 'data'");
            }

            const users = data.data; // Access the users array
            const tableBody = document.querySelector("#usersTable tbody");
            if (!tableBody) return;
            tableBody.innerHTML = ""; // Clear table content

            users.forEach((user) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="showUserForm('${user.id}', '${user.name}', '${user.email}', '${user.role}')">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch((error) => {
            console.error("Error fetching users:", error);
            const tableBody = document.querySelector("#usersTable tbody");
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center">Error fetching users. Please try again.</td></tr>`;
        });
}

// Show the user form modal
function showUserForm(id = "", name = "", email = "", role = "") {
    const userForm = document.getElementById("userForm");
    const userFormTitle = document.getElementById("userFormTitle");
    const userId = document.getElementById("userId");
    const userName = document.getElementById("userName");
    const userEmail = document.getElementById("userEmail");
    const userRole = document.getElementById("userRole");

    // Populate form fields if editing a user
    userId.value = id;
    userName.value = name;
    userEmail.value = email;
    userRole.value = role;

    userFormTitle.textContent = id ? "Edit User" : "Add User";
    userForm.style.display = "block";
}

// Hide the user form modal
function hideUserForm() {
    const userForm = document.getElementById("userForm");
    const userFormElement = document.getElementById("userFormElement");

    userForm.style.display = "none";
    userFormElement.reset(); // Clear form fields
}

// Handle form submission for adding or editing a user
function handleUserFormSubmit(e) {
    e.preventDefault();

    const formData = Object.fromEntries(new FormData(e.target).entries());
    const method = formData.id ? "PUT" : "POST"; // Determine if we're adding or editing
    const url = "../php/user.php";

    // Include only the fields needed
    const payload = {
        id: formData.id || undefined,
        name: formData.name,
        email: formData.email,
        role: formData.role,
    };

    fetch(url, {
        method: method,
        body: JSON.stringify(payload),
        headers: {
            "Content-Type": "application/json",
        },
    })
        .then((response) => {
            if (!response.ok) throw new Error(`Failed to save user. Status: ${response.status}`);
            return response.json();
        })
        .then((data) => {
            if (data.status === "success") {
                alert(data.message);
                hideUserForm();
                fetchUsers(); // Refresh the user list
            } else {
                alert(data.message);
            }
        })
        .catch((error) => console.error("Error saving user:", error));
}


// Delete a user
function deleteUser(id) {
    if (confirm("Are you sure you want to delete this user?")) {
        fetch("../php/user.php", {
            method: "DELETE",
            body: JSON.stringify({ id }),
            headers: {
                "Content-Type": "application/json",
            },
        })
            .then((response) => {
                if (!response.ok) throw new Error(`Failed to delete user. Status: ${response.status}`);
                return response.json();
            })
            .then((data) => {
                if (data.status === "success") {
                    alert(data.message);
                    fetchUsers();
                } else {
                    alert(data.message);
                }
            })
            .catch((error) => console.error("Error deleting user:", error));
    }
}
