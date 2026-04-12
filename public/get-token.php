<?php
// إذا رجع من Instagram بالكود
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    $ch = curl_init('https://api.instagram.com/oauth/access_token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => '1328943562382309',
        'client_secret' => '034bccf05019b5c967c7cd40464d4a23',
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'https://almalki.sa/get-token.php',
        'code' => $code,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo '<h1>Result:</h1><pre>' . $response . '</pre>';
    
    // Try to get long-lived token
    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
        $ch2 = curl_init('https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=034bccf05019b5c967c7cd40464d4a23&access_token=' . $data['access_token']);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $long = curl_exec($ch2);
        curl_close($ch2);
        echo '<h1>Long-Lived Token:</h1><pre>' . $long . '</pre>';
    }
} else {
    echo '<h1>No code received</h1>';
}