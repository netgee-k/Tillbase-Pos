<?php
include 'db.php';
include 'auth.php';
include 'nav.php';

$isEditing = false;
$editData = [];
$feedback = '';

// Handle add or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $price = $_POST['price'];
    $cost = $_POST['cost'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'];

    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $id = (int)$_POST['edit_id'];
        $stmt = $conn->prepare("UPDATE products SET name=?, brand=?, price=?, cost=?, quantity=?, expiry_date=? WHERE id=?");
        $stmt->bind_param("ssddisi", $name, $brand, $price, $cost, $quantity, $expiry_date, $id);
        $feedback = $stmt->execute() ? 'Product updated successfully!' : 'Error updating product: ' . $stmt->error;
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, brand, price, cost, quantity, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddis", $name, $brand, $price, $cost, $quantity, $expiry_date);
        $feedback = $stmt->execute() ? 'Product added successfully!' : 'Error adding product: ' . $stmt->error;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $feedback = $conn->query("DELETE FROM products WHERE id = $id") ? 'Product deleted successfully!' : 'Error deleting product.';
}

// Handle edit
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $editData = $result->fetch_assoc();
        $isEditing = true;
    }
}

// Search and filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
$where = [];
if ($search) $where[] = "name LIKE '%$search%' OR brand LIKE '%$search%'";
if ($filter === 'expired') $where[] = "expiry_date < CURDATE()";
if ($filter === 'valid') $where[] = "expiry_date >= CURDATE()";
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination
$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;
$totalResult = $conn->query("SELECT COUNT(*) as count FROM products $whereClause")->fetch_assoc();
$totalRows = $totalResult['count'];
$totalPages = ceil($totalRows / $limit);

// Fetch products

$products = $conn->query("SELECT * FROM products $whereClause ORDER BY name ASC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - Tillbase</title>
    <style>
        body { font-family: Arial; background: #f4f4f9; }
        .container { width: 90%; max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; margin-top: 20px; }
        h2, h3 { margin-top: 0; }
        input, select, button { width: 100%; padding: 8px; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 14px; }
        th { background: #2196F3; color: white; }
        .btn { padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-edit { background: #ffc107; color: #000; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-save { background: #4CAF50; color: white; width: 100%; }
        .back-btn { background: #2196F3; color: white; padding: 8px; text-align: center; display: inline-block; border-radius: 5px; text-decoration: none; }
        .message { background: #e0ffe0; color: #2d7a2d; padding: 10px; margin: 10px 0; border: 1px solid #b2d8b2; }
    </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h2><?= $isEditing ? 'Edit Product' : 'Add New Product' ?></h2>

    <?php if ($feedback): ?>
        <div class="message"><?= htmlspecialchars($feedback) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="edit_id" value="<?= $editData['id'] ?? '' ?>">
        <label>Name</label>
        <input type="text" name="name" value="<?= $editData['name'] ?? '' ?>" required>
        <label>Brand</label>
        <input type="text" name="brand" value="<?= $editData['brand'] ?? '' ?>" required>
        <label>Price (KES)</label>
        <input type="number" step="0.01" name="price" value="<?= $editData['price'] ?? '' ?>" required>
        <label>Cost (KES)</label>
        <input type="number" step="0.01" name="cost" value="<?= $editData['cost'] ?? '' ?>" required>
        <label>Quantity</label>
        <input type="number" name="quantity" value="<?= $editData['quantity'] ?? '' ?>" required>
        <label>Expiry Date</label>
        <input type="date" name="expiry_date" value="<?= $editData['expiry_date'] ?? '' ?>" required>
        <button type="submit" class="btn btn-save"><?= $isEditing ? 'Update' : 'Add' ?> Product</button>
    </form>

    <h3>All Products</h3>
    <form method="GET" style="display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search name/brand" value="<?= htmlspecialchars($search) ?>">
        <select name="filter">
            <option value="">-- Filter --</option>
            <option value="expired" <?= $filter === 'expired' ? 'selected' : '' ?>>Expired</option>
            <option value="valid" <?= $filter === 'valid' ? 'selected' : '' ?>>Valid</option>
        </select>
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Brand</th>
                <th>Price</th>
                <th>Cost</th>
                <th>Qty</th>
                <th>Expiry</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $products->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['brand']) ?></td>
                <td><?= number_format($row['price'], 2) ?></td>
                <td><?= number_format($row['cost'], 2) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= $row['expiry_date'] ?></td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div style="margin-top: 20px; text-align: center;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>" style="margin: 0 5px;<?= $i === $page ? ' font-weight: bold;' : '' ?>">[<?= $i ?>]</a>
        <?php endfor; ?>
    </div>
</div>
</body>
</html>