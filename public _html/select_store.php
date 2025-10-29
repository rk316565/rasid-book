<?php
// public_html/select_store.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Store.php';
// ======================================================
//       यह है महत्वपूर्ण बदलाव
// ======================================================
// helpers/functions.php को शामिल करें ताकि protect_page() मिल सके
require_once __DIR__ . '/src/helpers/functions.php';

// अब protect_page() को कॉल करें
protect_page();

$store_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($store_id > 0) {
    $storeModel = new Store($pdo);
    
    // <<<--- एक और सुरक्षा जांच: यूजर का प्रकार जांचें ---
    // अगर यूजर स्टाफ है, तो सुनिश्चित करें कि वह सिर्फ अपने निर्धारित स्टोर को ही चुन सकता है
    // अगर यूजर मालिक है, तो वह अपने किसी भी स्टोर को चुन सकता है
    $user_id_for_check = ($_SESSION['user_type'] === 'owner') ? $_SESSION['user_id'] : $_SESSION['owner_user_id'];
    
    $store = $storeModel->findById($store_id, $user_id_for_check);
    
    if ($store && $store['status'] === 'active') {
        // स्टोर की जानकारी सेशन में सेव करें
        $_SESSION['current_store_id'] = $store['id'];
        $_SESSION['current_store_name'] = $store['store_name'];

        // स्टोर के होम पेज पर भेजें
        header('Location: ' . BASE_URL . '/store/' . $store['id'] . '/home');
        exit();
    }
}

// अगर कोई समस्या होती है, तो डैशबोर्ड पर वापस भेजें
header('Location: ' . BASE_URL . '/dashboard?error=store_not_accessible');
exit();