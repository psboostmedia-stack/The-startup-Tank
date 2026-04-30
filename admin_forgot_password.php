<?php
session_start();
include 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $pdo->prepare("UPDATE admins SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $admin['id']]);

        // In a real app, send email here. In this environment, we simulate it.
        $reset_link = "admin_reset_password.php?token=" . $token;
        $success = "Password reset link has been generated. <br><br> 
                   <a href='$reset_link' style='color:var(--blue-light); font-weight:bold;'>CLICK HERE TO RESET PASSWORD</a><br>
                   <small>(Simulated email: Link sent to $email)</small>";
    } else {
        $error = "No admin account found with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Admin Password - The Startup Tank</title>
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
            <h2 style="font-family:'Bebas Neue'; font-size:36px; margin-bottom:10px;">FORGOT <span style="color:var(--blue-light);">PASSWORD?</span></h2>
            <p style="color:rgba(255,255,255,0.6); margin-bottom:30px;">Enter your admin email to reset your password.</p>
            
            <?php if ($error): ?>
                <p style="color: #f44336; margin-bottom: 20px; font-size: 14px; text-align:center;"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background: rgba(76,175,80,0.1); border: 1px solid #4CAF50; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; color: #4CAF50; text-align:center;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required style="width:100%; padding:12px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                </div>
                <button type="submit" class="btn-primary" style="width:100%; background:var(--blue); border-color:var(--blue); color:white; margin-top:10px;">Send Reset Link</button>
            </form>
            
            <p style="text-align:center; margin-top:20px;">
                <a href="admin_login.php" style="color:rgba(255,255,255,0.4); font-size:12px;">Back to Login</a>
            </p>
        </div>
    </div>
</body>
</html>
