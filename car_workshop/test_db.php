<?php
$conn = new mysqli("localhost", "root", "", "workshop_db");

$res = $conn->query("SELECT mechanic_id, appointment_date, count(*) as c FROM appointments GROUP BY mechanic_id, appointment_date");
print_r($res->fetch_all(MYSQLI_ASSOC));

$res2 = $conn->query("
    SELECT 
        m.id, 
        m.name, 
        COUNT(a.id) as current_appointments
    FROM mechanics m
    LEFT JOIN appointments a ON m.id = a.mechanic_id AND a.appointment_date = '2026-03-05'
    GROUP BY m.id
");
print_r($res2->fetch_all(MYSQLI_ASSOC));
