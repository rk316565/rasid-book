<?php
// public_html/delete_bill_action.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Bill.php';
require_once __DIR__ . '/src/models/Product.php';
require_once __DIR__ . '/src/models/Customer.php';

protect_page();
require_store_context();
// सिर्फ मालिक ही बिल डिलीट कर सकता है
require_owner();

$bill_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($bill_id <= 0) {
    header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/bills');
    exit();
}

$billModel = new Bill($pdo);
$productModel = new Product($pdo);
$customerModel = new Customer($pdo);
$store_id = $_SESSION['current_store_id'];

// 1. बिल और उसके आइटम्स प्राप्त करें
$bill = $billModel->findById($bill_id, $store_id);
$bill_items = $billModel->getItemsByBillId($bill_id);

if (!$bill) {
    header('Location: ' . BASE_URL . '/store/' . $store_id . '/bills');
    exit();
}

// 2. स्टॉक को वापस बढ़ाएं
if (!empty($bill_items)) {
    $productModel->increaseStockForMultiple($store_id, $bill_items);
}

// 3. ग्राहक की उधारी को वापस कम करें (अगर थी)
if ($bill['due_amount'] > 0 && $bill['customer_id']) {
    $customerModel->updateDue($bill['customer_id'], $store_id, -$bill['due_amount']);
}

// 4. ग्राहक के लॉयल्टी पॉइंट्स को भी रिवर्स करें (अगर थे)
if ($bill['customer_id']) {
    $points_change_to_reverse = (int)$bill['points_redeemed'] - (int)$bill['points_earned'];
    if ($points_change_to_reverse != 0) {
        $customerModel->updateLoyaltyPoints($bill['customer_id'], $store_id, $points_change_to_reverse);
    }
}

// 5. अब बिल को डिलीट कर दें
$billModel->delete($bill_id, $store_id);

// 6. फ्लैश मैसेज सेट करें और वापस भेजें
$_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Bill #' . $bill_id . ' सफलतापूर्वक डिलीट हो गया।'];
header('Location: ' . BASE_URL . '/store/' . $store_id . '/bills');
exit();