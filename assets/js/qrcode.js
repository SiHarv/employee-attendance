// This file handles QR code generation and scanning functionalities.

function generateQRCode(data) {
    const qrCodeContainer = document.getElementById('qrcode');
    qrCodeContainer.innerHTML = ""; // Clear previous QR code
    const qrcode = new QRCode(qrCodeContainer, {
        text: data,
        width: 128,
        height: 128,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H,
    });
}

function scanQRCode() {
    const video = document.getElementById('video');
    const scanner = new Instascan.Scanner({ video: video });

    scanner.addListener('scan', function(content) {
        // Handle the scanned QR code content
        console.log('Scanned content: ', content);
        // You can send the scanned content to the server for processing
        updateAttendance(content);
    });

    Instascan.Camera.getCameras().then(function(cameras) {
        if (cameras.length > 0) {
            scanner.start(cameras[0]);
        } else {
            console.error('No cameras found.');
        }
    }).catch(function(e) {
        console.error(e);
    });
}

function updateAttendance(qrCodeData) {
    // Send the QR code data to the server to update attendance
    fetch('controller/user/attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ qrCodeData: qrCodeData }),
    })
    .then(response => response.json())
    .then(data => {
        console.log('Attendance updated:', data);
        // Handle success or error messages
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}

// Initialize QR code generation and scanning on page load
document.addEventListener('DOMContentLoaded', function() {
    const qrData = "Employee ID or Attendance Info"; // Replace with actual data
    generateQRCode(qrData);
    scanQRCode();
});