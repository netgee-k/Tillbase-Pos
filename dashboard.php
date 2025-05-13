<?php
include 'db.php';
include 'auth.php';
include 'nav.php'; 
// Stats queries
$total      = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'] ?? 0;
$expiring   = $conn->query("SELECT COUNT(*) AS soon FROM products WHERE expiry_date <= CURDATE() + INTERVAL 30 DAY")->fetch_assoc()['soon'] ?? 0;
$profit     = $conn->query("SELECT SUM((price - cost) * quantity) AS profit FROM products")->fetch_assoc()['profit'] ?? 0.0;

// Low-stock & expiring soon lists
$lowStock   = $conn->query("SELECT name, quantity FROM products WHERE quantity <= 10");
$expiring7  = $conn->query("SELECT name, expiry_date FROM products WHERE expiry_date <= CURDATE() + INTERVAL 7 DAY");

// Recent sales 
$recentSales = $conn->query("
  SELECT s.id, p.name, s.qty_sold, s.sale_date
  FROM sales s
  JOIN products p ON s.product_id = p.id
  ORDER BY s.sale_date DESC
  LIMIT 5
");

// Fetch products for inventory and sale form
$prods = $conn->query("SELECT id, name, brand, quantity, price FROM products ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - TillBase POS</title>
  <link rel="stylesheet" href="dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f9;
      margin: 0;
      padding: 0;
    }

    nav {
      background: #2196F3;
      padding: 10px;
      display: flex;
      justify-content: center;
    }

    nav a {
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      margin: 0 10px;
      border-radius: 4px;
    }

    nav a:hover {
      background-color: #1976D2;
    }

    main {
      display: flex;
      justify-content: space-between;
      margin: 20px;
    }

    .column-left, .column-right {
      width: 48%;
    }

    .widget {
      background: white;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .form-container input,
    .form-container select,
    .form-container button {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 5px;
    }

    .btn-edit, .btn-delete {
      margin-right: 10px;
      text-decoration: none;
      padding: 8px 12px;
      border-radius: 4px;
    }

    .btn-edit {
      background-color: #4CAF50;
      color: white;
    }

    .btn-delete {
      background-color: #F44336;
      color: white;
    }

    footer {
      background: #2196F3;
      padding: 10px;
      color: white;
      text-align: center;
    }

    footer a {
      color: white;
      text-decoration: none;
    }

    footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <header>
  </header>

  <main>
    <!-- Left Column: Inventory & Sales -->
    <section class="column-left">
    
      <!-- Inventory Search -->
      <div class="widget">
        <h3>Search Inventory</h3>
        <input
          type="text"
          id="searchInput"
          placeholder="Search by name or brand"
          onkeyup="filterTable()">
      </div>

      <!-- Make Sale Form -->
      <div class="widget">
        <h3>Make a Sale</h3>
        <form class="sale-form" method="POST" action="sale.php">
          <label>
            Product
            <select name="product_id" id="product_id" required>
              <option value="">— Select —</option>
              <?php
              $prods->data_seek(0);
              while($p = $prods->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>" data-barcode="<?= $p['barcode'] ?>">
                  <?= htmlspecialchars("{$p['name']} ({$p['brand']}) — {$p['quantity']} in stock") ?>
                </option>
              <?php endwhile; ?>
            </select>
          </label>
          <label>
            Or Scan Barcode
            <input type="text" id="barcode" name="barcode" placeholder="Scan Barcode" />
          </label>
          <label>
            Quantity
            <input type="number" name="qty_sold" min="1" placeholder="Units" required>
          </label>
          <label>
            Payment Method
            <select name="payment_method" required>
              <option value="cash">Cash</option>
              <option value="mpesa">M-Pesa</option>
            </select>
          </label>
          <label id="cash_amount" style="display:none;">
            Cash Paid
            <input type="number" name="cash_paid" placeholder="Amount Paid" />
          </label>
          <button type="submit">Record Sale</button>
        </form>
      </div>

      <!-- Inventory Table -->
      <div class="widget">
        <h3>Products Inventory</h3>
        <table class="med-table" id="medTable">
          <thead>
            <tr>
              <th>Name</th>
              <th>Brand</th>
              <th>Qty</th>
              <th>Price (KES)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $prods->data_seek(0);
              while($row = $prods->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['brand']) ?></td>
              <td><?= (int)$row['quantity'] ?></td>
              <td><?= number_format($row['price'], 2) ?></td>
              <td>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                <a href="delete.php?id=<?= $row['id'] ?>" class="btn-delete"
                   onclick="return confirm('Delete <?= htmlspecialchars($row['name']) ?>?')">
                  Delete
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </section>

    <!-- Right Column: Widgets -->
    <aside class="column-right">
      <div class="widget">
        <h3>Quick Stats</h3>
        <ul class="stats">
          <li><strong><?= $total ?></strong><br>Total Products</li>
          <li><strong><?= $expiring ?></strong><br>Expiring Soon</li>
          <li><strong>KES <?= number_format($profit,2) ?></strong><br>Profit</li>
        </ul>
      </div>

      <div class="widget">
        <h3>Low Stock Alerts</h3>
        <ul>
          <?php while($r = $lowStock->fetch_assoc()): ?>
          <li><?= htmlspecialchars($r['name']) ?> — <?= (int)$r['quantity'] ?> left</li>
          <?php endwhile; ?>
          <?php if ($lowStock->num_rows === 0): ?>
          <li>All stock levels OK</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="widget">
        <h3>Expiring in 7 Days</h3>
        <ul>
          <?php while($e = $expiring7->fetch_assoc()): ?>
          <li><?= htmlspecialchars($e['name']) ?> — <?= $e['expiry_date'] ?></li>
          <?php endwhile; ?>
          <?php if ($expiring7->num_rows === 0): ?>
          <li>No upcoming expiries</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="widget">
        <h3>Recent Sales</h3>
        <ul>
          <?php while($s = $recentSales->fetch_assoc()): ?>
          <li><?= htmlspecialchars($s['name']) ?>: <?= $s['qty_sold'] ?> units on <?= $s['sale_date'] ?></li>
          <?php endwhile; ?>
          <?php if ($recentSales->num_rows === 0): ?>
          <li>No recent sales</li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Chart -->
      <div class="widget chart-widget">
        <canvas id="medChart"></canvas>
      </div>
    </aside>
  </main>

  <footer>
    <p>&copy; 2025 TillBase  Secure Point of Sale | <a href="privacy.php">Privacy Policy</a></p>
  </footer>

  <script>
    // Inventory search filter
    function filterTable() {
      const filter = document.getElementById('searchInput').value.toLowerCase();
      document.querySelectorAll('#medTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
      });
    }

    // Chart.js
    const ctx = document.getElementById('medChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Total', 'Expiring', 'Profit (K)'],
        datasets: [{
          label: 'Stats',
          data: [<?= $total ?>, <?= $expiring ?>, <?= round($profit/1000,2) ?>],
          backgroundColor: ['#2196F3','#FFC107','#4CAF50']
        }]
      },
      options: { scales: { y: { beginAtZero: true } } }
    });
  </script>

</body>

<script src="fullscreen-logout.js"></script>
<script>
// Trigger full-screen mode and lockdown when the page loads
window.onload = function() {
    triggerFullScreen();
    lockDownPage();
};
</script>

</html>
