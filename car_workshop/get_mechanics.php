<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_GET['date']) || empty($_GET['date'])) {
    echo json_encode(['error' => 'Date is required']);
    exit;
}

$date = $conn->real_escape_string($_GET['date']);

$DEFAULT_MAX_APPOINTMENTS = 4;


$sql = "
    SELECT 
        m.id, 
        m.name, 
        COALESCE(msa.max_spaces, $DEFAULT_MAX_APPOINTMENTS) as max_spaces,
        COUNT(a.id) as current_appointments
    FROM mechanics m
    LEFT JOIN mechanic_space_allocations msa ON m.id = msa.mechanic_id AND msa.allocation_date = '$date'
    LEFT JOIN appointments a ON m.id = a.mechanic_id AND a.appointment_date = '$date'
    GROUP BY m.id, m.name, msa.max_spaces
";

$result = $conn->query($sql);

$mechanics = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $appointments = (int) $row['current_appointments'];
        $max_spaces = (int) $row['max_spaces'];
        $free_places = $max_spaces - $appointments;

        $mechanics[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'max_spaces' => $max_spaces,
            'free_places' => max(0, $free_places)
        ];
    }
    echo json_encode($mechanics);
} else {
    echo json_encode(['error' => 'Database query failed']);
}

$conn->close();
?>
