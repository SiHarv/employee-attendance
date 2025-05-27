document.addEventListener('DOMContentLoaded', function() {
    const qrCodeScanner = document.getElementById('qrCodeScanner');
    const attendanceStatus = document.getElementById('attendanceStatus');
    const employeeIdInput = document.getElementById('employeeId');
    const submitButton = document.getElementById('submitButton');

    // Function to handle QR code scanning
    function scanQRCode() {
        // Simulate QR code scanning
        const scannedData = qrCodeScanner.value; // Assume this gets the scanned QR code data
        if (scannedData) {
            const employeeId = scannedData; // Assuming the QR code contains the employee ID
            employeeIdInput.value = employeeId;
            checkAttendanceStatus(employeeId);
        } else {
            alert('Please scan a valid QR code.');
        }
    }

    // Function to check attendance status
    function checkAttendanceStatus(employeeId) {
        fetch(`../controller/user/attendance.php?employee_id=${employeeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    attendanceStatus.innerText = `Status: ${data.status}`;
                } else {
                    attendanceStatus.innerText = 'Status: Absent';
                }
            })
            .catch(error => {
                console.error('Error fetching attendance status:', error);
            });
    }

    // Event listener for the submit button
    submitButton.addEventListener('click', function() {
        scanQRCode();
    });
});