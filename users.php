<?php
include 'db.php';
include 'auth.php';
include 'nav.php'; 
if ($_SESSION['role'] !== 'admin') {
    die("Access Denied!");
}

$currentUserId = $_SESSION['user_id'] ?? 0;

// Add user
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // 👈 Removed password_hash
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php");
    exit();
}

// Delete user (not self)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== $currentUserId) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: users.php");
    exit();
}

// Role change
if (isset($_POST['change_role']) && isset($_POST['user_ids'])) {
    $new_role = $_POST['new_role'];
    foreach ($_POST['user_ids'] as $uid) {
        if ((int)$uid !== $currentUserId) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $uid);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: users.php");
    exit();
}

$result = $conn->query("SELECT id, username, role FROM users");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { background: #fff; padding: 20px; max-width: 600px; margin: auto; border-radius: 8px; }
        h2 { margin-top: 0; }
        input, select, button { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #ccc; font-size: 14px; }
        .btn { padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-primary { background: #007bff; color: white; }
        .btn-link { text-decoration: none; color: #007bff; font-size: 14px; }
        .checkbox-col { width: 30px; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="btn-link">← Back to Dashboard</a>
    <h2>User Management</h2>

    <!-- Add User -->
    <form method="POST">
        <input type="text" name="username" placeholder="New Username" required>
        <input type="password" name="password" placeholder="New Password" required>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
    </form>

    <!-- List Users -->
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th class="checkbox-col">✓</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="checkbox-col">
                        <?php if ($u['id'] !== $currentUserId): ?>
                            <input type="checkbox" name="user_ids[]" value="<?= $u['id'] ?>">
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($u['username']) ?>
                        <?php if ($u['id'] === $currentUserId): ?> <small>(You)</small><?php endif; ?>
                    </td>
                    <td><?= ucfirst($u['role']) ?></td>
                    <td>
                        <?php if ($u['id'] !== $currentUserId): ?>
                            <a href="?delete=<?= $u['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                        <?php else: ?>
                            <span style="color: gray;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 10px;">
            <select name="new_role" required>
                <option value="">-- Select Role --</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
            <button type="submit" name="change_role" class="btn btn-primary">Change Role</button>
        </div>
    </form>
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
