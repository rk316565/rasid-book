<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/models/User.php';

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URL);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        header('Location: ' . BASE_URL . '/?error=' . urlencode($token['error']));
        exit();
    }
    $client->setAccessToken($token['access_token']);
    
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $user_info = [
        'google_id' => $google_account_info->id,
        'name' => htmlspecialchars($google_account_info->name),
        'email' => htmlspecialchars($google_account_info->email),
        'profile_image' => htmlspecialchars($google_account_info->picture),
    ];

    $userModel = new User($pdo);
    $user = $userModel->findOrCreate($user_info);

    $_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_type'] = 'owner';

    // नए URL स्ट्रक्चर का उपयोग करें
    header('Location: ' . BASE_URL . '/dashboard');
    exit();
} else {
    // नए URL स्ट्रक्चर का उपयोग करें
    header('Location: ' . BASE_URL . '/');
    exit();
}