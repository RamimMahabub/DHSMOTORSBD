<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['date']) || empty($_GET['date']) || !isset($_GET['mechanic_id']) || empty($_GET['mechanic_id'])) {
    echo json_encode(['error' => 'Date and mechanic are required']);
    exit;
}

$date = $conn->real_escape_string($_GET['date']);
$mechanic_id = (int) $_GET['mechanic_id'];
$DEFAULT_MAX_APPOINTMENTS = 4;

$max_spaces_sql = "SELECT max_spaces FROM mechanic_space_allocations WHERE mechanic_id = $mechanic_id AND allocation_date = '$date' LIMIT 1";
$max_spaces_result = $conn->query($max_spaces_sql);
$max_spaces = $DEFAULT_MAX_APPOINTMENTS;
$source = 'default';

if ($max_spaces_result && $max_spaces_result->num_rows > 0) {
    $max_row = $max_spaces_result->fetch_assoc();
    $max_spaces = max(0, (int) $max_row['max_spaces']);
    $source = 'custom';
}

$booked_sql = "SELECT COUNT(id) as booked FROM appointments WHERE mechanic_id = $mechanic_id AND appointment_date = '$date'";
$booked_result = $conn->query($booked_sql);
$booked = 0;
if ($booked_result && $booked_result->num_rows > 0) {
    $booked = (int) $booked_result->fetch_assoc()['booked'];
}

echo json_encode([
    'max_spaces' => $max_spaces,
    'booked_spaces' => $booked,
    'free_spaces' => max(0, $max_spaces - $booked),
    'default_spaces' => $DEFAULT_MAX_APPOINTMENTS,
    'source' => $source
]);

$conn->close();
?>
