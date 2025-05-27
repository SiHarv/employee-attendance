<aside>
    <style>
        .user-sidebar {
            width: 100%;
            max-width: 220px;
            background: #343a40;
            border-right: 1px solid #23272b;
            min-height: 100vh;
            padding-top: 1rem;
            color: #fff;
            font-size: 0.97rem;
            box-shadow: 1px 0 10px rgba(0,0,0,0.08);
        }
        .user-sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            font-weight: 500;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            padding: 0.75rem 1.2rem;
            display: flex;
            align-items: center;
            transition: background 0.15s, color 0.15s;
            font-size: 1rem;
        }
        .user-sidebar .nav-link.active,
        .user-sidebar .nav-link:hover {
            background: #007bff;
            color: #fff;
        }
        .user-sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.15rem;
        }
        @media (max-width: 900px) {
            .user-sidebar {
                max-width: 100vw;
                width: 100vw;
                border-right: none;
                border-bottom: 1px solid #23272b;
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
                min-height: unset;
            }
            .user-sidebar .nav {
                flex-direction: row !important;
                justify-content: space-around;
            }
            .user-sidebar .nav-link {
                margin-bottom: 0;
                padding: 0.7rem 0.7rem;
                font-size: 0.97rem;
            }
        }
    </style>
    <nav class="user-sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? ' active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? ' active' : ''; ?>" href="profile.php">
                    <i class="bi bi-person"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? ' active' : ''; ?>" href="settings.php">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
        </ul>
    </nav>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</aside>