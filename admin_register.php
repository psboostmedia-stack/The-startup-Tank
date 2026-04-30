<?php
session_start();
include 'db.php';

// In a real application, you might want to restrict who can register as an admin
// For example, only if an existing admin is logged in, or with a secret key.
// For this demo, we'll allow public registration but with a notice.

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or Email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = "Admin account created successfully. <a href='admin_login.php' style='color:var(--blue-light);'>Login here</a>";
            } else {
                $error = "Failed to create account.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - The Startup Tank</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #060e1c; }
        .login-box { background: var(--navy); padding: 40px; border-radius: 16px; border: 2px solid var(--blue); width: 100%; max-width: 450px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-size: 14px; color: rgba(255,255,255,0.7); }
        input { width: 100%; padding: 12px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; outline: none; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-box">
            <h2 style="font-family:'Bebas Neue'; font-size:36px; margin-bottom:10px;">ADMIN <span style="color:var(--blue-light);">REGISTRATION</span></h2>
            <p style="color:rgba(255,255,255,0.6); margin-bottom:30px;">Create a new administrative account.</p>
            
            <?php if ($error): ?>
                <p style="color: #f44336; margin-bottom: 20px; font-size: 14px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p style="color: #4CAF50; margin-bottom: 20px; font-size: 14px;"><?php echo $success; ?></p>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn-primary" style="width:100%; background:var(--blue); border-color:var(--blue); color:white; margin-top:10px;">Register Admin</button>
            </form>
            
            <p style="text-align:center; margin-top:20px;">
                <a href="admin_login.php" style="color:rgba(255,255,255,0.4); font-size:12px;">Already have an account? Login</a>
            </p>
        </div>
    </div>
</body>
</html>
