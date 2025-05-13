<?php
include 'db.php';
include 'auth.php';

if ($_SESSION['role'] != 'admin') {
    echo "Access Denied!";
    exit();
}

$id = $_GET['id'];

if ($conn->query("DELETE FROM users WHERE id=$id")) {
    header("Location: users.php");
} else {
    echo "Error: " . $conn->error;
}
?>
