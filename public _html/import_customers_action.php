<?php
// public_html/import_customers_action.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Customer.php';

protect_page();
require_store_context();
require_owner(); // सिर्फ मालिक ही इंपोर्ट कर सकता है

$redirect_url = BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/customers';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['customerCsvFile'])) {
    $file = $_FILES['customerCsvFile'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'फाइल अपलोड करने में कोई त्रुटि हुई।'];
        header('Location: ' . $redirect_url);
        exit();
    }

    $file_path = $file['tmp_name'];
    $handle = fopen($file_path, "r");

    if ($handle === FALSE) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'CSV फाइल को पढ़ने में असमर्थ।'];
        header('Location: ' . $redirect_url);
        exit();
    }

    $customerModel = new Customer($pdo);
    $header = fgetcsv($handle, 1000, ",");
    $added_count = 0;
    $skipped_count = 0;
    $owner_id = $_SESSION['user_id'];

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $customer_data = [
            'name' => $data[0] ?? '',
            'phone' => $data[1] ?? '',
            'email' => $data[2] ?? '',
            'address' => $data[3] ?? ''
        ];
        
        // ======================================================
        //       यह है महत्वपूर्ण बदलाव: ट्रांसलिटरेशन
        // ======================================================
        $customer_data['name_en'] = transliterate_hindi_to_english($customer_data['name']);

        if (!empty($customer_data['name'])) {
            $customerModel->create($_SESSION['current_store_id'], $owner_id, $customer_data);
            $added_count++;
        } else {
            $skipped_count++;
        }
    }
    fclose($handle);

    $_SESSION['flash_message'] = ['type' => 'success', 'text' => $added_count . ' ग्राहक सफलतापूर्वक इंपोर्ट हो गए। ' . $skipped_count . ' पंक्तियाँ छोड़ी गईं।'];
} else {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'कोई फाइल नहीं चुनी गई।'];
}

header('Location: ' . $redirect_url);
exit();