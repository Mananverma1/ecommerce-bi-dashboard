<?php
// public/admin/products.php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?error=unauthorized");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = DatabaseConnection::getMySQL();

// Gather dependencies
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
    <style>
        body { display: block; padding: 40px; background-color: var(--bg-main); }
        .container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 2fr; gap: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { background-color: #1e293b; color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        .product-img { width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color); }
    </style>
</head>
<body>
    <div style="max-width:1200px; margin:0 auto 20px auto; display:flex; justify-content:space-between; align-items:center;">
        <a href="dashboard.php" style="color:var(--emerald-alpha); text-decoration:none;">← Return to Master Dashboard</a>
        <h1>Catalog Management Terminal</h1>
    </div>

    <div class="container">
        <div class="auth-container" style="max-width:100%; height: fit-content; padding: 25px;">
            <h3>Append New SKU Record</h3>
            <form action="../../src/product-handler.php?action=create" method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                <label>Target Category</label>
                <select name="category_id" style="background:var(--bg-main); color:var(--text-main); border:1px solid var(--border-color); padding:10px; border-radius:6px; margin-bottom:20px;" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Item Nominal Identifier (Name)</label>
                <input type="text" name="name" placeholder="Supercomputing Node" required>

                <label>Detailed Specifications (Description)</label>
                <input type="text" name="description" placeholder="High throughput execution architecture cluster..." required>

                <label>Financial Asset Valuation (Price USD)</label>
                <input type="number" step="0.01" name="price" placeholder="299.99" required>

                <label>Available Structural Units (Stock)</label>
                <input type="number" name="stock" placeholder="50" required>

                <label>Product Imagery Matrix (Image File)</label>
                <input type="file" name="image" accept="image/*" style="border:none; padding:0; margin-bottom:25px;">

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
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">No inventory assets currently registered in database system.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td><img src="../assets/uploads/<?php echo htmlspecialchars($prod['image_path']); ?>" class="product-img" onerror="this.src='../assets/uploads/default.png'"></td>
                            <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                            <td><span style="color:var(--text-muted);"><?php echo htmlspecialchars($prod['category_name']); ?></span></td>
                            <td style="color:var(--emerald-alpha); font-weight:600;">$<?php echo number_format($prod['price'], 2); ?></td>
                            <td><?php echo $prod['stock']; ?> units</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>