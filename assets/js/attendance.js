// attendance.js
document.addEventListener('DOMContentLoaded', function() {
    const qrCodeScanner = document.getElementById('qrCodeScanner');
    const attendanceForm = document.getElementById('attendanceForm');
    const statusMessage = document.getElementById('statusMessage');

    // Function to handle QR code scanning
    function scanQRCode() {
        // Assuming qrcode.js handles the QR code scanning
        qrCodeScanner.addEventListener('scan', function(event) {
            const employeeId = event.detail; // Get employee ID from scanned QR code
            markAttendance(employeeId);
        });
    }

    // Function to mark attendance
    function markAttendance(employeeId) {
        const currentTime = new Date();
        const timeIn = currentTime.toTimeString().split(' ')[0]; // Get current time in HH:MM:SS format

        // Fetch threshold time from settings (this should be done via an AJAX call)
        const thresholdTime = '09:00:00'; // Example threshold time

        let status = 'absent'; // Default status
        if (timeIn <= thresholdTime) {
            status = 'present';
        } else {
            status = 'late';
        }

        // Update attendance in the database (this should be done via an AJAX call)
        fetch('controller/user/attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                employeeId: employeeId,
                timeIn: timeIn,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusMessage.textContent = `Attendance marked as ${status} for employee ID: ${employeeId}`;
            } else {
                statusMessage.textContent = 'Error marking attendance. Please try again.';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusMessage.textContent = 'Error marking attendance. Please try again.';
        });
    }

    // Initialize QR code scanning
    scanQRCode();
});