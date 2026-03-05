<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Workshop Appointment</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">DHS MOTORS BANGLADESH</a>
        <div class="navbar-links">
            <a href="admin_login.php" class="btn-nav">Admin Login</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <header>
                <h1 style="font-size: 1.25rem;">Book an Appointment</h1>
                <p>Schedule your vehicle's checkup online</p>
            </header>

            <div class="form-container">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message <?php echo $_SESSION['messageType']; ?>">
                        <?php
                        echo htmlspecialchars($_SESSION['message']);
                        unset($_SESSION['message']);
                        unset($_SESSION['messageType']);
                        ?>
                    </div>
                <?php endif; ?>

                <form id="appointmentForm" action="process_appointment.php" method="POST">

                    <div class="form-group">
                        <label for="client_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="client_name" name="client_name" placeholder="e.g. Abul Hasan" required>
                        <span class="error" id="nameError"></span>
                    </div>

                    <div class="form-group">
                        <label for="client_address">Address</label>
                        <textarea id="client_address" name="client_address" rows="2"
                            placeholder="e.g. Mirpur 10, Dhaka"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="client_phone">Phone Number <span class="required">*</span></label>
                        <input type="text" id="client_phone" name="client_phone" placeholder="e.g. 01712345678"
                            required>
                        <span class="error" id="phoneError"></span>
                    </div>

                    <div id="vehicles_container">
                        <div class="vehicle-box" id="vehicle_box_1" data-index="1">
                            <h3>Vehicle 1</h3>
                            <div class="form-group">
                                <label for="car_license_no_1">Car License Number <span class="required">*</span></label>
                                <input type="text" id="car_license_no_1" name="car_license_no[]"
                                    placeholder="e.g. Dhaka Metro-Ga 12-3456" required>
                                <span class="error error-license"></span>
                            </div>

                            <div class="form-group">
                                <label for="car_engine_no_1">Car Engine Number (Digits only) <span
                                        class="required">*</span></label>
                                <input type="text" id="car_engine_no_1" name="car_engine_no[]"
                                    placeholder="e.g. 987654321" required>
                                <span class="error error-engine"></span>
                            </div>

                            <div class="form-group">
                                <label for="appointment_date_1">Appointment Date <span class="required">*</span></label>
                                <input type="date" id="appointment_date_1" name="appointment_date[]"
                                    class="appointment-date" required>
                                <span class="error error-date"></span>
                            </div>

                            <div class="form-group mb-0">
                                <label for="mechanic_id_1">Select Mechanic <span class="required">*</span></label>
                                <select id="mechanic_id_1" name="mechanic_id[]" class="mechanic-select" required
                                    disabled>
                                    <option value="">-- Please select a date first --</option>
                                </select>
                                <div class="help-text help-mechanic">Choose a date to see available mechanics. Limits
                                    can vary by mechanic and date.</div>
                                <span class="error error-mechanic"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="text-align: right;">
                        <button type="button" id="add_vehicle_btn" class="btn btn-secondary"
                            style="display: inline-block; width: auto; padding: 0.5rem 1rem;">+ Add Another Car</button>
                    </div>

                    <button type="submit" class="btn" id="submitBtn">Book Appointment(s)</button>
                </form>
                <div id="formMessage"></div>
            </div>

        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>