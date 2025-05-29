# Employee Attendance System

This project is a web-based application designed to manage employee attendance using QR codes. It allows employees to clock in and out, while providing administrators with tools to manage attendance records and settings.

## Project Structure

The project is organized into several directories and files:

```
employee-attendance
├── assets
│   ├── css
│   │   ├── style.css         # General styles for the application
│   │   ├── admin.css         # Styles specific to the admin interface
│   │   └── user.css          # Styles specific to the user interface
│   ├── js
│   │   ├── qrcode.js         # QR code generation and scanning functionalities
│   │   ├── attendance.js      # Attendance-related JavaScript functions
│   │   ├── admin.js          # JavaScript functions specific to the admin interface
│   │   └── user.js           # JavaScript functions specific to the user interface
│   └── images
│       ├── logo.svg          # Logo image for the application
│       └── favicon.svg       # Favicon for the application
├── controller
│   ├── admin
│   │   ├── attendance.php     # Attendance management for the admin
│   │   ├── dashboard.php      # Admin dashboard functionalities
│   │   ├── employees.php      # Employee-related functionalities for the admin
│   │   ├── login.php          # Admin login functionalities
│   │   └── settings.php       # Application settings for the admin
│   └── user
│       ├── attendance.php     # Attendance functionalities for the user
│       ├── dashboard.php      # User dashboard functionalities
│       ├── login.php          # User login functionalities
│       └── profile.php        # User profile functionalities
├── db
│   ├── config.php             # Database configuration settings
│   ├── connect.php            # Establishes a connection to the database
│   └── attendance_db.sql      # SQL commands to create necessary database tables
├── includes
│   ├── admin
│   │   ├── header.php         # Header HTML for the admin interface
│   │   └── sidebar.php        # Sidebar HTML for the admin interface
│   └── user
│       ├── header.php         # Header HTML for the user interface
│       └── sidebar.php        # Sidebar HTML for the user interface
├── js
│   ├── admin
│   │   ├── dashboard.js       # JavaScript functions specific to the admin dashboard
│   │   └── employees.js       # JavaScript functions for managing employees in the admin interface
│   └── user
│       ├── attendance.js      # JavaScript functions for user attendance management
│       └── profile.js         # JavaScript functions for user profile management
├── lib
│   └── qrcode-go.php          # Integration code for the QRcode Go API
├── views
│   ├── admin
│   │   ├── dashboard.php      # Renders the admin dashboard view
│   │   ├── employees.php      # Renders the employee management view for the admin
│   │   ├── attendance.php     # Renders the attendance management view for the admin
│   │   ├── settings.php       # Renders the settings view for the admin
│   │   └── scan.php          # Renders the QR code scanning view for the admin
│   └── user
│       ├── dashboard.php      # Renders the user dashboard view
│       ├── profile.php        # Renders the user profile view
│       └── attendance.php     # Renders the attendance view for the user
├── index.php                  # Entry point of the application, handling user and admin login
├── admin.php                  # Main entry point for admin functionalities
├── config.php                 # General configuration settings for the application
└── README.md                  # Documentation and instructions for the project
```

## Features

- **User Login**: Employees can log in to their accounts.
- **Admin Login**: Admins can log in to manage the system.
- **QR Code Attendance**: Employees can scan QR codes to record their attendance.
- **Attendance Management**: Admins can view and manage attendance records.
- **Settings Configuration**: Admins can configure time settings for attendance.

## Database

The application uses a MySQL database with the following tables:

- **users**: Stores employee information.
- **admin**: Stores admin information.
- **morning_time_log**: Records attendance with fields for employee ID, time in, time out, and status (present, late, absent).
- **settings**: Stores application settings such as time in, threshold minutes for late arrivals, and time out.

## Getting Started

1. Clone the repository to your local machine.
2. Set up the database using the SQL commands in `db/attendance_db.sql`.
3. Configure the database connection in `db/config.php`.
4. Open `index.php` in your web browser to access the application.

## License

This project is licensed under the MIT License.