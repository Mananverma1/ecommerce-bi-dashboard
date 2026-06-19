<?php
// public/index.php
session_start();
require_once __DIR__ . '/../config/database.php';
$db = DatabaseConnection::getMySQL();

$products = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quantum Catalog Storefront</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .hero-banner { background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.08), transparent), var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 50px; margin-bottom: 40px; }
        .hero-banner h2 { font-size: 2.2rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 10px; }
        .catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
        .product-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .product-card:hover { transform: translateY(-4px); border-color: #384f73; box-shadow: 0 12px 30px rgba(0,0,0,0.3); }
        .card-img-container { position: relative; width: 100%; height: 220px; background: #0b0f19; overflow: hidden; }
        .card-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
        .product-card:hover .card-img { transform: scale(1.04); }
        .card-body { padding: 24px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .card-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .card-category { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }
        .card-title { font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 8px; line-height: 1.3; }
        .card-desc { font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 20px; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(34, 47, 71, 0.5); padding-top: 16px; }
        .card-price { font-size: 1.4rem; font-weight: 800; color: var(--text-main); }
        .btn-action { background: transparent; border: 1px solid var(--border-color); color: var(--text-main); font-weight: 600; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; transition: all 0.2s ease; }
        .btn-action:hover { border-color: var(--emerald-alpha); color: var(--emerald-alpha); background: rgba(16, 185, 129, 0.04); }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <div class="brand">NEXUS<span>MATRIX</span></div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item" style="color: #fff;">Storefront</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php" class="nav-item">Cart Container</a>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="nav-item" style="color: var(--emerald-alpha);">[Admin Console]</a>
                    <?php endif; ?>
                    <span class="nav-item" style="color: var(--text-main);">// Operator: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="logout.php" class="nav-item" style="color: var(--danger-color);">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-item">Sign In</a>
                    <a href="register.php" class="btn-primary" style="padding: 10px 18px; font-size: 0.85rem;">Initialize Profile</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="wrapper">
        <div class="hero-banner">
            <h2>Computational Catalog Matrix</h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 600px;">Provision hardware allocations and browse operational logistics deployment files smoothly.</p>
        </div>

        <main>
            <div class="catalog-grid">
                <?php if (empty($products)): ?>
                    <div style="grid-column:1/-1; text-align:center; padding:80px; color:var(--text-muted); background:var(--bg-card); border:1px dashed var(--border-color); border-radius:12px;">
                        <h3>No Core Asset Matrix Detected</h3>
                        <p style="margin-top:10px; font-size:0.95rem;">Please populate database instances inside the operational admin console dashboard.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($products as $prod): ?>
                    <div class="product-card">
                        <div class="card-img-container">
                            <img src="/ecommerce-bi/public/assets/uploads/<?php echo htmlspecialchars($prod['image_path']); ?>" 
     class="card-img" 
     onerror="this.onerror=null; this.src='/ecommerce-bi/public/assets/cssuploads/default.png';">
                        </div>
                        <div class="card-body">
                            <div>
                                <div class="card-meta">
                                    <span class="card-category"><?php echo htmlspecialchars($prod['category_name']); ?></span>
                                    <span class="stock-badge <?php echo $prod['stock'] > 0 ? 'instock' : 'outstock'; ?>">
                                        <?php echo $prod['stock'] > 0 ? $prod['stock'] . ' Units' : 'Depleted'; ?>
                                    </span>
                                </div>
                                <div class="card-title"><?php echo htmlspecialchars($prod['name']); ?></div>
                                <div class="card-desc"><?php echo htmlspecialchars($prod['description']); ?></div>
                            </div>
                            <div class="card-footer">
                                <div class="card-price">$<?php echo number_format($prod['price'], 2); ?></div>
                                <form action="cart-action.php?action=add" method="POST" style="margin:0;">
                                    <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                    <button type="submit" class="btn-action" <?php echo $prod['stock'] <= 0 ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''; ?>>
                                        Allocate Asset
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>