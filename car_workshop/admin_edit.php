<?php
session_start();
require_once 'db.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = (int) $_GET['id'];
$message = '';
$messageType = '';

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_date = $conn->real_escape_string($_POST['appointment_date']);
    $new_mechanic = (int) $_POST['mechanic_id'];

    if (empty($new_date) || empty($new_mechanic)) {
        $message = "Please select a date and a mechanic.";
        $messageType = "error";
    } else {

        $MAX_APPOINTMENTS = 4;
        $check_mechanic_sql = "SELECT count(id) as total FROM appointments WHERE mechanic_id = $new_mechanic AND appointment_date = '$new_date' AND id != $id";
        $mechanic_result = $conn->query($check_mechanic_sql);
        $row = $mechanic_result->fetch_assoc();

        if ($row['total'] >= $MAX_APPOINTMENTS) {
            $message = "The selected mechanic is fully booked on this new date. Please choose another one.";
            $messageType = "error";
        } else {
            // Update the record
            $update_sql = "UPDATE appointments SET appointment_date = '$new_date', mechanic_id = $new_mechanic WHERE id = $id";
            if ($conn->query($update_sql) === TRUE) {
                $_SESSION['message'] = "Appointment successfully updated!";
                $_SESSION['messageType'] = "success";
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $message = "Error updating appointment: " . $conn->error;
                $messageType = "error";
            }
        }
    }
}

// Fetch appointment details
$sql = "SELECT a.*, m.name as mechanic_name FROM appointments a JOIN mechanics m ON a.mechanic_id = m.id WHERE a.id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: admin_dashboard.php");
    exit;
}

$appointment = $result->fetch_assoc();
$current_date = $appointment['appointment_date'];
$current_mechanic_id = $appointment['mechanic_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - Workshop Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">DHS MOTORS BANGLADESH</a>
        <div class="navbar-links">
            <a href="?action=logout" class="btn-nav">Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="container" style="max-width: 600px;">
            <header class="admin-header">
                <div>
                    <h1 style="margin-bottom: 0;">Edit Appointment</h1>
                    <p>Modify date and mechanic</p>
                </div>
                <a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
            </header>

            <div class="form-container">
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div
                    style="background-color: var(--card-bg); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
                    <p><strong>Client:</strong>
                        <?php echo htmlspecialchars($appointment['client_name']); ?>
                    </p>
                    <p><strong>Phone:</strong>
                        <?php echo htmlspecialchars($appointment['client_phone']); ?>
                    </p>
                    <p><strong>Car License:</strong>
                        <?php echo htmlspecialchars($appointment['car_license_no']); ?>
                    </p>
                    <p><strong>Current Appointment:</strong>
                        <?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?> with
                        <?php echo htmlspecialchars($appointment['mechanic_name']); ?>
                    </p>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="appointment_date">Change Appointment Date</label>
                        <input type="date" id="appointment_date" name="appointment_date"
                            value="<?php echo $appointment['appointment_date']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="mechanic_id">Change Mechanic</label>
                        <select id="mechanic_id" name="mechanic_id" required>
                            <option value="">-- Select a Mechanic --</option>

                        </select>
                        <div id="mechanicHelp" class="help-text">Select a date to see available mechanics.</div>
                    </div>

                    <button type="submit" class="btn">Update Appointment</button>
                </form>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('appointment_date');
            const mechanicSelect = document.getElementById('mechanic_id');
            const mechanicHelp = document.getElementById('mechanicHelp');
            const currentMechanicId = "<?php echo $current_mechanic_id; ?>";
            const appointmentId = "<?php echo $id; ?>";

            function fetchMechanics() {
                const selectedDate = dateInput.value;
                if (!selectedDate) {
                    mechanicSelect.innerHTML = '<option value="">-- Please select a date first --</option>';
                    mechanicSelect.disabled = true;
                    return;
                }


                fetch(`get_mechanics.php?date=${selectedDate}&exclude_id=${appointmentId}`)
                    .then(response => response.json())
                    .then(data => {
                        mechanicSelect.innerHTML = '<option value="">-- Select a Mechanic --</option>';

                        if (data.error) {
                            mechanicHelp.innerHTML = `<span class="error">${data.error}</span>`;
                            mechanicSelect.disabled = true;
                            return;
                        }

                        let availableCount = 0;

                        data.forEach(mechanic => {
                            const option = document.createElement('option');
                            option.value = mechanic.id;

                            let freeSpaces = parseInt(mechanic.free_places);


                            if (freeSpaces > 0 || mechanic.id == currentMechanicId) {
                                option.textContent = `${mechanic.name} (${freeSpaces} free places left)`;
                                availableCount++;
                            } else {
                                option.textContent = `${mechanic.name} (Fully booked)`;
                                option.disabled = true;
                            }


                            if (mechanic.id == currentMechanicId) {
                                option.selected = true;
                            }

                            mechanicSelect.appendChild(option);
                        });

                        if (availableCount === 0) {
                            mechanicHelp.innerHTML = '<span class="error">All mechanics are fully booked on this date.</span>';

                        } else {
                            mechanicHelp.textContent = 'Choose an available mechanic.';
                            mechanicSelect.disabled = false;
                        }
                    });
            }


            if (dateInput.value) {
                fetchMechanics();
            }

            dateInput.addEventListener('change', fetchMechanics);
        });
    </script>
</body>

</html>