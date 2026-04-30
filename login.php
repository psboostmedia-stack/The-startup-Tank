<?php
session_start();
include 'db.php';

if (isset($_SESSION['student_id'])) {
    header("Location: profile.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'pending') {
            $error = "Your application is pending verification. Please wait for an administrator to approve your enrollment.";
        } elseif ($user['status'] === 'rejected') {
            $error = "Your enrollment application has been rejected. Please contact support.";
        } else {
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['student_name'] = $user['first_name'] . ' ' . $user['last_name'];
            header("Location: profile.php");
            exit();
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Student Login - The Startup Tank</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #060e1c; padding: 20px; }
        .login-box { background: var(--navy); padding: 40px; border-radius: 16px; border: 1px solid rgba(255,193,7,0.2); width: 100%; max-width: 400px; }
        #reg_success { display: none; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-box" style="position: relative;">
            <a href="index.php" style="position: absolute; top: 20px; right: 20px; color: rgba(255,255,255,0.4); text-decoration: none; font-size: 24px; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">&times;</a>
            <h2 style="font-family:'Bebas Neue'; font-size:36px; margin-bottom:10px;">STUDENT <span style="color:var(--gold);">LOGIN</span></h2>
            <p style="color:rgba(255,255,255,0.6); margin-bottom:30px;">Access your innovation feed and profile.</p>
            
            <?php if (isset($_SESSION['registered_success'])): ?>
                <div id="reg_success" style="display: block; color: #4CAF50; margin-bottom: 20px; font-weight: 600; background: rgba(76,175,80,0.1); padding: 10px; border-radius: 4px; border-left: 4px solid #4CAF50;">Registration successful! Please login to your profile.</div>
                <?php unset($_SESSION['registered_success']); ?>
            <?php endif; ?>

            <script>
                // Preview logic for AI Studio environment
                window.addEventListener('DOMContentLoaded', () => {
                    const regSuccess = document.getElementById('reg_success');
                    if (window.location.search.includes('registered=1')) {
                        if (regSuccess) regSuccess.style.display = 'block';
                        
                        setTimeout(() => {
                            const url = new URL(window.location);
                            url.searchParams.delete('registered');
                            window.history.replaceState({}, document.title, url.pathname + url.search);
                        }, 1000);
                    }
                });
            </script>
            
            <?php if ($error): ?>
                <p style="color: #f44336; margin-bottom: 20px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required autocomplete="email">
                </div>
                <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <label>Password</label>
                        <a href="forgot_password.php" style="color:var(--gold); font-size:12px; margin-bottom:5px;">Forgot Password?</a>
                    </div>
                    <input type="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-primary" style="width:100%;">Login to Dashboard</button>
            </form>

            <div style="margin: 25px 0; display: flex; align-items: center; gap: 10px;">
                <div style="flex: 1; height: 1px; background: rgba(255,255,255,0.1);"></div>
                <div style="font-size: 11px; color: rgba(255,255,255,0.3); font-weight: 700; text-transform: uppercase;">Or continue with</div>
                <div style="flex: 1; height: 1px; background: rgba(255,255,255,0.1);"></div>
            </div>

            <button onclick="alert('Google Login is coming soon!')" class="btn-secondary" style="width:100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px; border-color: rgba(255,255,255,0.1); color: white;">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18" alt="Google Logo">
                Login with Google
            </button>
            <p style="text-align:center; margin-top:20px; font-size:14px; color:rgba(255,255,255,0.6);">Don't have an account? <a href="index.php" style="color:var(--gold);">Register Now</a></p>
        </div>
    </div>
</body>
</html>
