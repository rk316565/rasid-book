<?php
// public_html/export_customers_action.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Customer.php';

protect_page();
require_store_context();
require_owner(); // सिर्फ मालिक ही एक्सपोर्ट कर सकता है

$customerModel = new Customer($pdo);
$customers = $customerModel->getAllByStoreId($_SESSION['current_store_id']);

if (empty($customers)) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'एक्सपोर्ट करने के लिए कोई ग्राहक नहीं है।'];
    header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/customers');
    exit();
}

$filename = "customers_export_" . date('Y-m-d') . ".csv";

// ब्राउज़र को बताएं कि यह एक डाउनलोड करने वाली फाइल है
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// लिखने के लिए फाइल पॉइंटर खोलें
$output = fopen('php://output', 'w');

// CSV का हेडर (कॉलम के नाम)
fputcsv($output, ['Name', 'Phone', 'Email', 'Address']);

// हर ग्राहक का डेटा CSV में लिखें
foreach ($customers as $customer) {
    fputcsv($output, [$customer['name'], $customer['phone'], $customer['email'], $customer['address']]);
}

fclose($output);
exit();