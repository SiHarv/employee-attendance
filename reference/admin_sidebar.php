<?php
// Prevent direct access
if(!defined('ADMIN_ACCESS')) {
    header("Location: ../index.php");
    exit();
}

// Determine current page for highlighting active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    /* Fix sidebar positioning - must match header settings */
    .sidebar {
        position: fixed;
        top: 80px; /* Match header height from admin_header.php */
        left: 0;
        width: 250px;
        height: calc(100% - 80px); /* Adjust for header height */
        overflow-y: auto;
        background-color: #343a40;
        color: white;
        z-index: 1040;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(31, 45, 65, 0.15);
    }
    
    .sidebar .nav-link {
        color: rgba(255,255,255,.75);
        padding: 1rem;
        border-left: 3px solid transparent;
    }
    
    .sidebar .nav-link:hover {
        color: white;
        background-color: rgba(255,255,255,.05);
        border-left: 3px solid rgba(255,255,255,.25);
    }
    
    .sidebar .nav-link.active {
        color: white;
        background-color: #007bff;
        border-left: 3px solid white;
    }
    
    /* Fix content overlay issues */
    .sidebar + .content-wrapper {
        margin-left: 250px;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            left: -250px; /* Hide by default on mobile */
        }
        
        .sidebar + .content-wrapper {
            margin-left: 0;
        }
    }
</style>

<div class="sidebar" id="adminSidebar">
    <div class="p-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'employees.php') ? 'active' : ''; ?>" href="employees.php">
                    <i class="bi bi-people me-2"></i> Employees
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                    <i class="bi bi-file-earmark-text me-2"></i> Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="settings.php">
                    <i class="bi bi-gear me-2"></i> Settings
                </a>
            </li>
        </ul>
        
        <hr class="bg-light my-3">
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="bi bi-box-arrow-left me-2"></i> Back to QR Scanner
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-power me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
    // This script ensures sidebar toggles properly
    document.addEventListener('DOMContentLoaded', function() {
        function adjustSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const contentWrapper = document.querySelector('.content-wrapper');
            
            if (!sidebar || !contentWrapper) return;
            
            if (window.innerWidth <= 768) {
                sidebar.style.left = '-250px';
                contentWrapper.style.marginLeft = '0';
            } else {
                sidebar.style.left = '0';
                contentWrapper.style.marginLeft = '250px';
            }
        }
        
        // Run on page load
        adjustSidebar();
        
        // Run on window resize
        window.addEventListener('resize', adjustSidebar);
    });
</script>
