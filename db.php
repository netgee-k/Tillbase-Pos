<?php
$host = "localhost";
$user = "tillbase";
$pass = "tillbase";
$dbname = "Tillbase";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>
