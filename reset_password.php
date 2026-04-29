<?php
session_start();
include 'db.php';

$error = "";
$success = "";
$token = $_GET['token'] ?? '';
$isValid = false;

if (!$token) {
    header("Location: login.php");
    exit();
}

// Verify token
$stmt = $pdo->prepare("SELECT * FROM students WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user) {
    $isValid = true;
} else {
    $error = "Invalid or expired reset token.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $isValid) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE students SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $updateStmt->execute([$hashed_password, $user['id']]);
        $success = "Password reset successfully! You can now login.";
        $isValid = false; // Hide form
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - The Startup Tank</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #060e1c; padding: 20px; }
        .login-box { background: var(--navy); padding: 40px; border-radius: 16px; border: 1px solid rgba(255,193,7,0.2); width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-box">
            <h2 style="font-family:'Bebas Neue'; font-size:36px; margin-bottom:10px;">NEW <span style="color:var(--gold);">PASSWORD</span></h2>
            
            <?php if ($success): ?>
                <p style="color: #4CAF50; margin-bottom: 20px; font-weight: 600;"><?php echo $success; ?></p>
                <a href="login.php" class="btn-primary" style="display:block; text-align:center;">Go to Login</a>
            <?php endif; ?>

            <?php if ($error): ?>
                <p style="color: #f44336; margin-bottom: 20px;"><?php echo $error; ?></p>
                <?php if (!$isValid && !$success): ?>
                    <a href="forgot_password.php" class="btn-secondary" style="display:block; text-align:center;">Request New Link</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($isValid): ?>
                <p style="color:rgba(255,255,255,0.6); margin-bottom:30px;">Set a strong password for your account.</p>
                <form action="" method="POST">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%;">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
