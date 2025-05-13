<?php
include 'db.php';
include 'auth.php';
include 'nav.php'; 
if ($_SESSION['role'] != 'admin') {
    echo "Access Denied!";
    exit();
}

// Fetch users
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management - JojoMeds</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <h2>Manage Users</h2>
    <a href="add_user.php" class="add-user-btn">Add New User</a>
    <table class="user-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Role</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= $row['username'] ?></td>
          <td><?= ucfirst($row['role']) ?></td>
          <td>
            <a href="edit_user.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a> |
            <a href="delete_user.php?id=<?= $row['id'] ?>" class="delete-btn">Delete</a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
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
