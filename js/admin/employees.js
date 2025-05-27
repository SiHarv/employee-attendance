document.addEventListener('DOMContentLoaded', function() {
    const employeeTable = document.getElementById('employeeTable');
    const addEmployeeForm = document.getElementById('addEmployeeForm');
    const updateEmployeeForm = document.getElementById('updateEmployeeForm');
    const employeeIdInput = document.getElementById('employeeIdInput');

    // Fetch and display employees
    function fetchEmployees() {
        fetch('controller/admin/employees.php')
            .then(response => response.json())
            .then(data => {
                employeeTable.innerHTML = '';
                data.forEach(employee => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${employee.id}</td>
                        <td>${employee.name}</td>
                        <td>${employee.email}</td>
                        <td>
                            <button onclick="editEmployee(${employee.id})">Edit</button>
                            <button onclick="deleteEmployee(${employee.id})">Delete</button>
                        </td>
                    `;
                    employeeTable.appendChild(row);
                });
            });
    }

    // Add new employee
    addEmployeeForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(addEmployeeForm);
        fetch('controller/admin/employees.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchEmployees();
                addEmployeeForm.reset();
            } else {
                alert(data.message);
            }
        });
    });

    // Edit employee
    window.editEmployee = function(id) {
        fetch(`controller/admin/employees.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                employeeIdInput.value = data.id;
                document.getElementById('employeeNameInput').value = data.name;
                document.getElementById('employeeEmailInput').value = data.email;
                updateEmployeeForm.style.display = 'block';
            });
    };

    // Update employee
    updateEmployeeForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(updateEmployeeForm);
        fetch('controller/admin/employees.php', {
            method: 'PUT',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchEmployees();
                updateEmployeeForm.reset();
                updateEmployeeForm.style.display = 'none';
            } else {
                alert(data.message);
            }
        });
    });

    // Delete employee
    window.deleteEmployee = function(id) {
        if (confirm('Are you sure you want to delete this employee?')) {
            fetch(`controller/admin/employees.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchEmployees();
                } else {
                    alert(data.message);
                }
            });
        }
    };

    // Initial fetch of employees
    fetchEmployees();
});