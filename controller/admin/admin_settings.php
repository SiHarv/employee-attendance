<?php
require_once '../../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $time_in = $_POST['time_in'];
    $threshold_minute = $_POST['threshold_minute'];
    $time_out = $_POST['time_out'];
    $qr_pin = isset($_POST['qr_pin']) ? $_POST['qr_pin'] : null;
    $qr_active = isset($_POST['qr_active']) && $_POST['qr_active'] == '1' ? 1 : 0;

    if ($qr_pin !== null && $qr_pin !== '') {
        $query = "UPDATE settings SET time_in = ?, threshold_minute = ?, time_out = ?, qr_pin = ?, qr_active = ? WHERE id = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sissi", $time_in, $threshold_minute, $time_out, $qr_pin, $qr_active);
    } else {
        $query = "UPDATE settings SET time_in = ?, threshold_minute = ?, time_out = ?, qr_active = ? WHERE id = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sisi", $time_in, $threshold_minute, $time_out, $qr_active);
    }

    if ($stmt->execute()) {
        echo "Settings updated successfully.";
    } else {
        echo "Error updating settings: " . $stmt->error;
    }
    exit;
}

$query = "SELECT time_in, threshold_minute, time_out, qr_pin, qr_active FROM settings WHERE id = 1";
$result = $conn->query($query);
$settings = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php require_once '../../includes/admin/header.php'; ?>
    <?php require_once '../../includes/admin/sidebar.php'; ?>

    <div class="content">
        <h1>Settings</h1>
        <form method="POST" action="">
            <label for="time_in">Time In:</label>
            <input type="time" name="time_in" value="<?php echo $settings['time_in']; ?>" required>
            <br>
            <label for="threshold_minute">Threshold Minute for Late:</label>
            <input type="number" name="threshold_minute" value="<?php echo $settings['threshold_minute']; ?>" required>
            <br>
            <label for="time_out">Time Out:</label>
            <input type="time" name="time_out" value="<?php echo $settings['time_out']; ?>" required>
            <br>
            <label for="qr_pin">QR Pin:</label>
            <input type="text" name="qr_pin" value="<?php echo $settings['qr_pin']; ?>">
            <br>
            <label for="qr_active">QR Active:</label>
            <select name="qr_active">
                <option value="1" <?php echo $settings['qr_active'] == 1 ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo $settings['qr_active'] == 0 ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <br>
            <button type="submit">Update Settings</button>
        </form>
    </div>
</body>
</html>