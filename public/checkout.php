<?php
// public/checkout.php

// Force error output if any column mapping names fail
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    die("Access Denied: Unauthenticated Operations Request.");
}

require_once __DIR__ . '/../config/database.php';
$db = DatabaseConnection::getMySQL();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch current live contents from user's database table cart line entries
    $cart_query = "SELECT c.quantity, p.id as product_id, p.price 
                   FROM cart c 
                   JOIN products p ON c.product_id = p.id 
                   WHERE c.user_id = ?";
    $stmt = $db->prepare($cart_query);
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        die("Procurement Cancelled: Your database shopping basket contains 0 elements.");
    }

    // 2. Open atomic transactional block boundary
    $db->beginTransaction();

    // Compute exact database aggregate total valuation matrix
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += ($item['price'] * $item['quantity']);
    }

    // 3. Inject new master entry row down to orders log table
    $order_sql = "INSERT INTO orders (user_id, total_amount, status, created_at, updated_at) 
                  VALUES (:user_id, :total_amount, 'success', NOW(), NOW())";
    $order_stmt = $db->prepare($order_sql);
    $order_stmt->execute([
        ':user_id'      => $user_id,
        ':total_amount' => $total_amount
    ]);

    // Track the generated transaction tracking index sequence key
    $order_id = $db->lastInsertId();

    // 4. Prepare batch insertion script context mapping to order_items layout block
    $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase, price) 
                 VALUES (:order_id, :product_id, :quantity, :price_at_purchase, :price)";
    $item_stmt = $db->prepare($item_sql);

    foreach ($cart_items as $item) {
        $item_stmt->execute([
            ':order_id'          => $order_id,
            ':product_id'        => $item['product_id'],
            ':quantity'          => $item['quantity'],
            ':price_at_purchase' => $item['price'],
            ':price'             => $item['price']
        ]);
    }

    // 5. Success: Clear the staging cart table rows for this specific client user
    $clear_sql = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = $db->prepare($clear_sql);
    $clear_stmt->execute([$user_id]);

    // 6. Finalize transactional state commit down to drive engine
    $db->commit();

    // Route back to operational dashboard matrix display context loop
    header("Location: admin/dashboard.php?checkout=success");
    exit;

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    echo "<div style='background:#111; color:#ff4444; padding:30px; font-family:monospace; margin:40px; border:1px solid #ff4444;'>";
    echo "<h3>System Ledger Database Exception Caught</h3>";
    echo "<strong>Error Details:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "<strong>Line Code Origin:</strong> " . $e->getLine();
    echo "</div>";
    exit;
}