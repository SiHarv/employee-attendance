document.addEventListener('DOMContentLoaded', function() {
    const attendanceForm = document.getElementById('attendanceForm');
    const qrCodeInput = document.getElementById('qrCodeInput');
    const statusMessage = document.getElementById('statusMessage');

    attendanceForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const qrCode = qrCodeInput.value;

        if (qrCode) {
            fetch('controller/admin/attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ qrCode: qrCode })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusMessage.textContent = 'Attendance recorded successfully: ' + data.status;
                    statusMessage.style.color = 'green';
                } else {
                    statusMessage.textContent = 'Error: ' + data.message;
                    statusMessage.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusMessage.textContent = 'An error occurred while recording attendance.';
                statusMessage.style.color = 'red';
            });
        } else {
            statusMessage.textContent = 'Please scan a QR code.';
            statusMessage.style.color = 'red';
        }
    });
});