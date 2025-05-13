<?php
include 'db.php';
include 'config.php'; // Include the common settings

// Ensure that sale_id is passed
if (!isset($_GET['id'])) {
    die("Sale ID is missing.");
}

$sale_id = $_GET['id'];
$auto = isset($_GET['auto']) ? $_GET['auto'] : false;
$change = isset($_GET['change']) ? (float)$_GET['change'] : 0.0;

$sale = $conn->query("SELECT s.*, p.name AS product_name, p.price FROM sales s JOIN products p ON s.product_id = p.id WHERE s.id = $sale_id")->fetch_assoc();
$payment = $conn->query("SELECT * FROM payments WHERE sale_id = $sale_id")->fetch_assoc();

if (!$sale || !$payment) {
    die("Sale or payment not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - Tillbase</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .receipt { border: 1px solid #ccc; padding: 20px; max-width: 400px; margin: auto; margin-top: 40px; }
        img.logo { width: 100px; margin-bottom: 10px; }
        .small { font-size: 0.8em; color: #777; }
        .company-details { font-size: 0.9em; margin-bottom: 20px; }
        .footer { font-size: 0.8em; margin-top: 20px; color: #777; }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Company Information -->
        <div class="company-details">
            <img src="<?= LOGO_PATH ?>" class="logo" alt="<?= COMPANY_NAME ?> Logo">
            <h3><?= COMPANY_NAME ?></h3>
            <p><?= COMPANY_ADDRESS ?></p>
            <p><?= COMPANY_PHONE ?></p>
        </div>

        <!-- Receipt Details -->
        <h2>Receipt</h2>
        <p><strong>Product:</strong> <?= $sale['product_name'] ?></p>
        <p><strong>Quantity:</strong> <?= $sale['qty_sold'] ?></p>
        <p><strong>Total Paid:</strong> KES <?= number_format($payment['amount'], 2) ?></p>
        <p><strong>Payment Method:</strong> <?= ucfirst($payment['method']) ?></p>
        <p><strong>Change:</strong> KES <?= number_format($change, 2) ?></p>
        <p><strong>Paid By:</strong> <?= $payment['customer'] ?></p>
        <p class="small"><?= $payment['created_at'] ?></p>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for shopping with us!</p>
            <p>Served by: <?= $_SESSION['username'] ?></p>
        </div>
    </div>

    <?php if ($auto): ?>
        <script>
            window.onload = () => {
                window.print();  // Automatically print the receipt
                setTimeout(() => {
                    window.location.href = "dashboard.php";  // Redirect to dashboard after printing
                }, 1500);
            };
        </script>
    <?php endif; ?>
</body>
</html>
