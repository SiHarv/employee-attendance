<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <!-- Include necessary CSS and JS -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../../assets/js/lib/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/lib/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/lib/sweetalert2.all.min.js"></script>
    <style>
        .qr-code-container {
            text-align: center;
            margin-top: 15px;
        }
        .qr-code-image {
            max-width: 100px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<?php
// Start the session
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../../includes/admin/header.php';
require_once '../../includes/admin/sidebar.php';
require_once '../../db/connect.php';

// Fetch all employees from the database
$query = "SELECT * FROM users WHERE id != 1 ORDER BY username";
$result = $conn->query($query);

// Count statistics
$total_employees = $result ? $result->num_rows : 0;
$active_employees = 0;
$new_employees = 0;
$inactive_employees = 0;

// Calculate statistics
$current_month = date('Y-m');
if($result && $result->num_rows > 0) {
    $employees = $result->fetch_all(MYSQLI_ASSOC);
    foreach($employees as $employee) {
        // Count employees registered in current month as "new"
        if (substr($employee['created_at'], 0, 7) === $current_month) {
            $new_employees++;
        }
        $active_employees++; // Placeholder: all active
    }
    $inactive_employees = $total_employees - $active_employees;
    // Reset result pointer for display loop
    $result->data_seek(0);
}
?>

<div class="main-content">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold text-primary">Employee Management</h2>
            <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="fas fa-plus-circle me-2"></i>Add Employee
            </button>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Employees</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_employees; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-success text-white shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Active Employees</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $active_employees; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-info text-white shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">New This Month</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $new_employees; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-plus fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-secondary text-white shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Inactive</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $inactive_employees; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-times fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employee List -->
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
                    </div>
                    <div class="col-auto">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search..." id="searchEmployee">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="employeeTable">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>QR Code</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($row['code'])): ?>
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($row['code']); ?>" 
                                                    alt="QR Code" class="img-fluid" style="max-width: 80px;">
                                                <div class="small text-muted mt-1"><?php echo htmlspecialchars($row['code']); ?></div>
                                                <button class="btn btn-sm btn-outline-primary mt-1 download-qr" 
                                                        data-code="<?php echo htmlspecialchars($row['code']); ?>"
                                                        data-username="<?php echo htmlspecialchars($row['username']); ?>">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-danger">No QR Code</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-warning edit-employee" 
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                        data-code="<?php echo htmlspecialchars($row['code'] ?? ''); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-employee" 
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($row['username']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No employees found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEmployeeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="code" class="form-label">QR Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="code" name="code" placeholder="Leave empty for auto-generate">
                            <button class="btn btn-outline-secondary" type="button" id="generateCode">Generate</button>
                        </div>
                        <div id="qrPreview" class="qr-code-container mt-3" style="display:none;">
                            <img id="qrImage" src="" alt="QR Code Preview" class="qr-code-image">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../controller/admin/edit_employee.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" id="edit_password">
                        <div class="form-text">Leave empty to keep current password</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">QR Code</label>
                        <input type="text" class="form-control" name="code" id="edit_code">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle form submission with AJAX and SweetAlert2
document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    submitButton.disabled = true;
    
    // Send AJAX request
    fetch('../../controller/admin/add_employee.php', {
        method: 'POST',
        body: formData,
        credentials: 'include' // Important: Include cookies for session
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        // Reset button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        
        console.log('Server response:', data);
        
        if (data.status === 'success') {
            // Reset the form
            document.getElementById('addEmployeeForm').reset();
            document.getElementById('qrPreview').style.display = 'none';
            
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal')).hide();
            
            // Show success message with SweetAlert2
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#28a745'
            }).then(() => {
                // Reload the page to show the new employee
                window.location.reload();
            });
        } else {
            // Show error message with SweetAlert2
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#dc3545',
                footer: data.debug ? '<a href="#" onclick="console.log(' + JSON.stringify(data.debug) + '); return false;">Show debug info in console</a>' : ''
            });
        }
    })
    .catch(error => {
        // Reset button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        
        console.error('Fetch Error:', error);
        
        // Show error message with SweetAlert2
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred. Please check your session and login status.',
            confirmButtonColor: '#dc3545'
        });
    });
});

// Function to show debug info in console
function showDebugInfo(debugData) {
    try {
        const parsedData = JSON.parse(debugData);
        console.log('Debug information:', parsedData);
        alert('Debug information has been logged to the console');
    } catch (e) {
        console.error('Failed to parse debug data:', e, debugData);
        alert('Failed to parse debug data. See console for details.');
    }
}

// Generate QR Code
document.getElementById('generateCode').addEventListener('click', function() {
    // Generate a unique code (employee ID pattern)
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    let result = "EMP";
    for (let i = 0; i < 6; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('code').value = result;
    
    // Show QR code preview
    const qrImage = document.getElementById('qrImage');
    qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${result}`;
    
    document.getElementById('qrPreview').style.display = 'block';
});

// Download QR code
document.querySelectorAll('.download-qr').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const code = this.getAttribute('data-code');
        const username = this.getAttribute('data-username');
        
        // Create temporary link element
        const link = document.createElement('a');
        link.href = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${code}`;
        link.download = `qrcode_${username.replace(/\s+/g, '_')}.png`;
        link.target = '_blank';
        
        // Append to document, trigger click and remove
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});

// Edit employee
document.querySelectorAll('.edit-employee').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const username = this.getAttribute('data-username');
        const email = this.getAttribute('data-email');
        const code = this.getAttribute('data-code');
        
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_code').value = code;
        document.getElementById('edit_password').value = '';
        
        new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
    });
});
</script>

<?php require_once '../../includes/admin/footer.php'; ?>
</body>
</html>