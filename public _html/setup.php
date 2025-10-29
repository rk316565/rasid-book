<?php
require_once __DIR__ . '/config/config.php';
echo '<!DOCTYPE html><html>... (पिछली बार जैसा स्टाइल) ...</html>';
echo '<h1>ट्रांसलिटरेशन सर्च के लिए डेटाबेस अपडेट</h1>';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<p class="success">✓ डेटाबेस से कनेक्शन सफल रहा!</p>';

    // --- Products टेबल को अपडेट करें ---
    $pdo->exec("ALTER TABLE `products` ADD `name_en` VARCHAR(255) NULL AFTER `name`;");
    echo '<p class="success">✓ `products` टेबल में `name_en` कॉलम जोड़ दिया गया।</p>';

    // --- Customers टेबल को अपडेट करें ---
    $pdo->exec("ALTER TABLE `customers` ADD `name_en` VARCHAR(255) NULL AFTER `name`;");
    echo '<p class="success">✓ `customers` टेबल में `name_en` कॉलम जोड़ दिया गया।</p>';

    echo '<h2 class="note">अपडेट पूरा हुआ! अब इस `setup.php` फाइल को डिलीट कर दें!</h2>';
} catch (PDOException $e) {
    echo '<p class="error">✗ एक त्रुटि हुई (हो सकता है कॉलम पहले से मौजूद हों): ' . $e->getMessage() . '</p>';
}
echo '</div></body></html>';