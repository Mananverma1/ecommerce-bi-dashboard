<?php
// public/admin/dashboard.php
session_start();

// Strict Role-Based Access Control (RBAC)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?error=unauthorized");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = DatabaseConnection::getMySQL();

// Fetch rapid store performance aggregates via PDO
$userCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$productCount = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orderCount = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $db->query("SELECT IFNULL(SUM(total_amount), 0.00) FROM orders WHERE status != 'cancelled'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Operations Control Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { display: block; padding: 40px; background-color: var(--bg-main); }
        .admin-layout { max-width: 1200px; margin: 0 auto; }
        .nav-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color); }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .metric-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 25px; text-align: center; }
        .metric-card h3 { font-size: 0.85rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 10px; }
        .metric-card p { font-size: 2rem; font-weight: bold; color: var(--emerald-alpha); }
        .quick-actions { display: flex; gap: 15px; }
        .btn-secondary { background: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 12px 20px; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: 600; }
        .btn-secondary:hover { background: var(--border-color); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <header class="nav-header">
            <div>
                <h1>Central Operations Center</h1>
                <p style="color: var(--text-muted);">Welcome back, Admin <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            </div>
            <a href="../logout.php" class="btn-secondary" style="border-color: var(--danger-color); color: #fca5a5;">Terminate Session</a>
        </header>

        <main>
            <section class="metrics-grid">
                <div class="metric-card">
                    <h3>Registered Customers</h3>
                    <p><?php echo $userCount; ?></p>
                </div>
                <div class="metric-card">
                    <h3>Active Catalog SKUs</h3>
                    <p><?php echo $productCount; ?></p>
                </div>
                <div class="metric-card">
                    <h3>Processed Transactions</h3>
                    <p><?php echo $orderCount; ?></p>
                </div>
                <div class="metric-card">
                    <h3>Gross Operational Volume</h3>
                    <p>$<?php echo number_format($totalRevenue, 2); ?></p>
                </div>
            </section>

            <h2>System Directives</h2>
            <div class="quick-actions" style="margin-top: 20px;">
                <a href="products.php" class="btn-secondary" style="background: var(--emerald-alpha); border: none; color: #fff;">Manage Product Catalog Matrix</a>
                <a href="../index.php" class="btn-secondary">View Live Client Interface</a>
            </div>
        </main>
    </div>
</body>
</html>