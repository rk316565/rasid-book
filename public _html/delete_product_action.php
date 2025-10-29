<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Product.php';
protect_page();
require_store_context();

if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    if ($product_id > 0) {
        $productModel = new Product($pdo);
        $productModel->delete($product_id, $_SESSION['current_store_id']);
        
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'प्रोडक्ट सफलतापूर्वक डिलीट हो गया।'];
    }
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/products');
exit();