<?php
// public_html/import_products_action.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Product.php';

protect_page();
require_store_context();
require_owner(); // सिर्फ मालिक ही इंपोर्ट कर सकता है

$redirect_url = BASE_URL . '/store/' . $_SESSION['current_store_id'] . '/products';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['productCsvFile'])) {
    $file = $_FILES['productCsvFile'];

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

    $productModel = new Product($pdo);
    $header = fgetcsv($handle, 1000, ",");
    $added_count = 0;
    $skipped_count = 0;
    $owner_id = $_SESSION['user_id'];

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $product_data = [
            'name' => $data[0] ?? '',
            'costPrice' => !empty($data[1]) ? (float)$data[1] : 0,
            'price' => !empty($data[2]) ? (float)$data[2] : 0,
            'stock' => !empty($data[3]) ? (int)$data[3] : 0,
            'godownStock' => !empty($data[4]) ? (int)$data[4] : 0,
            'discountType' => $data[5] ?? 'none',
            'discountValue' => !empty($data[6]) ? (float)$data[6] : null,
            'bulkDiscountMinQuantity' => !empty($data[7]) ? (int)$data[7] : null,
            'bulkDiscountType' => $data[8] ?? 'none',
            'bulkDiscountValue' => !empty($data[9]) ? (float)$data[9] : null,
            'lowStockThreshold' => !empty($data[10]) ? (int)$data[10] : 0
        ];

        // ======================================================
        //       यह है महत्वपूर्ण बदलाव: ट्रांसलिटरेशन
        // ======================================================
        $product_data['name_en'] = transliterate_hindi_to_english($product_data['name']);

        if (!empty($product_data['name'])) {
            $productModel->create($_SESSION['current_store_id'], $owner_id, $product_data);
            $added_count++;
        } else {
            $skipped_count++;
        }
    }
    fclose($handle);

    $_SESSION['flash_message'] = ['type' => 'success', 'text' => $added_count . ' प्रोडक्ट सफलतापूर्वक इंपोर्ट हो गए। ' . $skipped_count . ' पंक्तियाँ छोड़ी गईं।'];
} else {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'कोई फाइल नहीं चुनी गई।'];
}

header('Location: ' . $redirect_url);
exit();