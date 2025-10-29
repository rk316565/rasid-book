<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Product.php';
protect_page();
require_store_context();

header('Content-Type: application/json');

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id > 0) {
    $productModel = new Product($pdo);
    $product = $productModel->findById($product_id, $_SESSION['current_store_id']);
    if ($product) { echo json_encode($product); } 
    else { echo json_encode(['error' => 'Product not found.']); }
} else { echo json_encode(['error' => 'No product ID.']); }
exit();