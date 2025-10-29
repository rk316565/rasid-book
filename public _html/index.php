<?php
// public_html/index.php
require_once __DIR__ . '/config/config.php';

// --- URL को सुरक्षित रूप से पार्स करना ---
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($request_uri, '/');

// पैरामीटर्स को डिफ़ॉल्ट वैल्यूज पर सेट करें
$page = 'landing';
$store_id = 0; $bill_id = 0; $customer_id = 0; $filter = null;

// URL के पैटर्न को जांचें
if (preg_match('#^store/([0-9]+)/inventory/([^/]+)/?$#', $path, $matches)) {
    $store_id = (int)$matches[1]; $page = 'inventory'; $filter = $matches[2];
} elseif (preg_match('#^store/([0-9]+)/?([^/]*)/?$#', $path, $matches)) {
    $store_id = (int)$matches[1]; $page = !empty($matches[2]) ? $matches[2] : 'home';
} elseif (preg_match('#^bill/([0-9]+)/?$#', $path, $matches)) {
    $bill_id = (int)$matches[1]; $page = 'bill_view';
} elseif (preg_match('#^customer/ledger/([0-9]+)/?$#', $path, $matches)) {
    $customer_id = (int)$matches[1]; $page = 'customer_ledger';
} elseif (!empty($path)) {
    $page = $path;
}

// एक्शन पेज हैंडलिंग
$action_pages = [ 'google_login_action', 'logout', 'callback', 'select_store', 'add_store_action', 'edit_store_action', 'delete_store_action', 'get_store_details', 'payment-handler', 'payment_for_store', 'add_product_action', 'edit_product_action', 'delete_product_action', 'get_product_details', 'add_customer_action', 'edit_customer_action', 'delete_customer_action', 'get_customer_details', 'save_bill_action', 'api_search_products', 'api_search_customers', 'api_get_product_price', 'update_stock_action', 'transfer_stock_action', 'record_customer_payment_action', 'add_staff_action', 'delete_staff_action', 'staff_login_action', 'loyalty_settings_action', 'add_coupon_action', 'delete_coupon_action', 'api_apply_coupon', 'update_permissions_action', 'import_products_action', 'export_products_action', 'import_customers_action', 'export_customers_action' ];
if (in_array($page, $action_pages)) {
    if (file_exists(__DIR__ . '/' . $page . '.php')) {
        require_once __DIR__ . '/' . $page . '.php';
    } else { http_response_code(404); echo "404 Not Found"; }
    exit();
}

// सुरक्षा जांच (HTML भेजने से पहले)
$protected_pages = [ 'dashboard', 'home', 'products', 'add_product', 'customers', 'add_customer', 'billing', 'bills', 'inventory', 'reports', 'dues', 'staff', 'bill_view', 'customer_ledger', 'loyalty' ];
if (in_array($page, $protected_pages)) { if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . '/login'); exit(); } }
$owner_only_pages = ['dashboard', 'staff', 'loyalty'];
if (in_array($page, $owner_only_pages)) { if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'owner') { if (isset($_SESSION['current_store_id'])) { header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/home'); } else { header('Location: ' . BASE_URL . '/logout'); } exit(); } }

// लेआउट तय करें
if ($page === 'login' || $page === 'bill_view' || $page === 'customer_ledger') { /* No Layout */ } 
elseif ($store_id > 0) { require_once __DIR__ . '/templates/layouts/store_header.php'; } 
else { require_once __DIR__ . '/templates/layouts/header.php'; }

// पेज का कंटेंट दिखाएं
if ($page === 'login') {
    include __DIR__ . '/templates/pages/login_page.php';
} elseif ($page === 'bill_view') {
    $_GET['bill_id'] = $bill_id;
    include __DIR__ . '/templates/pages/store/bill_view.php';
} elseif ($page === 'customer_ledger') {
    $_GET['customer_id'] = $customer_id;
    include __DIR__ . '/templates/pages/store/customer_ledger.php';
} elseif ($store_id > 0) {
    $allowed_store_pages = ['home', 'products', 'add_product', 'customers', 'add_customer', 'billing', 'bills', 'inventory', 'reports', 'dues', 'staff', 'loyalty'];
    if (in_array($page, $allowed_store_pages)) {
        if (!isset($_SESSION['current_store_id']) || $_SESSION['current_store_id'] != $store_id) { header('Location: ' . BASE_URL . '/dashboard'); exit(); }
        $_GET['filter'] = $filter;
        include __DIR__ . '/templates/pages/store/' . $page . '.php';
    } else { header('Location: ' . BASE_URL . '/store/' . $store_id . '/home'); exit(); }
} else {
    switch ($page) {
        case 'dashboard': include __DIR__ . '/templates/pages/dashboard.php'; break;
        case 'refund_policy': include __DIR__ . '/templates/pages/refund_policy.php'; break;
        case 'landing': default: include __DIR__ . '/templates/pages/landing.php'; break;
    }
}

// फुटर लोड करें
if ($page === 'login' || $page === 'bill_view' || $page === 'customer_ledger') { /* No Footer */ } 
elseif ($store_id > 0) { require_once __DIR__ . '/templates/layouts/store_footer.php'; } 
else { require_once __DIR__ . '/templates/layouts/footer.php'; }