<?php
// public_html/add_product_action.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Product.php';

// सुरक्षा जांचें
protect_page();
require_store_context();

// जांचें कि क्या फॉर्म सबमिट किया गया है
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // फॉर्म से सभी डेटा प्राप्त करें
    $data = [
        'name' => trim($_POST['productName'] ?? ''),
        'costPrice' => !empty($_POST['productCostPrice']) ? (float)$_POST['productCostPrice'] : 0,
        'price' => !empty($_POST['productPrice']) ? (float)$_POST['productPrice'] : 0,
        'stock' => !empty($_POST['productInitialStock']) ? (int)$_POST['productInitialStock'] : 0,
        'godownStock' => !empty($_POST['productGodownStock']) ? (int)$_POST['productGodownStock'] : 0,
        'discountType' => trim($_POST['discountType'] ?? 'none'),
        'discountValue' => !empty($_POST['discountValue']) ? (float)$_POST['discountValue'] : null,
        'bulkDiscountMinQuantity' => !empty($_POST['bulkMinQuantity']) ? (int)$_POST['bulkMinQuantity'] : null,
        'bulkDiscountType' => trim($_POST['bulkDiscountType'] ?? 'none'),
        'bulkDiscountValue' => !empty($_POST['bulkDiscountValue']) ? (float)$_POST['bulkDiscountValue'] : null,
        'lowStockThreshold' => !empty($_POST['lowStockThreshold']) ? (int)$_POST['lowStockThreshold'] : 0
    ];

    // --- ट्रांसलिटरेशन ---
    // हिंदी नाम का इंग्लिश फोनेटिक संस्करण बनाएं
    $data['name_en'] = transliterate_hindi_to_english($data['name']);

    // तय करें कि कौन सी user_id (मालिक की आईडी) का उपयोग करना है
    $owner_id = ($_SESSION['user_type'] === 'owner') ? $_SESSION['user_id'] : $_SESSION['owner_user_id'];

    // सुनिश्चित करें कि प्रोडक्ट का नाम खाली नहीं है
    if (!empty($data['name'])) {
        $productModel = new Product($pdo);
        // मॉडल को हमेशा मालिक की आईडी भेजें
        $is_success = $productModel->create($_SESSION['current_store_id'], $owner_id, $data);

        if ($is_success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'प्रोडक्ट सफलतापूर्वक जोड़ दिया गया।'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'प्रोडक्ट को जोड़ने में कोई त्रुटि हुई।'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'प्रोडक्ट का नाम डालना अनिवार्य है।'];
    }
}

// एक्शन पूरा होने के बाद यूजर को वापस प्रोडक्ट लिस्ट पेज पर भेजें
// अगर नाम खाली था, तो उसे वापस फॉर्म पेज पर भेजें
if (!empty($data['name'])) {
    header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/products');
} else {
    header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/add_product');
}
exit();