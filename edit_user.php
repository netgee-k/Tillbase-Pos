<?php
include 'db.php';
include 'auth.php';

if ($_SESSION['role'] != 'admin') {
    echo "Access Denied!";
    exit();
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM users WHERE id=$id");
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $role = $_POST['role'];

    $sql = "UPDATE users SET username='$username', password='$password', role='$role' WHERE id=$id";

    if ($conn->query($sql)) {
        header("Location: users.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Edit User - JojoMeds</title></head>
<body>
  <h2>Edit User</h2>
  <form action="edit_user.php?id=<?= $id ?>" method="POST">
    Username: <input type="text" name="username" value="<?= $user['username'] ?>" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    Role: 
    <select name="role" required>
      <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
      <option value="worker" <?= $user['role'] == 'worker' ? 'selected' : '' ?>>Worker</option>
    </select><br><br>
    <button type="submit">Update User</button>
  </form>
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
