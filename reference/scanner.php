<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Time Tracking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .status-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .status-present {
            color: green;
        }
        .status-late {
            color: orange;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                <h1 class="mb-4">Employee Time Tracking System</h1>
                <h3 class="mb-4">Scan your QR code to check in/out</h3>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        QR Scanner
                    </div>
                    <div class="card-body">
                        <div id="qr-reader" style="width: 100%"></div>
                        <div id="qr-reader-results" class="mt-3"></div>
                    </div>
                </div>
                
                <div id="result-message" class="mt-3 alert" style="display: none;"></div>
                
                <div class="mt-4">
                    <a href="view/admin_login.php" class="btn btn-secondary">Admin Login</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function docReady(fn) {
            // check if DOM is already available
            if (document.readyState === "complete" || document.readyState === "interactive") {
                setTimeout(fn, 1);
            } else {
                document.addEventListener("DOMContentLoaded", fn);
            }
        }

        docReady(function() {
            let resultContainer = document.getElementById('qr-reader-results');
            let lastResult, countResults = 0;
            let isProcessing = false;
            
            function onScanSuccess(decodedText, decodedResult) {
                if (decodedText !== lastResult && !isProcessing) {
                    lastResult = decodedText;
                    isProcessing = true;
                    
                    // Send to server to process check-in/out
                    $.ajax({
                        url: 'controller/time_tracking.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            qr_code: decodedText,
                            action: 'update_status'
                        },
                        success: function(data) {
                            if (data.success) {
                                let icon = 'success';
                                let statusHtml = '';
                                if (data.status === 'late') {
                                    icon = 'warning';
                                    statusHtml = '<div class="mt-2"><i class="bi bi-clock-fill status-icon status-late"></i>You are marked as <strong>LATE</strong></div>';
                                } else if (data.status === 'present') {
                                    statusHtml = '<div class="mt-2"><i class="bi bi-check-circle-fill status-icon status-present"></i>You are marked as <strong>PRESENT</strong></div>';
                                }
                                Swal.fire({
                                    icon: icon,
                                    title: 'Success!',
                                    html: data.message + statusHtml,
                                    timer: 5000,
                                    showConfirmButton: true
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message,
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }
                            setTimeout(() => {
                                isProcessing = false;
                                lastResult = null;
                            }, 3000);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("AJAX Error:", textStatus, errorThrown);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Connection error. Please try again.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                            
                            // Allow scanning again after delay
                            setTimeout(() => {
                                isProcessing = false;
                                lastResult = null;
                            }, 3000);
                        }
                    });
                }
            }

            let html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader", 
                { fps: 10, qrbox: 250 }
            );
            html5QrcodeScanner.render(onScanSuccess);
        });
    </script>
</body>
</html>