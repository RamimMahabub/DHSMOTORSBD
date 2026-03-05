<?php
session_start();
require_once 'db.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}


if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

$DEFAULT_MAX_APPOINTMENTS = 4;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_space_allocation') {
    $allocation_date = $conn->real_escape_string($_POST['allocation_date'] ?? '');
    $mechanic_id = isset($_POST['allocation_mechanic_id']) ? (int) $_POST['allocation_mechanic_id'] : 0;
    $max_spaces = isset($_POST['max_spaces']) ? (int) $_POST['max_spaces'] : -1;

    if (empty($allocation_date) || $mechanic_id <= 0 || $max_spaces < 0) {
        $_SESSION['message'] = "Please select date, mechanic and a valid max free spaces value.";
        $_SESSION['messageType'] = "error";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO mechanic_space_allocations (mechanic_id, allocation_date, max_spaces)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE max_spaces = VALUES(max_spaces)
        ");

        if ($stmt) {
            $stmt->bind_param("isi", $mechanic_id, $allocation_date, $max_spaces);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Mechanic max free spaces updated for " . date('d M Y', strtotime($allocation_date)) . ".";
                $_SESSION['messageType'] = "success";
            } else {
                $_SESSION['message'] = "Failed to update space allocation.";
                $_SESSION['messageType'] = "error";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Unable to prepare allocation update.";
            $_SESSION['messageType'] = "error";
        }
    }

    header("Location: admin_dashboard.php");
    exit;
}

$mechanics_sql = "SELECT id, name FROM mechanics ORDER BY name ASC";
$mechanics_result = $conn->query($mechanics_sql);

// Fetch appointments
$sql = "
    SELECT 
        a.id, 
        a.client_name, 
        a.client_phone, 
        a.car_license_no, 
        a.appointment_date, 
        m.name as mechanic_name 
    FROM appointments a
    JOIN mechanics m ON a.mechanic_id = m.id
    ORDER BY a.appointment_date DESC, a.id DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Workshop</title>
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
        <div class="container" style="max-width: 900px;">
            <header class="admin-header">
                <div>
                    <h1 style="margin-bottom: 0;">Admin Dashboard</h1>
                    <p>Manage Appointments</p>
                </div>
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

                <div class="vehicle-box" style="margin-bottom: 2rem;">
                    <h3>Mechanic Space Allocation</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="save_space_allocation">
                        <div class="form-group">
                            <label for="allocation_date">Select Date</label>
                            <input type="date" id="allocation_date" name="allocation_date" required>
                        </div>

                        <div class="form-group">
                            <label for="allocation_mechanic_id">Select Mechanic</label>
                            <select id="allocation_mechanic_id" name="allocation_mechanic_id" required>
                                <option value="">-- Select a Mechanic --</option>
                                <?php if ($mechanics_result && $mechanics_result->num_rows > 0): ?>
                                    <?php while ($mech = $mechanics_result->fetch_assoc()): ?>
                                        <option value="<?php echo (int) $mech['id']; ?>">
                                            <?php echo htmlspecialchars($mech['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="max_spaces">Max Free Spaces For Selected Day</label>
                            <input type="number" id="max_spaces" name="max_spaces" min="0"
                                value="<?php echo $DEFAULT_MAX_APPOINTMENTS; ?>" required>
                            <div id="allocationHelp" class="help-text">Pick a date and mechanic to load current capacity.
                            </div>
                        </div>

                        <button type="submit" class="btn">Save Space Allocation</button>
                    </form>
                </div>

                <h2>Appointment List</h2>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Phone</th>
                                <th>Car License</th>
                                <th>Date</th>
                                <th>Mechanic</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($row['client_name']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['client_phone']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['car_license_no']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(date('d M Y', strtotime($row['appointment_date']))); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['mechanic_name']); ?>
                                        </td>
                                        <td class="action-links">
                                            <a href="admin_edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No appointments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <footer>
                <p>&copy;
                    <?php echo date('Y'); ?> Dhaka Auto Repair Workshop
                </p>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('allocation_date');
            const mechanicSelect = document.getElementById('allocation_mechanic_id');
            const maxSpacesInput = document.getElementById('max_spaces');
            const allocationHelp = document.getElementById('allocationHelp');
            const defaultMax = <?php echo (int) $DEFAULT_MAX_APPOINTMENTS; ?>;

            function loadAllocation() {
                const selectedDate = dateInput.value;
                const selectedMechanic = mechanicSelect.value;

                if (!selectedDate || !selectedMechanic) {
                    maxSpacesInput.value = defaultMax;
                    allocationHelp.textContent = 'Pick a date and mechanic to load current capacity.';
                    return;
                }

                fetch(`get_space_allocation.php?date=${selectedDate}&mechanic_id=${selectedMechanic}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            allocationHelp.innerHTML = `<span class="error">${data.error}</span>`;
                            maxSpacesInput.value = defaultMax;
                            return;
                        }

                        maxSpacesInput.value = data.max_spaces;
                        const sourceText = data.source === 'custom' ? 'custom' : 'default';
                        allocationHelp.textContent = `Current ${sourceText} max: ${data.max_spaces}, booked: ${data.booked_spaces}, free: ${data.free_spaces}.`;
                    })
                    .catch(() => {
                        allocationHelp.innerHTML = '<span class="error">Failed to load allocation info.</span>';
                        maxSpacesInput.value = defaultMax;
                    });
            }

            dateInput.addEventListener('change', loadAllocation);
            mechanicSelect.addEventListener('change', loadAllocation);
        });
    </script>
</body>

</html>
