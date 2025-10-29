<?php
// public_html/add_staff_action.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Staff.php';
protect_page();
require_store_context();
require_owner();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? ''
    ];

    if (empty($data['name']) || empty($data['username']) || empty($data['password'])) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'सभी फील्ड्स भरना अनिवार्य है।'];
        header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/staff');
        exit();
    }

    $staffModel = new Staff($pdo);
    
    // ======================================================
    //       यह है महत्वपूर्ण बदलाव: ग्लोबल जांच
    // ======================================================
    // अब हम ग्लोबली जांच कर रहे हैं
    if ($staffModel->usernameExists($data['username'])) {
        $_SESSION['flash_message'] = [
            'type' => 'error', 
            'text' => 'यह यूजरनेम "' . htmlspecialchars($data['username']) . '" पहले से ही किसी और ने ले लिया है। कृपया कोई दूसरा यूजरनेम चुनें।'
        ];
        header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/staff');
        exit();
    }
    
    $is_success = $staffModel->create($_SESSION['current_store_id'], $_SESSION['user_id'], $data);

    if ($is_success) {
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'स्टाफ सदस्य सफलतापूर्वक जोड़ दिया गया।'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'स्टाफ को जोड़ने में कोई त्रुटि हुई।'];
    }
}
header('Location: ' . BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/staff');
exit();