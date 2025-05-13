<?php
include 'db.php';
include 'auth.php';

$id = $_GET['id'];

if ($conn->query("DELETE FROM medicines WHERE id=$id")) {
    header("Location: dashboard.php");
} else {
    echo "Error: " . $conn->error;
}
?>

