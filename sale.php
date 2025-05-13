<?php
include 'db.php';
include 'auth.php';

session_start();

// Process the sale and payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $barcode = mysqli_real_escape_string($conn, $_POST['barcode']);
    $qty_sold = (int)$_POST['qty_sold'];
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $cash_paid = isset($_POST['cash_paid']) ? (float)$_POST['cash_paid'] : 0.0;

    // Check if a product was selected or barcode scanned
    if (empty($product_id) && empty($barcode)) {
        header('Location: dashboard.php?error=Product not selected or barcode not scanned');
        exit();
    }

    // Get product details
    $product_query = $conn->query("SELECT * FROM products WHERE id = $product_id OR barcode = '$barcode'");
    if (!$product_query) {
        die('Error fetching product: ' . $conn->error);
    }
    $product = $product_query->fetch_assoc();

    if ($product) {
        $current_stock = (int)$product['quantity'];
        $price = (float)$product['price'];
        $total = $qty_sold * $price;

        if ($current_stock >= $qty_sold) {
            // Update stock
            $new_stock = $current_stock - $qty_sold;
            $update_stock_query = $conn->query("UPDATE products SET quantity = $new_stock WHERE id = {$product['id']}");
            if (!$update_stock_query) {
                die('Error updating stock: ' . $conn->error);
            }

            // Get logged-in user ID
            $username = $_SESSION['username'];
            $user_query = $conn->query("SELECT id FROM users WHERE username = '$username'");
            $user = $user_query->fetch_assoc();
            $user_id = $user['id'];

            // Record sale
            $sale_date = date('Y-m-d H:i:s');
            $sale_query = $conn->query("INSERT INTO sales (product_id, qty_sold, sale_date, total_amount, user_id) 
                                        VALUES ({$product['id']}, $qty_sold, '$sale_date', $total, $user_id)");
            if (!$sale_query) {
                die('Error recording sale: ' . $conn->error);
            }
            $sale_id = $conn->insert_id;

            // Handle payment
            $customer = 'Walk-in'; // You can change this based on actual customer data if necessary
            $change = 0;
            if ($payment_method === 'cash') {
                if ($cash_paid < $total) {
                    // Not enough cash paid
                    header("Location: dashboard.php?error=Insufficient cash payment");
                    exit();
                }
                $change = $cash_paid - $total; // Calculate change
            }
            $payment_query = $conn->query("INSERT INTO payments (customer, method, amount, created_at, sale_id, change_amount)
                                          VALUES ('$customer', '$payment_method', $total, '$sale_date', $sale_id, $change)");
            if (!$payment_query) {
                die('Error recording payment: ' . $conn->error);
            }

            // Redirect to receipt
            header("Location: receipt.php?id=$sale_id&change=$change&auto=1");
            exit();
        } else {
            header('Location: dashboard.php?error=Not enough stock available');
            exit();
        }
    } else {
        header('Location: dashboard.php?error=Product not found');
        exit();
    }
}
?>
