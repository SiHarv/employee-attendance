<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="icon" href="../../assets/images/favicon.svg" type="image/svg+xml">
    <style>
        .user-header {
            background: #2c3e50;
            background-image: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            color: #fff;
            padding: 15px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1050;
            min-height: 70px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.12);
        }
        .user-header .logo img {
            height: 38px;
        }
        .user-header .navbar-brand {
            font-weight: 600;
            font-size: 1.2rem;
            color: #fff;
            margin-left: 0.5rem;
            letter-spacing: 0.5px;
        }
        .user-header .user-label {
            font-weight: 500;
            font-size: 1rem;
            background: rgba(255,255,255,0.08);
            padding: 7px 18px;
            border-radius: 20px;
            color: #fff;
            letter-spacing: 0.5px;
        }
        @media (max-width: 600px) {
            .user-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 0.7rem;
                min-height: 56px;
            }
            .user-header .logo img {
                height: 28px;
            }
            .user-header .navbar-brand {
                font-size: 1rem;
            }
            .user-header .user-label {
                font-size: 0.95rem;
                padding: 5px 12px;
            }
        }
    </style>
</head>
<body>
    <header class="user-header">
        <div class="d-flex align-items-center logo">
            <img src="../../assets/images/logo.svg" alt="Logo">
            <span class="navbar-brand ms-2">Employee Attendance</span>
        </div>
        <div>
            <span class="user-label"><i class="bi bi-person-circle me-1"></i>User Panel</span>
        </div>
    </header>