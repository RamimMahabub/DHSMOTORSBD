<?php
$host = "sql102.infinityfree.com";
$user = "if0_41287696";
$pass = "XrT9wwQvNMbOGq";
$dbname = "if0_41287696_dhsmotors";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("
    CREATE TABLE IF NOT EXISTS mechanic_space_allocations (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        mechanic_id INT(6) UNSIGNED NOT NULL,
        allocation_date DATE NOT NULL,
        max_spaces INT NOT NULL DEFAULT 4,
        UNIQUE KEY uniq_mechanic_day (mechanic_id, allocation_date),
        FOREIGN KEY (mechanic_id) REFERENCES mechanics(id) ON DELETE CASCADE
    )
");
?>
