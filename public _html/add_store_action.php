<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Store.php';
protect_page();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'store_name' => trim($_POST['store_name'] ?? ''),
        'owner_name' => trim($_POST['owner_name'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'shop_number' => trim($_POST['shop_number'] ?? ''),
        'street_name' => trim($_POST['street_name'] ?? ''),
        'landmark' => trim($_POST['landmark'] ?? ''),
        'locality' => trim($_POST['locality'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'country' => trim($_POST['country'] ?? 'India'),
        'pin_code' => trim($_POST['pin_code'] ?? '')
    ];

    if (!empty($data['store_name'])) {
        $storeModel = new Store($pdo);
        $storeModel->create($_SESSION['user_id'], $data);
    }
}
header('Location: ' . BASE_URL . '/dashboard');
exit();