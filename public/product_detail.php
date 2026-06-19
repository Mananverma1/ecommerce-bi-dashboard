<?php
// public/product_detail.php
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$conn = new mysqli('localhost', 'root', '', 'ecommerce_oltp');
if ($conn->connect_error) { die("Database offline"); }

$stmt = $conn->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$product = $stmt->get_result()->fetch_assoc() ?? ['name' => 'Model Selection Error', 'price' => 0.00];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at 80% 20%, #12253d 0%, #040a12 60%, #010306 100%);
            color: #ffffff; font-family: 'Space Grotesk', sans-serif; margin: 0; padding: 60px; height: 100vh; box-sizing: border-box;
        }
        .back-btn { color: #7f92a3; text-decoration: none; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.1em; }
        .layout { display: flex; align-items: center; justify-content: space-between; height: 80%; margin-top: 40px; }
        .info-panel { max-width: 500px; }
        .meta { color: #00a3ff; font-size: 0.85rem; letter-spacing: 0.2em; text-transform: uppercase; margin-bottom: 15px; }
        h1 { font-size: 4rem; font-weight: 300; text-transform: uppercase; line-height: 1.05; margin: 0 0 30px 0; }
        .action-btn {
            background: #00a3ff; color: #ffffff; border: none; padding: 16px 40px; font-family: 'Space Grotesk', sans-serif;
            font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 30px; cursor: pointer; transition: background 0.2s;
        }
        .action-btn:hover { background: #0082cc; }
        .visuals-panel { text-align: center; position: relative; width: 500px; height: 500px; }
        .outer-ring {
            width: 100%; height: 100%; border: 1px dashed rgba(255,255,255,0.05); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; animation: rotate 40s linear infinite;
        }
        .inner-display {
            position: absolute; font-size: 6rem; font-weight: 700; color: rgba(255,255,255,0.02);
            text-transform: uppercase; pointer-events: none; text-align: center; line-height: 1;
        }
        @keyframes rotate { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <a href="index.php" class="back-btn">← Return to Collection</a>
    
    <div class="layout">
        <div class="info-panel">
            <div class="meta"><?php echo htmlspecialchars($product['cat_name'] ?? 'Premium Asset Portfolio'); ?> // CH-<?php echo $id; ?>00</div>
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p style="color: #7f92a3; font-weight: 300; line-height: 1.6; margin-bottom: 40px;">
                Engineered with high-precision mechanics and real-time database indexing. Synchronized perfectly into structural data pipelines.
            </p>

            <!-- Success notification box -->
            <div style="margin-bottom: 20px;">
                <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                    <p style="color: #10b981; font-weight: bold; background: rgba(16,185,129,0.1); padding: 12px; border-radius: 8px; border: 1px solid rgba(16,185,129,0.3); margin: 0;">
                        ✓ Asset Allocated Successfully! Run your Python ETL script to update BI charts.
                    </p>
                <?php endif; ?>
            </div>

            <!-- Interactive POST execution form -->
            <form action="checkout.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                <button type="submit" class="action-btn">
                    Acquire For $<?php echo number_format($product['price'], 2); ?>
                </button>
            </form>
        </div>

        <div class="visuals-panel">
            <div class="outer-ring">
                <div style="width: 70%; height: 70%; border: 1px solid rgba(0,163,255,0.1); border-radius: 50%;"></div>
            </div>
            <div class="inner-display">
                NEXUS<br><span style="color: #00a3ff; opacity: 0.2;">$<?php echo intval($product['price']); ?></span>
            </div>
        </div>
    </div>
</body>
</html>
<?php $stmt->close(); $conn->close(); ?>