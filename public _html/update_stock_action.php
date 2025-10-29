<?php
require_once __DIR__ . '/config/config.php'; require_once __DIR__ . '/src/models/Product.php';
protect_page(); require_store_context();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['productId'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $type = $_POST['type'] ?? '';
    $location = $_POST['location'] ?? 'store';

    if ($product_id > 0 && $quantity >= 0 && !empty($type)) {
        $productModel = new Product($pdo);
        $productModel->updateStock($product_id, $_SESSION['current_store_id'], $quantity, $type, $location);
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'स्टॉक सफलतापूर्वक अपडेट हो गया।'];
    }
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/inventory');
exit();