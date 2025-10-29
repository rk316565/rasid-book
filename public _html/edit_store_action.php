<?php
// public_html/edit_store_action.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Store.php';

// यूजर को लॉग इन होना चाहिए
protect_page();

// जांचें कि क्या फॉर्म सबमिट किया गया है
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storeId = $_POST['store_id'] ?? 0;
    $data = [
        'store_name' => trim($_POST['store_name'] ?? ''),
        'owner_name' => trim($_POST['owner_name'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'shop_number' => trim($_POST['shop_number'] ?? ''),
        'street_name' => trim($_POST['street_name'] ?? ''),
        'landmark' => trim($_POST['landmark'] ?? ''),
        'locality' => trim($_POST['locality'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'country' => trim($_POST['country'] ?? 'India'),
        'pin_code' => trim($_POST['pin_code'] ?? '')
    ];

    // सुनिश्चित करें कि स्टोर का नाम और आईडी खाली नहीं है
    if (!empty($data['store_name']) && !empty($storeId)) {
        $storeModel = new Store($pdo);
        
        // स्टोर को अपडेट करें
        $is_success = $storeModel->update($storeId, $_SESSION['user_id'], $data);

        // ===========================================
        //       यह है महत्वपूर्ण बदलाव
        // ===========================================
        // अगर अपडेट सफल होता है, तो फ्लैश मैसेज सेट करें
        if ($is_success) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'स्टोर सफलतापूर्वक अपडेट हो गया।'
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'text' => 'स्टोर को अपडेट करने में कोई त्रुटि हुई।'
            ];
        }
    }
}

// साफ-सुथरे URL पर भेजें
header('Location: ' . BASE_URL . '/dashboard');
exit();