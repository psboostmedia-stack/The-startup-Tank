<?php
session_start();
include 'db.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - The Startup Tank</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #060e1c; }
        .login-box { background: var(--navy); padding: 40px; border-radius: 16px; border: 2px solid var(--blue); width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-box">
            <h2 style="font-family:'Bebas Neue'; font-size:36px; margin-bottom:10px;">ADMIN <span style="color:var(--blue-light);">PORTAL</span></h2>
            <p style="color:rgba(255,255,255,0.6); margin-bottom:30px;">Manage students and post daily links.</p>
            
            <?php if ($error): ?>
                <p style="color: #f44336; margin-bottom: 20px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-primary" style="width:100%; background:var(--blue); border-color:var(--blue); color:white;">Login as Admin</button>
            </form>
            <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px; border: 1px dashed rgba(25,118,210,0.3); font-size: 13px; color: rgba(255,255,255,0.5);">
                <strong>Default Credentials:</strong><br>
                Username: <code style="color:var(--blue-light);">admin</code><br>
                Password: <code style="color:var(--blue-light);">admin123</code>
            </div>
            <p style="text-align:center; margin-top:20px;"><a href="index.php" style="color:rgba(255,255,255,0.4); font-size:12px;">Back to Website</a></p>
        </div>
    </div>
</body>
</html>
