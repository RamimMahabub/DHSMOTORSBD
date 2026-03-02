<?php
$host = "sql102.infinityfree.com";
$user = "if0_41287696";
$pass = "XrT9wwQvNMbOGq";
$dbname = "if0_41287696_dhsmotors";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>