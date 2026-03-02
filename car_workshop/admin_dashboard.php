<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

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
</body>

</html>