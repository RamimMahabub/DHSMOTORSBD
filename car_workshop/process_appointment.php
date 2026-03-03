<?php
require_once 'db.php';

session_start();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name = $conn->real_escape_string($_POST['client_name']);
    $client_address = $conn->real_escape_string($_POST['client_address']);
    $client_phone = $conn->real_escape_string($_POST['client_phone']);
    $car_license_no = $conn->real_escape_string($_POST['car_license_no']);
    $car_engine_no = $conn->real_escape_string($_POST['car_engine_no']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $mechanic_id = (int) $_POST['mechanic_id'];

    if (empty($client_name) || empty($client_phone) || empty($car_license_no) || empty($car_engine_no) || empty($appointment_date) || empty($mechanic_id)) {
        $message = "Please fill all required fields.";
        $messageType = "error";
    } elseif (!ctype_digit($client_phone) || !ctype_digit($car_engine_no)) {
        $message = "Phone number and Engine number must contain numbers only.";
        $messageType = "error";
    } else {

        $check_client_sql = "SELECT id FROM appointments WHERE (client_phone = '$client_phone' OR car_license_no = '$car_license_no') AND appointment_date = '$appointment_date'";
        $client_result = $conn->query($check_client_sql);

        if ($client_result->num_rows > 0) {
            $message = "You have already booked an appointment on this specific date.";
            $messageType = "error";
        } else {

            $MAX_APPOINTMENTS = 4;
            $check_mechanic_sql = "SELECT count(id) as total FROM appointments WHERE mechanic_id = $mechanic_id AND appointment_date = '$appointment_date'";
            $mechanic_result = $conn->query($check_mechanic_sql);
            $row = $mechanic_result->fetch_assoc();

            if ($row['total'] >= $MAX_APPOINTMENTS) {

                $message = "Sorry, the selected mechanic is fully booked on this date. Please choose another mechanic or date.";
                $messageType = "error";
            } else {

                $insert_sql = "INSERT INTO appointments (client_name, client_address, client_phone, car_license_no, car_engine_no, appointment_date, mechanic_id) 
                               VALUES ('$client_name', '$client_address', '$client_phone', '$car_license_no', '$car_engine_no', '$appointment_date', $mechanic_id)";

                if ($conn->query($insert_sql) === TRUE) {
                    $message = "Appointment successfully booked for $appointment_date!";
                    $messageType = "success";
                } else {
                    $message = "Error booking appointment: " . $conn->error;
                    $messageType = "error";
                }
            }
        }
    }


    $_SESSION['message'] = $message;
    $_SESSION['messageType'] = $messageType;
    header("Location: index.php");
    exit();
}
?>