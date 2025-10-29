<?php
// public_html/get_customer_details.php

// सीधे config.php को शामिल करें
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Customer.php';

// सेशन को सुरक्षित रूप से शुरू करें
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// सुरक्षा जांचें
protect_page();
require_store_context();

// सुनिश्चित करें कि आउटपुट JSON है
header('Content-Type: application/json');

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$response = []; // एक खाली रिस्पांस ऐरे बनाएं

if ($customer_id > 0) {
    try {
        $customerModel = new Customer($pdo);
        $customer = $customerModel->findById($customer_id, $_SESSION['current_store_id']);

        if ($customer) {
            $response = $customer;
        } else {
            $response['error'] = 'Customer not found or access denied.';
        }
    } catch (Exception $e) {
        $response['error'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'No customer ID provided.';
}

// JSON रिस्पांस भेजें और स्क्रिप्ट को बंद करें
echo json_encode($response);
exit();