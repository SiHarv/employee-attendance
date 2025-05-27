// employee-attendance/js/admin/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

function loadDashboardData() {
    fetch('controller/admin/dashboard.php')
        .then(response => response.json())
        .then(data => {
            displayDashboardData(data);
        })
        .catch(error => console.error('Error loading dashboard data:', error));
}

function displayDashboardData(data) {
    const attendanceStats = document.getElementById('attendance-stats');
    attendanceStats.innerHTML = `
        <h3>Attendance Statistics</h3>
        <p>Total Employees: ${data.totalEmployees}</p>
        <p>Present Today: ${data.presentToday}</p>
        <p>Late Today: ${data.lateToday}</p>
        <p>Absent Today: ${data.absentToday}</p>
    `;
}

function updateAttendanceStatus(employeeId, status) {
    fetch('controller/admin/attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ employeeId, status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Attendance status updated successfully!');
            loadDashboardData(); // Refresh the dashboard data
        } else {
            alert('Failed to update attendance status.');
        }
    })
    .catch(error => console.error('Error updating attendance status:', error));
}