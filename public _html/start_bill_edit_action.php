<?php
// public_html/start_bill_edit_action.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Bill.php';
require_once __DIR__ . '/src/models/Product.php';
require_once __DIR__ . '/src/models/Customer.php';

protect_page();
require_store_context();

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

// 5. बिलिंग पेज के लिए डेटा तैयार करें
$items_for_billing = [];
if (!empty($bill_items)) {
    foreach ($bill_items as $item) {
        $product_details = $productModel->findById($item['product_id'], $store_id);
        if ($product_details) {
            $items_for_billing[] = [ 'id' => (int)$product_details['id'], 'name' => $product_details['name'], 'price' => (float)$item['price'], 'originalPrice' => (float)$item['original_price'], 'appliedDiscount' => ($item['original_price'] > $item['price']) ? 'manual' : 'none', 'quantity' => (int)$item['quantity'], 'costPrice' => (float)$product_details['costPrice'] ];
        }
    }
}

// 6. पुराने बिल को डिलीट कर दें
$billModel->delete($bill_id, $store_id);

// ======================================================
//       यह है महत्वपूर्ण बदलाव: पेमेंट जानकारी को सेव करें
// ======================================================
// 7. सेशन में बिलिंग का डेटा डालें
$_SESSION['billing_state'] = [
    'items' => $items_for_billing,
    'customer_id' => $bill['customer_id'],
    'payment_details' => [
        'status' => $bill['status'],
        'amount_paid' => $bill['amount_paid']
    ]
];

// 8. बिलिंग पेज पर भेजें
header('Location: ' . BASE_URL . '/store/' . $store_id . '/billing');
exit();