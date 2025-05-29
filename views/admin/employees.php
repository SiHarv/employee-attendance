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
        /* Prevent page scrolling */
        html, body {
            overflow: hidden !important;
            height: 100%;
        }
        body {
            position: relative;
        }
        /* Allow scrolling only inside table-responsive if needed */
        .table-responsive {
            max-height: 80vh;
            min-height: 300px;
            overflow-y: auto;
        }
        .qr-code-container {
            text-align: center;
            margin-top: 15px;
        }
        .qr-code-image {
            max-width: 100px;
            margin-bottom: 5px;
        }
        /* Align search bar to the right */
        .table-searchbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
    </style>
</head>
<body>

<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../../db/connect.php';
require_once '../../includes/admin/header.php';
require_once '../../includes/admin/sidebar.php';

// Get totals for summary cards
$totalEmployees = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];

$today = date('Y-m-d');
$presentQuery = "SELECT COUNT(*) AS count FROM morning_time_log WHERE DATE(time_in) = ? AND status = 'present'";
$stmtPresent = $conn->prepare($presentQuery);
$stmtPresent->bind_param("s", $today);
$stmtPresent->execute();
$presentToday = $stmtPresent->get_result()->fetch_assoc()['count'];

$lateQuery = "SELECT COUNT(*) AS count FROM morning_time_log WHERE DATE(time_in) = ? AND status = 'late'";
$stmtLate = $conn->prepare($lateQuery);
$stmtLate->bind_param("s", $today);
$stmtLate->execute();
$lateToday = $stmtLate->get_result()->fetch_assoc()['count'];

$absentToday = $totalEmployees - ($presentToday + $lateToday);
if($absentToday < 0) $absentToday = 0;

