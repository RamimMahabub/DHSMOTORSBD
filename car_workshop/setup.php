<?php
$host = "sql102.infinityfree.com";
$user = "if0_41287696";
$pass = "XrT9wwQvNMbOGq";
$dbname = "if0_41287696_dhsmotors";

// Create connection
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$conn->select_db($dbname);


$conn->query("DROP TABLE IF EXISTS appointments");
$conn->query("DROP TABLE IF EXISTS mechanic_space_allocations");
$conn->query("DROP TABLE IF EXISTS admin_users");
$conn->query("DROP TABLE IF EXISTS mechanics");


$sql = "CREATE TABLE IF NOT EXISTS mechanics (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'mechanics' created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_address TEXT NOT NULL,
    client_phone VARCHAR(20) NOT NULL,
    car_license_no VARCHAR(50) NOT NULL,
    car_engine_no VARCHAR(50) NOT NULL,
    appointment_date DATE NOT NULL,
    mechanic_id INT(6) UNSIGNED,
    FOREIGN KEY (mechanic_id) REFERENCES mechanics(id)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'appointments' created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$sql = "CREATE TABLE IF NOT EXISTS mechanic_space_allocations (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mechanic_id INT(6) UNSIGNED NOT NULL,
    allocation_date DATE NOT NULL,
    max_spaces INT NOT NULL DEFAULT 4,
    UNIQUE KEY uniq_mechanic_day (mechanic_id, allocation_date),
    FOREIGN KEY (mechanic_id) REFERENCES mechanics(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'mechanic_space_allocations' created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}


$sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'admin_users' created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}


$mechanics = ['Rahim', 'Karim', 'Jamal', 'Kamal', 'Rafiq'];
foreach ($mechanics as $mechanic) {
    $check = $conn->query("SELECT * FROM mechanics WHERE name='$mechanic'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO mechanics (name) VALUES ('$mechanic')");
    }
}

//admin user
$admin_user = 'admin';
$admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
$check = $conn->query("SELECT * FROM admin_users WHERE username='$admin_user'");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO admin_users (username, password) VALUES ('$admin_user', '$admin_pass')");
}

echo "Setup complete. <a href='index.php'>Go to homepage</a>";
$conn->close();
?>
