<?php
require_once __DIR__ . '/config/config.php'; require_once __DIR__ . '/src/models/Product.php';
protect_page(); require_store_context();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['productId'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);

    if ($product_id > 0 && $quantity > 0) {
        $productModel = new Product($pdo);
        $success = $productModel->transferStockFromGodown($product_id, $_SESSION['current_store_id'], $quantity);
        if ($success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => $quantity . ' आइटम सफलतापूर्वक ट्रांसफर हो गए।'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ट्रांसफर विफल रहा (अपर्याप्त गोदाम स्टॉक)।'];
        }
    }
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/inventory');
exit();