<?php
// public_html/delete_store_action.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Store.php';

// यूजर को लॉग इन होना चाहिए
protect_page();

// जांचें कि क्या URL में डिलीट करने के लिए स्टोर की आईडी है
if (isset($_GET['id'])) {
    $storeId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    $storeModel = new Store($pdo);
    
    // स्टोर को डिलीट करें
    $storeModel->delete($storeId, $userId);

    // फ्लैश मैसेज को सेशन में सेट करें
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => 'स्टोर सफलतापूर्वक डिलीट हो गया।'
    ];
}

// साफ-सुथरे URL पर भेजें
header('Location: ' . BASE_URL . '/dashboard');
exit();