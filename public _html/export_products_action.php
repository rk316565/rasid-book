<?php
// public_html/export_products_action.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Product.php';

protect_page();
require_store_context();
require_owner(); // सिर्फ मालिक ही एक्सपोर्ट कर सकता है

$productModel = new Product($pdo);
$products = $productModel->getAllByStoreId($_SESSION['current_store_id']);

if (empty($products)) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'एक्सपोर्ट करने के लिए कोई प्रोडक्ट नहीं है।'];
    header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/products');
    exit();
}

$filename = "products_export_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// CSV का हेडर (कॉलम के नाम)
fputcsv($output, [
    'Name', 'CostPrice', 'Price', 'StoreStock', 'GodownStock', 'DiscountType', 'DiscountValue', 
    'BulkDiscountMinQuantity', 'BulkDiscountType', 'BulkDiscountValue', 'LowStockThreshold'
]);

// हर प्रोडक्ट का डेटा CSV में लिखें
foreach ($products as $product) {
    fputcsv($output, [
        $product['name'], $product['costPrice'], $product['price'], $product['stock'], $product['godownStock'],
        $product['discountType'], $product['discountValue'], $product['bulkDiscountMinQuantity'],
        $product['bulkDiscountType'], $product['bulkDiscountValue'], $product['lowStockThreshold']
    ]);
}

fclose($output);
exit();