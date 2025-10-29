<?php
// public_html/save_bill_action.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Bill.php';
require_once __DIR__ . '/src/models/Product.php';
require_once __DIR__ . '/src/models/Loyalty.php';
require_once __DIR__ . '/src/models/Customer.php';

protect_page();
require_store_context();
header('Content-Type: application/json');

// जावास्क्रिप्ट से भेजे गए JSON डेटा को प्राप्त करें
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// डेटा को अलग-अलग वेरिएबल्स में डालें
$customer_id = $data['customerId'] ?? null;
$items = $data['items'] ?? [];
$totals = $data['totals'] ?? [];
$paymentDetails = $data['paymentDetails'] ?? [];
$loyaltyData = $data['loyaltyData'] ?? [];

// अगर बिल में कोई आइटम नहीं है, तो रोक दें
if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Bill is empty.']);
    exit();
}

// --- लाभ की गणना ---
try {
    $productModel = new Product($pdo);
    
    // 1. बिल के सभी प्रोडक्ट्स की IDs की एक लिस्ट बनाएं
    $product_ids = array_map(function($item) { return $item['id']; }, $items);

    // 2. एक ही बार में सभी प्रोडक्ट्स का costPrice प्राप्त करें
    $productsCostPrices = $productModel->getMultipleByIds($_SESSION['current_store_id'], $product_ids);
    
    // 3. कुल खरीद मूल्य की गणना करें
    $totalCostPrice = 0;
    foreach ($items as $item) {
        $costPrice = $productsCostPrices[$item['id']] ?? 0;
        $totalCostPrice += $costPrice * $item['quantity'];
    }

    // 4. लाभ की गणना फाइनल बिल टोटल से करें
    $finalBillTotal = (float)($totals['total'] ?? 0);
    $totalProfit = $finalBillTotal - $totalCostPrice;

    // 5. कुल लाभ को $totals ऐरे में अपडेट करें
    $totals['profit'] = $totalProfit;

} catch (Exception $e) {
    error_log("Profit calculation failed: " . $e->getMessage());
    $totals['profit'] = 0; // कोई त्रुटि होने पर लाभ को 0 पर सेट करें
}

// --- पॉइंट्स की गणना ---
$loyaltyModel = new Loyalty($pdo);
$settings = $loyaltyModel->getSettings($_SESSION['current_store_id']);
$points_earned = 0;
if ($settings['points_per_amount'] > 0 && $totals['total'] > 0) {
    // कुल राशि के आधार पर पॉइंट्स की गणना करें
    $points_earned = floor($totals['total'] / $settings['points_per_amount']) * $settings['points_awarded'];
}

// --- बिल डेटा में लॉयल्टी की जानकारी जोड़ें (कूपन के बिना) ---
$totals['points_earned'] = $points_earned;
$totals['points_redeemed'] = $loyaltyData['pointsRedeemed'] ?? 0;
// कूपन की लाइनें हटा दी गई हैं
// $totals['coupon_code'] = null;
// $totals['coupon_discount'] = 0;

// --- अब बिल को डेटाबेस में सेव करें ---
$billModel = new Bill($pdo);
$newBillId = $billModel->create(
    $_SESSION['current_store_id'], 
    ($_SESSION['user_type'] === 'owner') ? $_SESSION['user_id'] : $_SESSION['owner_user_id'], 
    $customer_id, 
    $items, 
    $totals, 
    $paymentDetails
);

// --- अगर बिल सफलतापूर्वक बन गया है, तो ग्राहक के पॉइंट्स अपडेट करें ---
if ($newBillId) {
    if ($customer_id) {
        $customerModel = new Customer($pdo);
        // कमाए गए पॉइंट्स जोड़ें और खर्च किए गए पॉइंट्स घटाएं
        $points_change = $points_earned - ($loyaltyData['pointsRedeemed'] ?? 0);
        
        if ($points_change != 0) {
            $customerModel->updateLoyaltyPoints($customer_id, $_SESSION['current_store_id'], $points_change);
        }
    }
    
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Bill #' . $newBillId . ' successfully created.'];
    echo json_encode(['success' => true, 'billId' => $newBillId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save the bill. A server error occurred.']);
}