<?php
session_start();
include 'db.php';

$message = "";
$error = "";
$resetLink = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $updateStmt = $pdo->prepare("UPDATE students SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $updateStmt->execute([$token, $expires, $email]);

        // Form the reset link
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $resetLink = "$protocol://$host/reset_password.php?token=$token";

        $message = "If an account exists with that email, a reset link has been generated.";
        // In a real app, you would mail($email, "Password Reset", "Click here: $resetLink");
    } else {
        // For security, don't reveal if user exists, but here we can be a bit more helpful for the demo
        $message = "If an account exists with that email, a reset link has been generated.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Forgot Password - The Startup Tank</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #060e1c; padding: 20px; }
        .login-box { background: var(--navy); padding: 40px; border-radius: 16px; border: 1px solid rgba(255,193,7,0.2); width: 100%; max-width: 400px; }
        .reset-debug { margin-top: 20px; padding: 15px; background: rgba(255,193,7,0.1); border: 1px dashed var(--gold); border-radius: 8px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-box" style="position: relative;">
            <a href="login.php" style="position: absolute; top: 20px; right: 20px; color: rgba(255,255,255,0.4); text-decoration: none; font-size: 24px; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">&times;</a>
            <h2 style="font-family:'Bebas Neue'; font-size:36px; margin-bottom:10px;">RESET <span style="color:var(--gold);">PASSWORD</span></h2>
            <p style="color:rgba(255,255,255,0.6); margin-bottom:30px;">Enter your email to receive a reset link.</p>
            
            <?php if ($message): ?>
                <p style="color: #4CAF50; margin-bottom: 20px; font-weight: 600;"><?php echo $message; ?></p>
                <?php if ($resetLink): ?>
                    <div class="reset-debug">
                        <p style="color:var(--gold); margin-bottom:10px;"><strong>[DEMO MODE] Reset Link Generated:</strong></p>
                        <a href="<?php echo $resetLink; ?>" style="color:white; text-decoration:underline; word-break: break-all;"><?php echo $resetLink; ?></a>
                        <p style="font-size:11px; margin-top:10px; opacity:0.6;">(This would normally be sent via email)</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($error): ?>
                <p style="color: #f44336; margin-bottom: 20px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="name@example.com">
                </div>
                <button type="submit" class="btn-primary" style="width:100%;">Generate Reset Link</button>
            </form>
            <p style="text-align:center; margin-top:20px; font-size:14px; color:rgba(255,255,255,0.6);">Remember your password? <a href="login.php" style="color:var(--gold);">Back to Login</a></p>
        </div>
    </div>
</body>
</html>
