<?php
// public/admin/dashboard.php
session_start();

// Standard validation check - bypass or modify if needed during your development phase
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // If you are using session bypass for local development, you can comment this block out
    // header("Location: ../login.php?error=unauthorized");
    // exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = DatabaseConnection::getMySQL();

// 1. Fetch Total Registered Customers Count
$customer_query = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$registered_customers = $customer_query ? $customer_query->fetchColumn() : 0;
// Fallback if no specific customer role rows exist yet
if ($registered_customers == 0) {
    $customer_query_alt = $db->query("SELECT COUNT(*) FROM users");
    $registered_customers = $customer_query_alt ? $customer_query_alt->fetchColumn() : 1;
}

// 2. Fetch Active Catalog SKUs
$sku_query = $db->query("SELECT COUNT(*) FROM products");
$catalog_skus = $sku_query ? $sku_query->fetchColumn() : 0;

// 3. Fetch Total Transaction Records from your warehouse pipeline mapping
$order_count_query = $db->query("SELECT COUNT(*) FROM orders");
$total_transactions = $order_count_query ? $order_count_query->fetchColumn() : 0;

// 4. Aggregate Gross Valuation Financial Metrics
$volume_query = $db->query("SELECT SUM(total_amount) FROM orders");
$gross_volume = $volume_query ? $volume_query->fetchColumn() : 0.00;
if (!$gross_volume) { $gross_volume = 0.00; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central Operations Center</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            display: block; 
            padding: 60px; 
            background-image: radial-gradient(circle at 50% 30%, #101f35 0%, #050b14 70%, #010306 100%); 
            background-attachment: fixed; 
            color: #ffffff; 
            font-family: 'Space Grotesk', sans-serif; 
            margin: 0;
        }
        .header-section {
            max-width: 1200px;
            margin: 0 auto 50px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 30px;
        }
        .header-titles h1 { font-size: 2.2rem; font-weight: 300; text-transform: uppercase; letter-spacing: 0.05em; margin: 0; }
        .header-titles p { color: #7f92a3; margin: 8px 0 0 0; font-size: 1rem; }
        
        .terminate-btn {
            background: transparent; border: 1px solid #ef4444; color: #ef4444; padding: 12px 24px;
            border-radius: 30px; cursor: pointer; font-family: 'Space Grotesk', sans-serif; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.2s; text-decoration: none; font-size: 0.85rem;
        }
        .terminate-btn:hover { background: #ef4444; color: #ffffff; box-shadow: 0 0 15px rgba(239, 68, 68, 0.4); }

        .matrix-grid {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;
        }
        .metric-card {
            background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px; padding: 30px 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .metric-label { color: #7f92a3; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 15px; font-weight: 700; }
        .metric-value { font-size: 2.5rem; font-weight: 300; color: #10b981; }

        .directive-section { max-width: 1200px; margin: 50px auto 0 auto; }
        .directive-section h2 { text-transform: uppercase; font-weight: 300; font-size: 1.5rem; letter-spacing: 0.05em; margin-bottom: 25px; }
        .btn-group { display: flex; gap: 20px; }
        
        .nav-btn {
            padding: 16px 32px; border-radius: 30px; font-family: 'Space Grotesk', sans-serif; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none; font-size: 0.9rem; transition: all 0.2s;
        }
        .btn-primary { background: #10b981; color: #ffffff; border: none; }
        .btn-primary:hover { background: #059669; box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
        .btn-secondary { background: transparent; color: #ffffff; border: 1px solid rgba(255,255,255,0.1); }
        .btn-secondary:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.3); }
    </style>
</head>
<body>

    <div class="header-section">
        <div class="header-titles">
            <h1>Central Operations Center</h1>
            <p>Welcome back, Admin Manan Verma</p>
        </div>
        <a href="../logout.php" class="terminate-btn">Terminate Session</a>
    </div>

    <div class="matrix-grid">
        <div class="metric-card">
            <div class="metric-label">Registered Customers</div>
            <div class="metric-value"><?php echo number_format($registered_customers); ?></div>
        </div>
        
        <div class="metric-card">
            <div class="metric-label">Active Catalog SKUs</div>
            <div class="metric-value"><?php echo number_format($catalog_skus); ?></div>
        </div>
        
        <div class="metric-card">
            <div class="metric-label">Processed Transactions</div>
            <div class="metric-value"><?php echo number_format($total_transactions); ?></div>
        </div>
        
        <div class="metric-card">
            <div class="metric-label">Gross Operational Volume</div>
            <div class="metric-value">$<?php echo number_format($gross_volume, 2); ?></div>
        </div>
    </div>

    <div class="directive-section">
        <h2>System Directives</h2>
        <div class="btn-group">
            <a href="products.php" class="nav-btn btn-primary">Manage Product Catalog Matrix</a>
            <a href="../index.php" class="nav-btn btn-secondary">View Live Client Interface</a>
        </div>
    </div>

</body>
</html>