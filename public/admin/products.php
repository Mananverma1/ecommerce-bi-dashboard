<?php
// public/admin/products.php
session_start();

// Standard validation check - bypass or modify if needed during your development phase
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // If you are using session bypass for local development, you can comment this block out
    // header("Location: ../login.php?error=unauthorized");
    // exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = DatabaseConnection::getMySQL();

// Gather dependencies using raw fetch sequences
$categories = $db->query("SELECT * FROM categories")->fetchAll();
$products = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog Inventory Manager</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { display: block; padding: 40px; background-image: radial-gradient(circle at 50% 30%, #1a365d 0%, #070f1e 70%, #02060d 100%); background-attachment: fixed; color: #ffffff; font-family: 'Space Grotesk', sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 2fr; gap: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; overflow: hidden; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-size: 0.9rem; }
        th { background-color: #0d1b2a; color: #7f92a3; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        .product-img { width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid rgba(255, 255, 255, 0.1); }
        .auth-container { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; }
        input, select { background: #070f1e !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; padding: 12px; border-radius: 6px; width: 100%; box-sizing: border-box; margin-bottom: 20px; font-family: 'Space Grotesk', sans-serif; }
        button[type="submit"] { width: 100%; background: #00a3ff; color: #ffffff; border: none; padding: 14px; font-weight: 700; border-radius: 30px; cursor: pointer; text-transform: uppercase; letter-spacing: 0.05em; transition: background 0.2s; }
        button[type="submit"]:hover { background: #0082cc; }
        h1, h2, h3 { text-transform: uppercase; font-weight: 300; letter-spacing: 0.05em; }
    </style>
</head>
<body>
    <div style="max-width:1200px; margin:0 auto 20px auto; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px;">
        <a href="dashboard.php" style="color: #00a3ff; text-decoration:none; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">← Return to Master Dashboard</a>
        <h1 style="margin: 0; font-size: 1.8rem;">Catalog Management Terminal</h1>
    </div>

    <div class="container">
        <div class="auth-container" style="padding: 25px; height: fit-content;">
            <h3>Append New SKU Record</h3>
            <form action="../../src/product-handler.php?action=create" method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                <label style="color: #7f92a3; font-size: 0.85rem; text-transform: uppercase;">Target Category</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label style="color: #7f92a3; font-size: 0.85rem; text-transform: uppercase;">Item Nominal Identifier (Name)</label>
                <input type="text" name="name" placeholder="Supercomputing Node" required>

                <label style="color: #7f92a3; font-size: 0.85rem; text-transform: uppercase;">Detailed Specifications (Description)</label>
                <input type="text" name="description" placeholder="High throughput execution architecture cluster..." required>

                <label style="color: #7f92a3; font-size: 0.85rem; text-transform: uppercase;">Financial Asset Valuation (Price USD)</label>
                <input type="number" step="0.01" name="price" placeholder="299.99" required>

                <label style="color: #7f92a3; font-size: 0.85rem; text-transform: uppercase;">Available Structural Units (Stock)</label>
                <input type="number" name="stock" placeholder="50" required>

                <label style="color: #7f92a3; font-size: 0.85rem; text-transform: uppercase;">Product Imagery Matrix (Image File)</label>
                <input type="file" name="image" accept="image/*" style="border:none !important; padding:0 !important; margin-bottom:25px; background:transparent !important;">

                <button type="submit">Commit Inventory Node</button>
            </form>
        </div>

        <div>
            <h3>Active Catalog Repository Matrix</h3>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Identifier</th>
                        <th>Classification</th>
                        <th>Valuation</th>
                        <th>Units Left</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="5" style="text-align:center; color:#7f92a3;">No inventory assets currently registered in database system.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td><img src="/ecommerce-bi/public/assets/uploads/<?php echo htmlspecialchars($prod['image_path']); ?>" 
                                     class="product-img" 
                                     onerror="this.onerror=null; this.src='/ecommerce-bi/public/assets/css/uploads/default.png';"></td>
                            <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                            <td><span style="color:#7f92a3;"><?php echo htmlspecialchars($prod['category_name']); ?></span></td>
                            <td style="color: #00a3ff; font-weight:600;">$<?php echo number_format($prod['price'], 2); ?></td>
                            <td><?php echo $prod['stock']; ?> units</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bi-dashboard-wrapper" style="margin-top: 60px; max-width: 1200px; margin-left: auto; margin-right: auto;">
        <div class="bi-header" style="margin-bottom: 20px; border-left: 4px solid #00a3ff; padding-left: 15px;">
            <h2 style="color: #ffffff; font-size: 1.5rem; font-weight: 700; margin: 0;">Enterprise BI Analytics Engine</h2>
            <p style="color: #7f92a3; margin: 5px 0 0 0; font-size: 0.9rem;">Real-time Online Analytical Processing (OLAP) synchronization stream from MSSQL Data Warehouse.</p>
        </div>

        <div class="iframe-container" style="width: 100%; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); background-color: #040a12;">
            <iframe 
                src="http://127.0.0.1:8050/" 
                style="width: 100%; height: 650px; border: none; display: block;"
                scrolling="no"
                loading="lazy">
            </iframe>
        </div>
    </div>
</body>
</html>