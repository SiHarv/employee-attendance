<?php
// Prevent direct access
if(!defined('ADMIN_ACCESS')) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Employee Time Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Reset any padding that affect layout */
        html, body {
            padding: 0;
            margin: 0;
            height: 100%;
            overflow-x: hidden;
            background-color: #f8f9fa;
        }
        
        /* Improved header styling with better positioning */
        .admin-header {
            background-color: #2c3e50;
            background-image: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            height: 80px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        /* Header brand styling */
        .admin-header .brand {
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 1.4rem;
        }
        
        /* User dropdown styling */
        .admin-header .dropdown-toggle {
            background-color: rgba(255,255,255,0.1);
            border: none;
            border-radius: 4px;
            transition: all 0.2s ease;
            padding: 8px 15px;
        }
        
        .admin-header .dropdown-toggle:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .admin-header .dropdown-menu {
            border-radius: 4px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: none;
            margin-top: 8px;
        }
        
        /* Critical fix: Main content area styling properly positioned */
        .content-wrapper {
            margin-top: 80px; /* Match header height exactly */
            margin-left: 250px;
            padding: 20px;
            min-height: calc(100vh - 80px);
            transition: margin-left 0.3s;
            position: relative; /* Ensures proper stacking context */
            background-color: #f8f9fa;
            z-index: 5; /* Lower than header/sidebar but still stacks properly */
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
            }
        }
        
        /* Sidebar positioning corrected */
        .sidebar {
            position: fixed;
            top: 80px; /* Match header height exactly */
            left: 0;
            height: calc(100% - 80px);
            width: 250px;
            z-index: 1040;
            overflow-y: auto;
            transition: all 0.3s;
            background-color: #343a40;
            color: white;
            box-shadow: 1px 0 10px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
        }
        
        /* Sidebar toggler styling */
        .sidebar-toggler {
            cursor: pointer;
            display: none;
            transition: transform 0.3s;
        }
        
        .sidebar-toggler:hover {
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            .sidebar-toggler {
                display: inline-block;
            }
        }
        
        /* Card styling */
        .card {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .container-fluid {
            padding-top: 0; /* No more additional padding needed */
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center h-100">
                <div class="d-flex align-items-center">
                    <span class="sidebar-toggler me-3" id="sidebarToggle">
                        <i class="bi bi-list text-white" style="font-size: 2rem;"></i>
                    </span>
                    <h1 class="mb-0 h3 brand">
                        <i class="bi bi-clock-history me-2"></i>
                        Employee Time Tracking
                    </h1>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3">
                        <a class="btn btn-dark dropdown-toggle d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 8px 16px;">
                            <i class="bi bi-person-circle me-2" style="font-size: 1.2rem;"></i>
                            <span><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="reports.php"><i class="bi bi-graph-up me-2"></i>Reports</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Add event listener for sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            // Force scroll to top on page load to ensure content visibility
            window.scrollTo(0, 0);
            
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    const sidebar = document.querySelector('.sidebar');
                    const content = document.querySelector('.content-wrapper');
                    
                    if (sidebar) {
                        if (sidebar.style.left === '0px' || getComputedStyle(sidebar).left === '0px') {
                            sidebar.style.left = '-250px';
                            if (content) content.style.marginLeft = '0';
                            this.classList.remove('active');
                        } else {
                            sidebar.style.left = '0px';
                            if (content && window.innerWidth > 768) content.style.marginLeft = '250px';
                            this.classList.add('active');
                        }
                    }
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
