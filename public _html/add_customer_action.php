<?php
// public_html/add_customer_action.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Customer.php';

// सुरक्षा जांचें
protect_page();
require_store_context();

// जांचें कि क्या फॉर्म सबमिट किया गया है
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // फॉर्म से सभी डेटा प्राप्त करें
    $data = [
        'name' => trim($_POST['customerName'] ?? ''),
        'phone' => trim($_POST['customerPhone'] ?? ''),
        'email' => trim($_POST['customerEmail'] ?? ''),
        'address' => trim($_POST['customerAddress'] ?? '')
    ];

    // --- ट्रांसलिटरेशन ---
    // हिंदी नाम का इंग्लिश फोनेटिक संस्करण बनाएं
    $data['name_en'] = transliterate_hindi_to_english($data['name']);
    
    // तय करें कि कौन सी user_id (मालिक की आईडी) का उपयोग करना है
    $owner_id = ($_SESSION['user_type'] === 'owner') ? $_SESSION['user_id'] : $_SESSION['owner_user_id'];

    // सुनिश्चित करें कि ग्राहक का नाम खाली नहीं है
    if (!empty($data['name'])) {
        $customerModel = new Customer($pdo);
        // मॉडल को हमेशा मालिक की आईडी भेजें
        $is_success = $customerModel->create($_SESSION['current_store_id'], $owner_id, $data);
        
        if ($is_success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'ग्राहक सफलतापूर्वक जोड़ दिया गया।'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ग्राहक को जोड़ने में कोई त्रुटि हुई।'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ग्राहक का नाम डालना अनिवार्य है।'];
    }
}

// एक्शन पूरा होने के बाद यूजर को वापस ग्राहक लिस्ट पेज पर भेजें
// अगर नाम खाली था, तो उसे वापस फॉर्म पेज पर भेजें
if (!empty($data['name'])) {
    header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/customers');
} else {
    header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/add_customer');
}
exit();