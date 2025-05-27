<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$data = include_once '../../controller/user/user_dashboard.php';
$user = $data['user'];
$attendance = $data['attendance'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance Record</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <style>
        .main-content-user {
            margin-top: 0 !important;
            padding-top: 0.5rem;
        }
        @media (max-width: 768px) {
            .main-content-user {
                padding-top: 0.3rem;
            }
        }
        /* Remove default margin from body to avoid extra gap */
        body {
            margin: 0 !important;
        }
    </style>
</head>
<body class="bg-light">
<?php
require_once '../../includes/user/header.php';
?>
<div class="container-fluid" style="min-height:100vh;">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once '../../includes/user/sidebar.php'; ?>
        </div>
        <div class="col-md-10 main-content-user">
            <div class="card shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3 text-center">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                    <p class="text-center text-muted mb-4">Email: <?php echo htmlspecialchars($user['email']); ?></p>
                    <h4 class="mb-3">Your Attendance Records</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Total Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($attendance)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No attendance records found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($attendance as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['time_in']); ?></td>
                                            <td><?php echo htmlspecialchars($row['time_out'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['total_hours']); ?></td>
                                            <td>
                                                <?php
                                                    $badge = 'secondary';
                                                    if ($row['status'] === 'present') $badge = 'success';
                                                    elseif ($row['status'] === 'late') $badge = 'warning';
                                                    elseif ($row['status'] === 'absent') $badge = 'danger';
                                                ?>
                                                <span class="badge bg-<?php echo $badge; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="../../controller/user/user_logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../assets/js/lib/bootstrap.bundle.min.js"></script>
</body>
</html>
