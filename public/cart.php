<?php
// public/cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
$db = DatabaseConnection::getMySQL();
$user_id = $_SESSION['user_id'];

// Multi-table query to pull user cart items with current prices
$query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image_path 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

$runningTotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staged Procurement Container</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .cart-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-top: 20px; }
        .cart-table { width: 100%; border-collapse: collapse; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
        th, td { padding: 18px 24px; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { background: #131a2c; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); }
        .summary-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 30px; height: fit-content; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 1.05rem; }
        .btn-danger { color: var(--danger-color); text-decoration: none; font-size: 0.85rem; font-weight: 600; }
        .btn-danger:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <div class="brand">NEXUS<span>MATRIX</span></div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item">Storefront</a>
                <a href="cart.php" class="nav-item" style="color: #fff;">Cart Container</a>
                <a href="logout.php" class="nav-item" style="color: var(--danger-color);">Logout</a>
            </nav>
        </div>
    </header>

    <div class="wrapper">
        <h2>Allocated Allocation Logs</h2>
        
        <div class="cart-layout">
            <div>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Structural Asset</th>
                            <th>Valuation</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding: 50px;">Your staged allocation matrix is completely empty.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($items as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $runningTotal += $subtotal;
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td style="color:var(--emerald-alpha); font-weight:600;">$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?> units</td>
                                <td><a href="cart-action.php?action=remove&id=<?php echo $item['cart_id']; ?>" class="btn-danger">Purge Element</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary-card">
                <h3>Financial Summary</h3>
                <div style="margin-top: 25px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">
                    <div class="summary-row">
                        <span style="color: var(--text-muted);">Aggregated Cost</span>
                        <strong>$<?php echo number_format($runningTotal, 2); ?></strong>
                    </div>
                </div>
                
                <form action="checkout.php" method="POST">
                    <button type="submit" style="width: 100%; background: #00a3ff; color: #ffffff; border: none; padding: 16px; font-family: 'Inter', sans-serif; font-size: 1rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 8px; cursor: pointer; transition: background 0.2s;">
                        Execute Procurement Protocol
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>