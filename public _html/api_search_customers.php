<?php
require_once __DIR__ . '/config/config.php'; require_once __DIR__ . '/src/models/Customer.php';
protect_page(); require_store_context();
header('Content-Type: application/json');
$term = $_GET['term'] ?? '';
$customerModel = new Customer($pdo);
$customers = $customerModel->searchByNameOrPhone($_SESSION['current_store_id'], $term);
echo json_encode($customers);