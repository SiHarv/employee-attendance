// user.js
document.addEventListener('DOMContentLoaded', function() {
    const qrCodeScannerButton = document.getElementById('scan-qr');
    const attendanceForm = document.getElementById('attendance-form');

    qrCodeScannerButton.addEventListener('click', function() {
        // Logic to scan QR code and record attendance
        scanQRCode();
    });

    attendanceForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const employeeId = document.getElementById('employee-id').value;
        const status = document.querySelector('input[name="status"]:checked').value;
        const timeIn = new Date().toISOString();

        // Logic to submit attendance data
        submitAttendance(employeeId, status, timeIn);
    });

    function scanQRCode() {
        // Placeholder for QR code scanning logic
        alert('QR Code scanning initiated...');
        // Implement QR code scanning functionality here
    }

    function submitAttendance(employeeId, status, timeIn) {
        // Placeholder for AJAX request to submit attendance
        fetch('controller/user/attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ employeeId, status, timeIn })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Attendance recorded successfully!');
            } else {
                alert('Error recording attendance: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
});