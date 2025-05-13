<?php
include 'db.php'; // Include your database connection

require 'vendor/autoload.php'; // Include the barcode library

use Picqer\Barcode\BarcodeGeneratorPNG;

// Get all products from the database
$products = $conn->query("SELECT id, name FROM products");

$generator = new BarcodeGeneratorPNG();

// Array to store barcode base value for each product name (to handle duplicates)
$barcodeBase = [];

while ($product = $products->fetch_assoc()) {
    // Generate a random base barcode for each product (using product name and a random value)
    $baseBarcode = strtoupper(substr(sha1($product['name'] . rand()), 0, 10)); // Random 10 character barcode
    
    // Check if the barcode base already exists for this product name (for handling duplicates)
    if (!isset($barcodeBase[$product['name']])) {
        $barcodeBase[$product['name']] = 1; // Start with 001 for the first occurrence
    }

    // Create the final barcode by appending a sequential number for products with the same name
    $finalBarcode = $baseBarcode . '-' . str_pad($barcodeBase[$product['name']], 3, '0', STR_PAD_LEFT); // 001, 002, ...

    // Increment the counter for the next product with the same name
    $barcodeBase[$product['name']]++;

    // Generate the barcode image
    $barcodeImage = $generator->getBarcode($finalBarcode, $generator::TYPE_CODE_128);

    // Save the barcode as a PNG file (or other formats as needed)
    file_put_contents("barcodes/{$finalBarcode}.png", $barcodeImage);

    // Update the product table with the generated barcode
    $stmt = $conn->prepare("UPDATE products SET barcode = ? WHERE id = ?");
    $stmt->bind_param("si", $finalBarcode, $product['id']);
    $stmt->execute();
}

echo "Barcodes have been generated and added to products.";
?>
