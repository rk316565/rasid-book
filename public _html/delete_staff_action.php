<?php
require_once __DIR__ . '/config/config.php'; require_once __DIR__ . '/src/models/Staff.php';
protect_page(); require_store_context(); require_owner();

if (isset($_GET['id'])) {
    $staff_id = (int)$_GET['id'];
    $staffModel = new Staff($pdo);
    $staffModel->delete($staff_id, $_SESSION['current_store_id']);
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'स्टाफ सफलतापूर्वक डिलीट हो गया।'];
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/staff');
exit();