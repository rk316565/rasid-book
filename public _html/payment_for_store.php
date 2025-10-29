<?php
use Razorpay\Api\Api;
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/Store.php';

protect_page();

if (!isset($_GET['store_id'])) {
    // नए URL स्ट्रक्चर का उपयोग करें
    header('Location: ' . BASE_URL . '/dashboard?error=no_store_id');
    exit();
}

$storeId = $_GET['store_id'];
$storeModel = new Store($pdo);
$store = $storeModel->findById($storeId, $_SESSION['user_id']);

if (!$store) {
    // नए URL स्ट्रक्चर का उपयोग करें
    header('Location: ' . BASE_URL . '/dashboard?error=store_not_found');
    exit();
}

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
$orderData = [
    'receipt'         => 'store_rcptid_' . $storeId . '_' . time(),
    'amount'          => 49900,
    'currency'        => 'INR',
    'payment_capture' => 1,
    'notes'           => [ 'store_id'  => $storeId ]
];
$razorpayOrder = $api->order->create($orderData);
$razorpayOrderId = $razorpayOrder['id'];
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>स्टोर एक्टिवेशन</title>
    <style>
        body{display:flex; justify-content:center; align-items:center; height:100vh; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-family:sans-serif; margin:0; padding: 20px;}
        .container{background:white; padding:2rem; border-radius:10px; text-align:center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 400px;}
        h1 { margin-bottom: 0.5rem; } p { margin-bottom: 1.5rem; color: #555; }
        .razorpay-payment-button{background:#FF6B6B !important; color:white !important; border:none !important; padding:1rem 2rem !important; font-size:1.1rem !important; border-radius:50px !important; transition: all 0.2s ease;}
        .razorpay-payment-button:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="container">
        <h1>स्टोर एक्टिवेशन</h1>
        <p>आप "<?php echo htmlspecialchars($store['store_name']); ?>" को एक्टिवेट करने वाले हैं।</p>
        <form action="<?php echo BASE_URL; ?>/payment-handler" method="POST">
            <script
                src="https://checkout.razorpay.com/v1/checkout.js"
                data-key="<?php echo RAZORPAY_KEY_ID; ?>"
                data-amount="<?php echo $orderData['amount']; ?>"
                data-currency="INR"
                data-order_id="<?php echo $razorpayOrderId; ?>"
                data-buttontext="Pay ₹499 to Activate"
                data-name="रसीद बुक - स्टोर एक्टिवेशन"
                data-description="Store: <?php echo htmlspecialchars($store['store_name']); ?>"
                data-prefill.name="<?php echo htmlspecialchars($_SESSION['user_name']); ?>"
            ></script>
        </form>
    </div>
</body>
</html>