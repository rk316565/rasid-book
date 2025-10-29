<?php
// public_html/record_customer_payment_action.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Customer.php';
protect_page();
require_store_context();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customerId'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($customer_id > 0 && $amount > 0) {
        $customerModel = new Customer($pdo);
        $customerModel->recordPayment($_SESSION['current_store_id'], $customer_id, $amount, $notes);
        
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => '₹' . $amount . ' का पेमेंट सफलतापूर्वक रिकॉर्ड हो गया।'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'अमान्य राशि या ग्राहक।'];
    }
    // उसी लेजर पेज पर वापस भेजें
    header('Location: ' . BASE_URL . '/customer/ledger/' . $customer_id);
    exit();
}
// अगर कोई POST डेटा नहीं है, तो डैशबोर्ड पर भेजें
header('Location: ' . BASE_URL . '/dashboard');
exit();