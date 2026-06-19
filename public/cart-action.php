<?php
// public/cart-action.php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=authentication_required");
    exit;
}

$db = DatabaseConnection::getMySQL();
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    
    // Upsert transaction block logic to safely evaluate scaling units
    $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1) 
                          ON DUPLICATE KEY UPDATE quantity = quantity + 1");
    $stmt->execute([$user_id, $product_id]);

    header("Location: index.php?allocation=success");
    exit;
}

if ($action === 'remove') {
    $cart_id = intval($_GET['id']);
    $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);

    header("Location: cart.php");
    exit;
}