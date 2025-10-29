<?php
// public_html/payment-handler.php

use Razorpay\Api\Api;
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Store.php';

// सुनिश्चित करें कि यूजर लॉग इन है
protect_page();

// जांचें कि क्या Razorpay से POST डेटा मिला है
if (isset($_POST['razorpay_payment_id'])) {
    
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    try {
        // पेमेंट सिग्नेचर को वेरिफाई करें
        $attributes = [
            'razorpay_order_id' => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        ];
        $api->utility->verifyPaymentSignature($attributes);

        // Razorpay से ऑर्डर डिटेल्स प्राप्त करें ताकि हम स्टोर आईडी निकाल सकें
        $order = $api->order->fetch($_POST['razorpay_order_id']);
        $storeId = $order->notes['store_id'];
        
        // अगर स्टोर आईडी मिलती है, तो स्टोर को एक्टिवेट करें
        if ($storeId) {
            $storeModel = new Store($pdo);
            $storeModel->activateStore($storeId, $_SESSION['user_id'], $_POST['razorpay_payment_id']);
            
            // फ्लैश मैसेज को सेशन में सेट करें
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'स्टोर सफलतापूर्वक एक्टिवेट हो गया!'
            ];
            
            // सफल होने पर डैशबोर्ड पर वापस भेजें
            header('Location: ' . BASE_URL . '/dashboard');
            exit();
        } else {
             throw new Exception("Store ID not found in order notes.");
        }

    } catch(Exception $e) {
        // अगर कोई भी गलती होती है, तो फ्लैश मैसेज सेट करें
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => 'पेमेंट में कोई त्रुटि हुई। कृपया दोबारा प्रयास करें।'
        ];
        header('Location: ' . BASE_URL . '/dashboard');
        exit();
    }
} else {
    // अगर कोई Razorpay का डेटा नहीं मिलता है, तो डैशबोर्ड पर वापस भेजें
    header('Location: '. BASE_URL . '/dashboard');
    exit();
}