<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Loyalty.php';
protect_page(); require_store_context(); require_owner();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'points_per_amount' => $_POST['points_per_amount'] ?? 100,
        'points_awarded' => $_POST['points_awarded'] ?? 1,
        'point_value' => $_POST['point_value'] ?? 1,
    ];
    $loyaltyModel = new Loyalty($pdo);
    $loyaltyModel->saveSettings($_SESSION['current_store_id'], $data);
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'लॉयल्टी के नियम सफलतापूर्वक सेव हो गए।'];
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/loyalty');
exit();