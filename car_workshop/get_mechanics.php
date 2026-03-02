<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_GET['date']) || empty($_GET['date'])) {
    echo json_encode(['error' => 'Date is required']);
    exit;
}

$date = $conn->real_escape_string($_GET['date']);

$exclude_sql = "";
if (isset($_GET['exclude_id']) && !empty($_GET['exclude_id'])) {
    $exclude_id = (int) $_GET['exclude_id'];
    $exclude_sql = " AND a.id != $exclude_id";
}

// Maximum 4 appointments per mechanic per day
$MAX_APPOINTMENTS = 4;

// Get all mechanics and their current appointment count on the specified date
$sql = "
    SELECT 
        m.id, 
        m.name, 
        COUNT(a.id) as current_appointments
    FROM mechanics m
    LEFT JOIN appointments a ON m.id = a.mechanic_id AND a.appointment_date = '$date' $exclude_sql
    GROUP BY m.id
";

$result = $conn->query($sql);

$mechanics = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $appointments = (int) $row['current_appointments'];
        $free_places = $MAX_APPOINTMENTS - $appointments;

        $mechanics[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'free_places' => max(0, $free_places)
        ];
    }
    echo json_encode($mechanics);
} else {
    echo json_encode(['error' => 'Database query failed']);
}

$conn->close();
?>