<?php
// public_html/edit_product_action.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Product.php';

protect_page();
require_store_context();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['editProductId'] ?? 0);
    $data = [
        'name' => trim($_POST['editProductName'] ?? ''),
        'costPrice' => (float)($_POST['editProductCostPrice'] ?? 0),
        'price' => (float)($_POST['editProductPrice'] ?? 0),
        'stock' => (int)($_POST['editProductStock'] ?? 0),
        'godownStock' => (int)($_POST['editGodownStock'] ?? 0),
        'discountType' => trim($_POST['editDiscountType'] ?? 'none'),
        'discountValue' => !empty($_POST['editDiscountValue']) ? (float)$_POST['editDiscountValue'] : null,
        'bulkDiscountMinQuantity' => !empty($_POST['editBulkMinQuantity']) ? (int)$_POST['editBulkMinQuantity'] : null,
        'bulkDiscountType' => trim($_POST['editBulkDiscountType'] ?? 'none'),
        'bulkDiscountValue' => !empty($_POST['editBulkDiscountValue']) ? (float)$_POST['editBulkDiscountValue'] : null,
        'lowStockThreshold' => !empty($_POST['editLowStockThreshold']) ? (int)$_POST['editLowStockThreshold'] : 0
    ];
    
    // ======================================================
    //       यह है महत्वपूर्ण बदलाव: ट्रांसलिटरेशन
    // ======================================================
    $data['name_en'] = transliterate_hindi_to_english($data['name']);

    if (!empty($data['name']) && $product_id > 0) {
        $productModel = new Product($pdo);
        // अब update फंक्शन को भी name_en को हैंडल करना होगा
        $productModel->update($product_id, $_SESSION['current_store_id'], $data);
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'प्रोडक्ट सफलतापूर्वक अपडेट हो गया।'];
    }
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/products');
exit();