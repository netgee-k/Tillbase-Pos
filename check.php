<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>✅ Tillbase System Check</h2>";

$requiredTables = ['users', 'products', 'sales'];
$requiredFiles = ['index.php', 'dashboard.php', 'db.php', 'users.php', 'sale.php', 'manage_products.php', 'receipt.php'];

include 'db.php';

// 1. Check DB Connection
if ($conn->connect_error) {
    die("<p style='color:red;'>❌ Database connection failed: " . $conn->connect_error . "</p>");
} else {
    echo "<p style='color:green;'>✔ Connected to database successfully.</p>";
}

// 2. Check Tables
foreach ($requiredTables as $table) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows === 1) {
        echo "<p style='color:green;'>✔ Table '$table' exists.</p>";
    } else {
        echo "<p style='color:red;'>❌ Table '$table' is missing!</p>";
    }
}

// 3. Check Required Columns in `users`
$expectedUserCols = ['id', 'username', 'password', 'email', 'phone', 'role'];
$res = $conn->query("DESCRIBE users");
if ($res) {
    $cols = [];
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
    foreach ($expectedUserCols as $col) {
        if (in_array($col, $cols)) {
            echo "<p style='color:green;'>✔ Column '$col' exists in 'users'.</p>";
        } else {
            echo "<p style='color:red;'>❌ Column '$col' missing in 'users'.</p>";
        }
    }
} else {
    echo "<p style='color:red;'>❌ Unable to describe 'users' table.</p>";
}

// 4. Check for at least 1 user
$userRes = $conn->query("SELECT * FROM users LIMIT 1");
if ($userRes && $userRes->num_rows > 0) {
    echo "<p style='color:green;'>✔ At least one user exists in 'users'.</p>";
} else {
    echo "<p style='color:red;'>❌ No users found in 'users'.</p>";
}

// 5. Check Required PHP Files
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<p style='color:green;'>✔ File '$file' exists.</p>";
    } else {
        echo "<p style='color:red;'>❌ File '$file' is missing.</p>";
    }
}

$conn->close();
?>
