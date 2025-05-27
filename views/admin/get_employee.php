<?php
require_once '../../db/connect.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No employee ID provided']);
    exit;
}

$id = intval($_GET['id']);
$query = "SELECT id, username, email, code FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    echo json_encode($employee);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Employee not found']);
}
exit;
