<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Store.php';
protect_page();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $storeId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    $storeModel = new Store($pdo);
    $store = $storeModel->findById($storeId, $userId);

    if ($store) {
        echo json_encode($store);
    } else {
        echo json_encode(['error' => 'Store not found']);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
exit();