<?php
include 'db.php';
include 'auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid or missing ID.");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM medicines WHERE id = $id");

if (!$result || $result->num_rows === 0) {
    die("Medicine not found.");
}

$medicine = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $brand = $conn->real_escape_string($_POST['brand']);
    $price = floatval($_POST['price']);
    $cost = floatval($_POST['cost']);
    $quantity = intval($_POST['quantity']);
    $expiry_date = $_POST['expiry_date'];

    $sql = "UPDATE medicines 
            SET name='$name', brand='$brand', price=$price, cost=$cost, quantity=$quantity, expiry_date='$expiry_date' 
            WHERE id=$id";

    if ($conn->query($sql)) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Medicine - JojoPharma</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
  <main class="edit-main">
    <h2 class="page-title">Edit Medicine</h2>
    <form class="edit-form" method="POST">
      <label>
        Name:
        <input type="text" name="name" value="<?= htmlspecialchars($medicine['name']) ?>" required>
      </label>
      <label>
        Brand:
        <input type="text" name="brand" value="<?= htmlspecialchars($medicine['brand']) ?>" required>
      </label>
      <label>
        Price (KES):
        <input type="number" name="price" step="0.01" value="<?= $medicine['price'] ?>" required>
      </label>
      <label>
        Cost (KES):
        <input type="number" name="cost" step="0.01" value="<?= $medicine['cost'] ?>" required>
      </label>
      <label>
        Quantity:
        <input type="number" name="quantity" value="<?= $medicine['quantity'] ?>" required>
      </label>
      <label>
        Expiry Date:
        <input type="date" name="expiry_date" value="<?= $medicine['expiry_date'] ?>" required>
      </label>
      <button type="submit">Update Medicine</button>
    </form>
    <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
  </main>
</body>
</html>
