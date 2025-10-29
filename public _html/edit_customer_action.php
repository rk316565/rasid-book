<?php
// public_html/edit_customer_action.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Customer.php';

protect_page();
require_store_context();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['editCustomerId'] ?? 0);
    $data = [
        'name' => trim($_POST['editCustomerName'] ?? ''),
        'phone' => trim($_POST['editCustomerPhone'] ?? ''),
        'email' => trim($_POST['editCustomerEmail'] ?? ''),
        'address' => trim($_POST['editCustomerAddress'] ?? '')
    ];

    // ======================================================
    //       यह है महत्वपूर्ण बदलाव: ट्रांसलिटरेशन
    // ======================================================
    $data['name_en'] = transliterate_hindi_to_english($data['name']);

    if (!empty($data['name']) && $customer_id > 0) {
        $customerModel = new Customer($pdo);
        // अब update फंक्शन को भी name_en को हैंडल करना होगा
        $customerModel->update($customer_id, $_SESSION['current_store_id'], $data);

        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'ग्राहक सफलतापूर्वक अपडेट हो गया।'];
    }
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/customers');
exit();