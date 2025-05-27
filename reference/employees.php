<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Define constant to allow access to include files
define('ADMIN_ACCESS', true);

require_once('../db/config.php');

try {
    // Get all employees from database - joining with time_logs instead of attendance_reports
    $stmt = $pdo->query("
        SELECT e.id, e.name, e.email, e.qr_code, e.created_at,
               CASE 
                   WHEN t.id IS NOT NULL THEN 
                       CASE 
                           WHEN TIME(t.time_in) > '09:00:00' THEN 'late'
                           ELSE 'present'
                       END
                   ELSE 'absent'
               END as today_status
        FROM employees e
        LEFT JOIN (
            SELECT employee_id, id, time_in
            FROM time_logs
            WHERE DATE(time_in) = CURRENT_DATE
        ) t ON e.id = t.employee_id
        ORDER BY e.name ASC
    ");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get totals for summary
    $totalEmployees = count($employees);
    
    $presentToday = 0;
    $absentToday = 0;
    $lateToday = 0;
    
    foreach ($employees as $employee) {
        if ($employee['today_status'] == 'present') {
            $presentToday++;
        } elseif ($employee['today_status'] == 'absent') {
            $absentToday++;
        } elseif ($employee['today_status'] == 'late') {
            $lateToday++;
        }
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Include header and sidebar
include_once('../includes/admin_header.php');
include_once('../includes/admin_sidebar.php');
?>

<head>
    <!-- ...existing head content... -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
</head>

<style>
    /* Base styles for layout */
    body {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    
    .content-wrapper {
        background-color: #f8f9fa;
        position: relative;
    }
    
    /* Modal specific fixes */
    .modal {
        background: rgba(0, 0, 0, 0.5);
    }
    
    .modal-dialog {
        margin: 100px auto !important; /* Adjust margin-top to be below header */
        max-width: 500px;
        width: 95%;
    }
    
    .modal-content {
        position: relative;
        z-index: 2000;
        pointer-events: auto !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.5);
    }
    
    /* Remove default backdrop */
    .modal-backdrop {
        display: none !important;
    }
    
    /* Other styles remain the same */
    .card {
        margin-bottom: 20px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: none;
    }
    
    .table-responsive {
        margin-bottom: 0;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3">Employee Management</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bi bi-plus-circle me-1"></i> Add Employee
            </button>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Employee Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Employees</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalEmployees; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
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
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    Present Today</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $presentToday; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-danger text-white shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    Absent Today</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $absentToday; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-warning text-white shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    Late Today</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $lateToday; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clock-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employee List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
                <div class="form-inline">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search...">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="employeeTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>QR Code</th>
                                <th>Email</th>
                                <th>Today's Status</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($employees)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No employees found. Add some employees to get started!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($employees as $employee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="showQRCode('<?php echo htmlspecialchars($employee['name']); ?>', '<?php echo htmlspecialchars($employee['qr_code']); ?>')">
                                                <i class="bi bi-qr-code-scan"></i> View QR
                                            </button>
                                        </td>
                                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td>
                                            <?php if ($employee['today_status'] == 'present'): ?>
                                                <span class="badge bg-success">Present</span>
                                            <?php elseif ($employee['today_status'] == 'absent'): ?>
                                                <span class="badge bg-danger">Absent</span>
                                            <?php elseif ($employee['today_status'] == 'late'): ?>
                                                <span class="badge bg-warning text-dark">Late</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($employee['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-warning"
                                                        onclick="editEmployee(<?php echo $employee['id']; ?>, 
                                                                             '<?php echo htmlspecialchars($employee['name']); ?>', 
                                                                             '<?php echo htmlspecialchars($employee['email']); ?>', 
                                                                             '<?php echo htmlspecialchars($employee['qr_code']); ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" 
                                                        onclick="confirmDeleteEmployee(<?php echo $employee['id']; ?>, '<?php echo htmlspecialchars($employee['name']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addEmployeeForm" action="process_employee.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="qr_code" class="form-label">QR Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="qr_code" name="qr_code" required>
                                <button class="btn btn-outline-secondary" type="button" id="generateQRCode">Generate</button>
                            </div>
                            <div id="qr_preview" class="mt-2"></div>
                            <div class="form-text">Click Generate to create a unique QR code or enter your own code.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editEmployeeForm" action="process_employee.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="employee_id" id="edit_employee_id">
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_qr_code" class="form-label">QR Code</label>
                            <input type="text" class="form-control" id="edit_qr_code" name="qr_code" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                            <div class="form-text">Leave blank to keep the current password.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Employee Modal -->
    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteEmployeeModalLabel">Delete Employee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this employee? <strong id="delete_employee_name"></strong></p>
                    <p class="text-danger">This action cannot be undone. All attendance records for this employee will also be deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="process_employee.php" method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="employee_id" id="delete_employee_id">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View QR Code Modal -->
    <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="qrCodeModalLabel">Employee QR Code</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h6 id="qrCodeEmployeeName"></h6>
                    <div id="qrCodeImage" class="mt-3 mb-3"></div>
                    <p>QR Code: <strong id="qrCodeValue"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="downloadQRCodeBtn">Download</button>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="mt-5 py-3 text-center text-muted">
        <p>&copy; <?php echo date('Y'); ?> Employee Time Tracking System</p>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#employeeTable tbody tr');
        
        tableRows.forEach(row => {
            const name = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const email = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const qrCode = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            
            if (name.includes(searchValue) || email.includes(searchValue) || qrCode.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Generate random QR code
    document.getElementById('generateQRCode').addEventListener('click', function() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = 'EMP';
        for (let i = 0; i < 5; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        // Set the value in the input field
        document.getElementById('qr_code').value = result;
        
        // Generate QR code preview using goqr.me API
        const previewContainer = document.getElementById('qr_preview');
        if (previewContainer) {
            previewContainer.innerHTML = `
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${result}" 
                     alt="QR Code Preview" class="img-fluid">
            `;
        }
    });

    // View QR code modal function
    function showQRCode(name, code) {
        document.getElementById('qrCodeEmployeeName').textContent = name;
        
        const qrCodeElement = document.getElementById('qrCodeImage');
        qrCodeElement.innerHTML = `
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${code}" 
                 alt="QR Code" class="img-fluid">
        `;
        
        // Set up download button
        document.getElementById('downloadQRCodeBtn').onclick = function() {
            const link = document.createElement('a');
            link.href = `https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=${code}`;
            link.download = 'qrcode-' + code + '.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
        
        const qrModal = new bootstrap.Modal(document.getElementById('qrCodeModal'));
        qrModal.show();
    }
    
    // Edit employee
    function editEmployee(id, name, email, qrCode) {
        document.getElementById('edit_employee_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_qr_code').value = qrCode;
        document.getElementById('edit_password').value = '';
        
        const editModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
        editModal.show();
    }
    
    // Confirm delete employee
    function confirmDeleteEmployee(id, name) {
        document.getElementById('delete_employee_id').value = id;
        document.getElementById('delete_employee_name').textContent = name;
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteEmployeeModal'));
        deleteModal.show();
    }   
    
    // View employee details
    function viewEmployee(id) {
        window.location.href = 'view_employee.php?id=' + id;
    }
    
    // Remove all the complex modal handling code and just keep the basic functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Force scroll to top of page
        window.scrollTo(0, 0);
        
        // Fix modal backdrop issue
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                document.body.style.overflow = 'hidden';
            });
            
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.style.overflow = '';
            });
        });
    });
</script>

</body>
</html>