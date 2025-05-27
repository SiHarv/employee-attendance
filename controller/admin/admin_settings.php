<?php
require_once '../../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $time_in = $_POST['time_in'];
    $threshold_minute = $_POST['threshold_minute'];
    $time_out = $_POST['time_out'];

    $query = "UPDATE settings SET time_in = ?, threshold_minute = ?, time_out = ? WHERE id = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $time_in, $threshold_minute, $time_out);

    if ($stmt->execute()) {
        $message = "Settings updated successfully.";
    } else {
        $message = "Error updating settings: " . $stmt->error;
    }
}

$query = "SELECT time_in, threshold_minute, time_out FROM settings WHERE id = 1";
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
        <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
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
            <button type="submit">Update Settings</button>
        </form>
    </div>
</body>
</html>