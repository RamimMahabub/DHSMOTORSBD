<?php
require_once 'db.php';

session_start();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name = $conn->real_escape_string($_POST['client_name']);
    $client_address = $conn->real_escape_string($_POST['client_address']);
    $client_phone = $conn->real_escape_string($_POST['client_phone']);

    $car_license_nos = $_POST['car_license_no']; 
    $car_engine_nos = $_POST['car_engine_no']; 
    $appointment_dates = $_POST['appointment_date']; 
    $mechanic_ids = $_POST['mechanic_id']; 

    if (empty($client_name) || empty($client_phone)) {
        $message = "Please fill all required client fields.";
        $messageType = "error";
    } elseif (!ctype_digit($client_phone)) {
        $message = "Phone number must contain numbers only.";
        $messageType = "error";
    } else {
        $DEFAULT_MAX_APPOINTMENTS = 4;

        function get_max_spaces_for_day($conn, $mech_id, $app_date, $default_max_spaces)
        {
            $sql = "SELECT max_spaces FROM mechanic_space_allocations WHERE mechanic_id = $mech_id AND allocation_date = '$app_date' LIMIT 1";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return max(0, (int) $row['max_spaces']);
            }

            return $default_max_spaces;
        }

        function book_vehicle($conn, $client_name, $client_address, $client_phone, $license_no, $engine_no, $app_date, $mech_id, $default_max_spaces)
        {
            if (empty($license_no) || empty($engine_no) || empty($app_date) || empty($mech_id) || !ctype_digit($engine_no)) {
                return ["success" => false, "msg" => "Invalid car data for license no $license_no."];
            }


            $check_client_sql = "SELECT id FROM appointments WHERE car_license_no = '$license_no' AND appointment_date = '$app_date'";
            $client_result = $conn->query($check_client_sql);
            if ($client_result->num_rows > 0) {
                return ["success" => false, "msg" => "You have already booked an appointment for $license_no on $app_date."];
            }

            $check_mechanic_sql = "SELECT count(id) as total FROM appointments WHERE mechanic_id = $mech_id AND appointment_date = '$app_date'";
            $mechanic_result = $conn->query($check_mechanic_sql);
            $row = $mechanic_result->fetch_assoc();
            $max_spaces_for_day = get_max_spaces_for_day($conn, $mech_id, $app_date, $default_max_spaces);

            if ($row['total'] >= $max_spaces_for_day) {
                return ["success" => false, "msg" => "Sorry, the selected mechanic is fully booked on $app_date. Please choose another mechanic or date for $license_no."];
            }

            $insert_sql = "INSERT INTO appointments (client_name, client_address, client_phone, car_license_no, car_engine_no, appointment_date, mechanic_id) 
                           VALUES ('$client_name', '$client_address', '$client_phone', '$license_no', '$engine_no', '$app_date', $mech_id)";

            if ($conn->query($insert_sql) === TRUE) {
                return ["success" => true, "msg" => "Appointment successfully booked!"];
            } else {
                return ["success" => false, "msg" => "Error booking appointment for $license_no: " . $conn->error];
            }
        }

        $conn->begin_transaction();

        $all_success = true;
        $error_message = "";

        for ($i = 0; $i < count($car_license_nos); $i++) {
            $license = $conn->real_escape_string($car_license_nos[$i]);
            $engine = $conn->real_escape_string($car_engine_nos[$i]);
            $date = $conn->real_escape_string($appointment_dates[$i]);
            $mech = (int) $mechanic_ids[$i];

            $res = book_vehicle($conn, $client_name, $client_address, $client_phone, $license, $engine, $date, $mech, $DEFAULT_MAX_APPOINTMENTS);

            if (!$res['success']) {
                $all_success = false;
                $error_message = $res['msg'];
                break;
            }
        }

        if ($all_success) {
            $conn->commit();
            $message = "Appointment(s) successfully booked!";
            $messageType = "success";
        } else {
            $conn->rollback();
            $message = $error_message;
            $messageType = "error";
        }
    }

    $_SESSION['message'] = $message;
    $_SESSION['messageType'] = $messageType;
    header("Location: index.php");
    exit();
}
?>
