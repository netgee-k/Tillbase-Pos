<?php
include 'db.php';

$amount = $_POST['amount'];
$customer = $_POST['customer'] ?? 'Walk-in';
$date = date('Y-m-d H:i:s');

// Insert into payments table
$stmt = $conn->prepare("INSERT INTO payments (customer, method, amount, created_at) VALUES (?, 'cash', ?, ?)");
$stmt->bind_param("sds", $customer, $amount, $date);
$stmt->execute();
$payment_id = $stmt->insert_id;
$stmt->close();
$conn->close();

// Redirect to auto-printing receipt
header("Location: print_receipt.php?id=$payment_id&auto=1");
exit();
?>
