<style>
    /* Fix sidebar positioning - must match header settings */
    .sidebar {
        position: fixed;
        top: 80px; /* Match header height exactly */
        left: 0;
        width: 250px;
        height: calc(100% - 80px); /* Adjust for header height */
        overflow-y: auto;
        background-color: #343a40;
        color: white;
        z-index: 1040;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(31, 45, 65, 0.15);
        padding-top: 1rem;
        font-size: 0.95rem !important;
    }
    
    .sidebar .nav-link {
        color: rgba(255,255,255,.75);
        padding: 1rem;
        border-left: 3px solid transparent;
        display: flex;
        align-items: center;
        transition: all 0.2s;
        font-size: 0.95rem !important;
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

    .sidebar .nav-link i {
        margin-right: 8px;
        font-size: 1.1rem;
    }
    
    /* Fix content overlay issues */
    .sidebar + .main-content {
        margin-left: 250px;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            left: -250px; /* Hide by default on mobile */
        }
        
        .sidebar + .main-content {
            margin-left: 0;
        }
    }

    .sidebar-header {
        padding: 1.5rem 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar-header h4 {
        color: white;
        margin-bottom: 0;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.7;
    }

    .sidebar .nav-divider {
        height: 1px;
        background-color: rgba(255,255,255,0.1);
        margin: 1rem 0;
    }
    
    /* Critical fix for consistent spacing */
    .sidebar .nav-item {
        margin-bottom: 0.25rem; /* Consistent spacing between nav items */
    }
    
    /* Ensure consistent spcaing in sidebar with main-content */
    .sidebar, .main-content {
        font-size: 0.95rem !important;
    }
    
    /* Force a fixed height to prevent layout shifting */
    .sidebar .nav {
        min-height: calc(100vh - 150px);
    }
</style>

<div class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <h4><i class="bi bi-speedometer2 me-2"></i> Admin Panel</h4>
    </div>
    <div class="p-3">
        <ul class="nav flex-column">
            <?php
            // Determine current page for highlighting active link
            $current_page = basename($_SERVER['PHP_SELF']);
            ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="../../views/admin/dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'employees.php') ? 'active' : ''; ?>" href="../../views/admin/employees.php">
                    <i class="bi bi-people"></i> Employees
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>" href="../../views/admin/attendance.php">
                    <i class="bi bi-clipboard-check"></i> Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'scan.php') ? 'active' : ''; ?>" href="../../views/admin/scan.php">
                    <i class="bi bi-qr-code-scan"></i> QR Scanner
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="../../views/admin/settings.php">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
        </ul>
        
        <div class="nav-divider"></div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="../../index.php" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> View Frontend
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../controller/admin/logout.php">
                    <i class="bi bi-power"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
    // This script ensures sidebar toggles properly and maintains consistent spacing
    document.addEventListener('DOMContentLoaded', function() {
        function adjustSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (!sidebar || !mainContent) return;
            
            if (window.innerWidth <= 768) {
                sidebar.style.left = '-250px';
                mainContent.style.marginLeft = '0';
            } else {
                sidebar.style.left = '0';
                mainContent.style.marginLeft = '250px';
            }
            
            // Critical fix: Apply consistent sizing throughout the app
            const adminPages = ['dashboard.php', 'employees.php', 'attendance.php', 'scan.php', 'settings.php'];
            const currentPage = window.location.pathname.split('/').pop();
            
            if (adminPages.includes(currentPage)) {
                document.body.style.fontSize = '0.95rem';
                
                // Ensure all elements inside main-content have consistent font size
                const mainContentElements = mainContent.querySelectorAll('*:not(h1):not(h2):not(h3):not(h4):not(h5):not(h6)');
                mainContentElements.forEach(el => {
                    el.style.fontSize = '0.95rem';
                });
            }
        }
        
        // Run on page load
        adjustSidebar();
        
        // Run on window resize
        window.addEventListener('resize', adjustSidebar);
    });
</script>