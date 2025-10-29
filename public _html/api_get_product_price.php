<?php
// public_html/api_get_product_price.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Product.php';

protect_page();
require_store_context();
header('Content-Type: application/json');

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$quantity = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;

$response = ['effectivePrice' => 0, 'error' => 'Invalid parameters.'];

if ($product_id > 0 && $quantity > 0) {
    try {
        $productModel = new Product($pdo);
        // प्रोडक्ट की पूरी जानकारी प्राप्त करें
        $product = $productModel->findById($product_id, $_SESSION['current_store_id']);
        
        if ($product) {
            // प्रभावी कीमत की गणना करें
            $priceData = $productModel->getEffectivePrice($product, $quantity);
            $response = [
                'effectivePrice' => $priceData['effectivePrice'],
                'appliedDiscount' => $priceData['appliedDiscount'],
                'originalPrice' => (float)$product['price']
            ];
        } else {
            $response['error'] = 'Product not found.';
        }
    } catch (Exception $e) {
        $response['error'] = 'Server error.';
    }
}

echo json_encode($response);
exit();