<?php
session_start();
include 'db.php';

$error = "";
$success = "";
$token = $_GET['token'] ?? '';

if (!$token) {
    header("Location: admin_login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM admins WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$admin = $stmt->fetch();

if (!$admin) {
    $error = "Invalid or expired reset token.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $admin) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        if ($stmt->execute([$hashed_password, $admin['id']])) {
            $success = "Password has been reset successfully. <a href='admin_login.php' style='color:var(--blue-light);'>Login now</a>";
        } else {
            $error = "Failed to update password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password - The Startup Tank</title>
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
            <h2 style="font-family:'Bebas Neue'; font-size:36px; margin-bottom:10px;">RESET <span style="color:var(--blue-light);">PASSWORD</span></h2>
            <p style="color:rgba(255,255,255,0.6); margin-bottom:30px;">Enter your new admin password below.</p>
            
            <?php if ($error): ?>
                <p style="color: #f44336; margin-bottom: 20px; font-size: 14px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p style="color: #4CAF50; margin-bottom: 20px; font-size: 14px;"><?php echo $success; ?></p>
            <?php else: ?>
                <?php if ($admin): ?>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="password" required minlength="6" style="width:100%; padding:12px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" required minlength="6" style="width:100%; padding:12px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                        </div>
                        <button type="submit" class="btn-primary" style="width:100%; background:var(--blue); border-color:var(--blue); color:white; margin-top:10px;">Reset Password</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            
            <p style="text-align:center; margin-top:20px;">
                <a href="admin_login.php" style="color:rgba(255,255,255,0.4); font-size:12px;">Back to Login</a>
            </p>
        </div>
    </div>
</body>
</html>
