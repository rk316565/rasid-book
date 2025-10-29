<?php
// public_html/google_login_action.php
require_once __DIR__ . '/config/config.php';

// गूगल क्लाइंट को कॉन्फ़िगर करें
$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URL);
$client->addScope("email");
$client->addScope("profile");

// यूजर को गूगल ऑथेंटिकेशन पेज पर भेजें
$loginUrl = $client->createAuthUrl();
header('Location: ' . $loginUrl);
exit();