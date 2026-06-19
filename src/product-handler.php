<?php
// src/product-handler.php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied.");
}

require_once __DIR__ . '/../config/database.php';
$db = DatabaseConnection::getMySQL();

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $category_id = intval($_POST['category_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image_name = 'default.png';

    // File Upload Handler matching core framework requirements
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            // Obfuscate image name securely to avoid directory traversal risks
            $image_name = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../public/assets/uploads/';
            
            // Create folder dynamically if missing
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            move_uploaded_file($fileTmpPath, $uploadFileDir . $image_name);
        }
    }

    // Mutate state in DB via prepared statements
    $stmt = $db->prepare("INSERT INTO products (category_id, name, description, price, stock, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$category_id, $name, $description, $price, $stock, $image_name]);

    header("Location: ../public/admin/products.php?status=inserted");
    exit;
}