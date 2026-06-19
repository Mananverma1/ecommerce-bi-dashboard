<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Security Access Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>System Access Portal</h2>

        <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
            <div class="badge success" style="display:block; margin-bottom:15px; padding:10px;">
                Registry verification successful! Authenticate credentials below.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="badge danger" style="display:block; margin-bottom:15px; padding:10px;">
                <?php
                    if ($_GET['error'] === 'invalid_credentials') echo "Authentication Refused: Incorrect profile token configurations.";
                    if ($_GET['error'] === 'empty_fields') echo "Validation Error: Complete all credential fields.";
                ?>
            </div>
        <?php endif; ?>

        <form action="../src/auth-handler.php?action=login" method="POST">
            <label for="email">Email Anchor Address</label>
            <input type="email" id="email" name="email" placeholder="user@domain.com" required>

            <label for="password">Password Component</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>

            <button type="submit">Verify Credentials & Execute Session</button>
        </form>
        <p style="margin-top:15px; font-size:0.9rem; text-align:center; color:var(--text-muted);">
            New operative segment? <a href="register.php" style="color:var(--emerald-alpha);">Register Profile Matrix</a>
        </p>
    </div>
</body>
</html>