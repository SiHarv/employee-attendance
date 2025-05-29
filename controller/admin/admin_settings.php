<?php
require_once '../../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $set_am_time_in = $_POST['set_am_time_in'];
    $set_am_time_out = $_POST['set_am_time_out'];
    $set_pm_time_in = $_POST['set_pm_time_in'];
    $set_pm_time_out = $_POST['set_pm_time_out'];

    $query = "UPDATE settings SET set_am_time_in = ?, set_am_time_out = ?, set_pm_time_in = ?, set_pm_time_out = ? WHERE id = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $set_am_time_in, $set_am_time_out, $set_pm_time_in, $set_pm_time_out);

    if ($stmt->execute()) {
        echo "Settings updated successfully.";
    } else {
        echo "Error updating settings: " . $stmt->error;
    }
    exit;
}

$query = "SELECT set_am_time_in, set_am_time_out, set_pm_time_in, set_pm_time_out FROM settings WHERE id = 1";
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
            <label for="set_am_time_in">AM Time In:</label>
            <input type="time" name="set_am_time_in" value="<?php echo $settings['set_am_time_in']; ?>" required>
            <br>
            <label for="set_am_time_out">AM Time Out:</label>
            <input type="time" name="set_am_time_out" value="<?php echo $settings['set_am_time_out']; ?>" required>
            <br>
            <label for="set_pm_time_in">PM Time In:</label>
            <input type="time" name="set_pm_time_in" value="<?php echo isset($settings['set_pm_time_in']) ? $settings['set_pm_time_in'] : ''; ?>" required>
            <br>
            <label for="set_pm_time_out">PM Time Out:</label>
            <input type="time" name="set_pm_time_out" value="<?php echo isset($settings['set_pm_time_out']) ? $settings['set_pm_time_out'] : ''; ?>" required>
            <br>
            <button type="submit">Update Settings</button>
        </form>
    </div>
</body>
</html>