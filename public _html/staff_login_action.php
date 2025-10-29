<?php
// public_html/staff_login_action.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Staff.php';
require_once __DIR__ . '/src/models/Store.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // सुनिश्चित करें कि कोई भी फील्ड खाली नहीं है
    if (empty($username) || empty($password)) {
        header('Location: ' . BASE_URL . '/staff_login?error=All fields are required.'); 
        exit();
    }

    $staffModel = new Staff($pdo);
    // स्टाफ की पूरी जानकारी प्राप्त करें (SELECT *)
    $staff = $staffModel->findByUsername($username);

    // स्टोर को भी यहीं ढूंढ लें ताकि हम जांच सकें कि वह एक्टिव है या नहीं
    $storeModel = new Store($pdo);
    // user_id स्टाफ टेबल से आती है, जो कि मालिक की आईडी है
    $store = $staff ? $storeModel->findById($staff['store_id'], $staff['user_id']) : null;

    // जांचें कि क्या स्टाफ, स्टोर, और पासवर्ड सभी सही हैं
    if ($staff && $store && password_verify($password, $staff['password'])) {
        
        // जांचें कि क्या स्टोर एक्टिव है
        if ($store['status'] !== 'active') {
            header('Location: '. BASE_URL . '/staff_login?error=This store is not active. Please contact the owner.');
            exit();
        }

        // पासवर्ड सही है, अब सेशन सेट करें
        $_SESSION['user_id'] = $staff['id']; // स्टाफ की अपनी आईडी
        $_SESSION['user_name'] = $staff['name'];
        $_SESSION['user_type'] = 'staff';
        $_SESSION['current_store_id'] = $staff['store_id'];
        $_SESSION['owner_user_id'] = $staff['user_id']; // स्टोर के मालिक की आईडी
        $_SESSION['current_store_name'] = $store['store_name'];

        // ======================================================
        //       यह है महत्वपूर्ण बदलाव
        // ======================================================
        // स्टाफ की सभी अनुमतियों को सेशन में सेव करें
        $_SESSION['permissions'] = [
            'can_view_reports' => (bool)$staff['can_view_reports'],
            'can_manage_products' => (bool)$staff['can_manage_products'],
            'can_manage_customers' => (bool)$staff['can_manage_customers'],
            'can_do_billing' => (bool)$staff['can_do_billing'],
            'can_manage_inventory' => (bool)$staff['can_manage_inventory'],
            'can_manage_dues' => (bool)$staff['can_manage_dues'],
        ];

        // स्टाफ को सीधे स्टोर के होम पेज पर भेजें
        header('Location: '. BASE_URL . '/store/' . $staff['store_id'] . '/home');
        exit();

    } else {
        // अगर यूजरनेम या पासवर्ड गलत है
        header('Location: '. BASE_URL . '/staff_login?error=Invalid username or password.'); 
        exit();
    }
}