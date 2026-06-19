<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - E-Commerce Terminal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Account Registration Portal</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="badge danger" style="display:block; margin-bottom:15px; padding:10px;">
                <?php
                    if ($_GET['error'] === 'invalid_inputs') echo "Validation Error: Please verify all field formats.";
                    if ($_GET['error'] === 'email_exists') echo "Conflict: This email address is already registered.";
                    if ($_GET['error'] === 'system_fault') echo "System failure. Please contact support.";
                ?>
            </div>
        <?php endif; ?>

        <form action="../src/auth-handler.php?action=register" method="POST">
            <label for="name">Full Structural Identifier Name</label>
            <input type="text" id="name" name="name" placeholder="John Doe" required>

            <label for="email">Communication Electronic Address (Email)</label>
            <input type="email" id="email" name="email" placeholder="john@example.com" required>

            <label for="password">Security Access Code (Password - Min 8 Characters)</label>
            <input type="password" id="password" name="password" minlength="8" placeholder="••••••••" required>

            <button type="submit">Commit Registry Profile</button>
        </form>
        <p style="margin-top:15px; font-size:0.9rem; text-align:center; color:var(--text-muted);">
            Existing profile detected? <a href="login.php" style="color:var(--emerald-alpha);">Access System Here</a>
        </p>
    </div>
</body>
</html>