// Fetch all employees
$query = "SELECT u.*, 
         (SELECT COUNT(*) FROM morning_time_log WHERE employee_id = u.id) AS attendance_count,
         (SELECT status FROM morning_time_log WHERE employee_id = u.id AND DATE(time_in) = CURDATE() LIMIT 1) AS today_status
         FROM users u ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<div class="main-content">
    <div class="container-fluid mt-4">
        <!-- Employee Stats Cards -->
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
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    Present Today</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $presentToday; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x"></i>
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
                                <i class="fas fa-times-circle fa-2x"></i>
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
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
                </div>
                <!-- Add Employee Button above the searchbar, aligned right -->
                <div class="d-flex justify-content-end align-items-center mt-3 mb-2">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="fas fa-user-plus me-1"></i> Add Employee
                    </button>
                </div>
                <div class="table-searchbar">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="searchEmployee" class="form-control form-control-sm" placeholder="Search...">
                        <button class="btn btn-outline-secondary btn-sm" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="employeeTable" width="100%" cellspacing="0" style="border-radius: 6px; overflow: hidden;">
                        <thead>
                            <tr style="background: #181c1f;">
                                <th class="text-white">Username</th>
                                <th class="text-white">Email</th>
                                <th class="text-white">Today's Status</th>
                                <th class="text-white">Registered</th>
                                <th class="text-white">View Details</th>
                                <th class="text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr
                                        <?php
                                            // Row coloring for status
                                            if (isset($row['today_status'])) {
                                                if ($row['today_status'] === 'present') {
                                                    echo 'style="background: #d6e6e3;"';
                                                } elseif ($row['today_status'] === 'late') {
                                                    echo 'style="background: #fff7df;"';
                                                } elseif ($row['today_status'] === 'absent') {
                                                    echo 'style="background: #ffeaea;"';
                                                }
                                            }
                                        ?>
                                    >
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="text-center">
                                            <?php
                                            if (isset($row['today_status'])) {
                                                if ($row['today_status'] === 'present') {
                                                    echo '<span class="badge bg-success">Present</span>';
                                                } elseif ($row['today_status'] === 'late') {
                                                    echo '<span class="badge bg-warning text-dark">Late</span>';
                                                } elseif ($row['today_status'] === 'absent') {
                                                    echo '<span class="badge bg-danger">Absent</span>';
                                                }
                                            } else {
                                                echo '<span class="badge bg-danger">Absent</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary view-details" 
                                                    data-employee-id="<?php echo $row['id']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#employeeDetailsModal">
                                                View details
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary edit-employee"
                                                        data-employee-id="<?php echo $row['id']; ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editEmployeeModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-employee"
                                                        data-employee-id="<?php echo $row['id']; ?>"
                                                        data-employee-name="<?php echo htmlspecialchars($row['username']); ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteEmployeeModal">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No employees found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination controls -->
                <nav>
                    <ul class="pagination justify-content-center" id="employeeTablePagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<?php include __DIR__ . '/modals/modal_employee_add.php'; ?>

<!-- Edit Employee Modal -->
<?php include __DIR__ . '/modals/modal_employee_edit.php'; ?>

<!-- Delete Employee Modal -->
<?php include __DIR__ . '/modals/modal_employee_delete.php'; ?>

<!-- Details Employee Modal -->
<?php include __DIR__ . '/modals/modal_employee_details.php'; ?>

<!-- Delete Employee Modal -->
<script>
$(document).ready(function() {
    // Employee search functionality
    $('#searchEmployee').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#employeeTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Load employee details when clicking "View details"
    $('.view-details').on('click', function() {
        const employeeId = $(this).data('employee-id');
        
        // Show loading spinner
        $('#employeeDetailsContent').html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading employee details...</p>
            </div>
        `);
        
        // Load the employee details from the backend controller
        $.ajax({
            url: '../../controller/admin/employee_details.php', // <-- use backend, not modal
            type: 'GET',
            data: { id: employeeId },
            success: function(response) {
                $('#employeeDetailsContent').html(response);
            },
            error: function() {
                $('#employeeDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading employee details. Please try again.
                    </div>
                `);
            }
        });
    });

    // Edit employee
    $('.edit-employee').on('click', function() {
        const employeeId = $(this).data('employee-id');
        // Get employee data via AJAX
        $.ajax({
            url: 'get_employee.php',
            type: 'GET',
            data: { id: employeeId },
            dataType: 'json',
            success: function(employee) {
                $('#edit_employee_id').val(employee.id);
                $('#edit_username').val(employee.username);
                $('#edit_email').val(employee.email);
                $('#edit_code').val(employee.code);
            },
            error: function(xhr) {
                alert('Error fetching employee data');
            }
        });
    });

    // Edit Employee AJAX form submission
    $('#editEmployeeForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $result = $('#editEmployeeResult');
        $result.html('');
        $.ajax({
            url: '../../controller/admin/employee_edit.php',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $result.html('<div class="alert alert-success">' + response.message + '</div>');
                    setTimeout(function() {
                        $('#editEmployeeModal').modal('hide');
                        location.reload();
                    }, 1200);
                } else {
                    var debug = response.debug ? response.debug : null;
                    if (debug) {
                        console.log('Edit Employee Error:', debug);
                    }
                    $result.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                // Print the actual error to the browser console
                console.log('Edit Employee AJAX Error:', xhr.status, xhr.statusText, xhr.responseText);
                var msg = 'Error updating employee. Please try again.';
                if (xhr.responseText) {
                    msg += '<br><pre class="mt-2 small bg-light text-dark p-2 border rounded">' + xhr.responseText + '</pre>';
                }
                $result.html('<div class="alert alert-danger">' + msg + '</div>');
            }
        });
    });

    // Delete employee
    $('.delete-employee').on('click', function() {
        const employeeId = $(this).data('employee-id');
        const employeeName = $(this).data('employee-name');
        $('#delete_employee_id').val(employeeId);
        $('#delete_employee_name').text(employeeName);
        $('#deleteEmployeeResult').html('');
    });

    // Delete Employee AJAX form submission
    $('#deleteEmployeeForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $result = $('#deleteEmployeeResult');
        $result.html('');
        $.ajax({
            url: '../../controller/admin/employee_delete.php',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $result.html('<div class="alert alert-success">' + response.message + '</div>');
                    setTimeout(function() {
                        $('#deleteEmployeeModal').modal('hide');
                        location.reload();
                    }, 1200);
                } else {
                    var debug = response.debug ? response.debug : null;
                    if (debug) {
                        console.log('Delete Employee Error:', debug);
                    }
                    $result.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                console.log('Delete Employee AJAX Error:', xhr.status, xhr.statusText, xhr.responseText);
                var msg = 'Error deleting employee. Please try again.';
                if (xhr.responseText) {
                    msg += '<br><pre class="mt-2 small bg-light text-dark p-2 border rounded">' + xhr.responseText + '</pre>';
                }
                $result.html('<div class="alert alert-danger">' + msg + '</div>');
            }
        });
    });

    // Add Employee AJAX form submission
    $('#addEmployeeForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $result = $('#addEmployeeResult');
        $result.html('');
        $.ajax({
            url: '../../controller/admin/employee_add.php',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $result.html('<div class="alert alert-success">' + response.message + '<br>QR Code: <span class="badge bg-dark">' + response.code + '</span></div>');
                    $form[0].reset();
                    setTimeout(function() {
                        $('#addEmployeeModal').modal('hide');
                        location.reload();
                    }, 1200);
                } else {
                    $result.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                $result.html('<div class="alert alert-danger">Error adding employee. Please try again.</div>');
            }
        });
    });

    // Pagination logic for employeeTable
    function paginateTable(tableSelector, paginationSelector, rowsPerPage) {
        var $table = $(tableSelector);
        var $rows = $table.find('tbody tr:visible');
        var totalRows = $rows.length;
        var totalPages = Math.ceil(totalRows / rowsPerPage);

        function showPage(page) {
            $rows.hide();
            $rows.slice((page - 1) * rowsPerPage, page * rowsPerPage).show();

            // Build pagination
            var $pagination = $(paginationSelector);
            $pagination.empty();

            if (totalPages <= 1) return;

            var prevDisabled = (page === 1) ? 'disabled' : '';
            var nextDisabled = (page === totalPages) ? 'disabled' : '';

            $pagination.append('<li class="page-item ' + prevDisabled + '"><a class="page-link" href="#" data-page="' + (page - 1) + '">Prev</a></li>');
            for (var i = 1; i <= totalPages; i++) {
                var active = (i === page) ? 'active' : '';
                $pagination.append('<li class="page-item ' + active + '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>');
            }
            $pagination.append('<li class="page-item ' + nextDisabled + '"><a class="page-link" href="#" data-page="' + (page + 1) + '">Next</a></li>');
        }

        // Initial page
        showPage(1);

        // Pagination click
        $(paginationSelector).off('click').on('click', 'a.page-link', function(e) {
            e.preventDefault();
            var page = parseInt($(this).data('page'));
            if (!isNaN(page) && page >= 1 && page <= totalPages) {
                showPage(page);
            }
        });
    }

    // Call pagination for employeeTable, 8 rows per page
    paginateTable('#employeeTable', '#employeeTablePagination', 7);

    // jQuery searchbar for employee table
    $('#searchEmployee').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#employeeTable tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(value) > -1);
        });
        paginateTable('#employeeTable', '#employeeTablePagination', 7);
    });

    // If you have search/filter, re-run pagination after filtering
    $('#searchEmployee').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#employeeTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
        paginateTable('#employeeTable', '#employeeTablePagination', 7);
    });
});
</script>

<?php require_once '../../includes/admin/footer.php'; ?>
</body>
</html>