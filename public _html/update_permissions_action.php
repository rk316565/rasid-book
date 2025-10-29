<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Staff.php';
protect_page(); require_store_context(); require_owner();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = (int)($_POST['staffId'] ?? 0);
    $permissions = [
        'can_view_reports' => isset($_POST['can_view_reports']),
        'can_manage_products' => isset($_POST['can_manage_products']),
        'can_manage_customers' => isset($_POST['can_manage_customers']),
        'can_do_billing' => isset($_POST['can_do_billing']),
        'can_manage_inventory' => isset($_POST['can_manage_inventory']),
        'can_manage_dues' => isset($_POST['can_manage_dues']),
    ];

    if ($staff_id > 0) {
        $staffModel = new Staff($pdo);
        $staffModel->updatePermissions($staff_id, $_SESSION['current_store_id'], $permissions);
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'अनुमतियाँ सफलतापूर्वक अपडेट हो गईं।'];
    }
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/staff');
exit();