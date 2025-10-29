<?php
require_once __DIR__ . '/config/config.php'; require_once __DIR__ . '/src/models/Product.php';
protect_page(); require_store_context();
header('Content-Type: application/json');
$term = $_GET['term'] ?? '';
$productModel = new Product($pdo);
$products = $productModel->searchByName($_SESSION['current_store_id'], $term);
echo json_encode($products);