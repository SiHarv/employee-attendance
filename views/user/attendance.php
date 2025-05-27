<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link rel="stylesheet" href="../../assets/css/user.css">
    <script src="../../assets/js/qrcode.js"></script>
    <script src="../../assets/js/attendance.js"></script>
</head>
<body>
    <?php require_once '../../includes/user/header.php'; ?>
    <?php require_once '../../includes/user/sidebar.php'; ?>

    <div class="content">
        <h1>Attendance</h1>
        <div id="qr-code-container">
            <h2>Scan QR Code</h2>
            <div id="qrcode"></div>
            <button id="scan-qr-btn">Scan QR Code</button>
        </div>
        <div id="attendance-status">
            <h2>Your Attendance Status</h2>
            <p id="status">Status: Absent</p>
            <p id="time-in">Time In: Not Recorded</p>
            <p id="time-out">Time Out: Not Recorded</p>
        </div>
    </div>

    <script>
        document.getElementById('scan-qr-btn').addEventListener('click', function() {
            // Implement QR code scanning functionality
            scanQRCode();
        });

        function scanQRCode() {
            // Logic to scan QR code and update attendance status
            // This function should call the backend to update the attendance status
        }

        // Generate QR code for the user
        const qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "User ID or Attendance Link",
            width: 128,
            height: 128,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    </script>
</body>
</html>