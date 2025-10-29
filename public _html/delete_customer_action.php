<?php
// public_html/delete_customer_action.php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Customer.php';

protect_page();
require_store_context();

if (isset($_GET['id'])) {
    $customer_id = (int)$_GET['id'];
    if ($customer_id > 0) {
        $customerModel = new Customer($pdo);
        $customerModel->delete($customer_id, $_SESSION['current_store_id']);
        
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'ग्राहक सफलतापूर्वक डिलीट हो गया।'];
    }
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/customers');
exit();