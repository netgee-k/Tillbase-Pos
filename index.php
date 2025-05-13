<?php
session_start();
$_SESSION['user_id'] = $user['id'];  // Store user ID for tracking sales

include 'db.php';

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // Reminder: use password hashing in production

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "❌ Invalid username or password!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Tillbase</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        }
        .wrapper {
            display: flex;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 900px;
            width: 100%;
            overflow: hidden;
        }
        .login-image {
            flex: 1;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-image img {
            width: 100%;
            max-width: 400px;
            height: auto;
            object-fit: contain;
        }
        .login-form {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-form h2 {
            margin-bottom: 30px;
            font-size: 28px;
            color: #333;
            text-align: center;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }
        button {
            padding: 14px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #1565c0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="login-image">
            <img src="logo.png" alt="Tillbase Logo">
        </div>
        <div class="login-form">
            <h2>Login to Tillbase</h2>
            <?php if ($error): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
