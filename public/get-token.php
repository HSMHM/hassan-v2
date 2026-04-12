<?php
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Send as RAW string — no http_build_query to avoid double-encoding
    $postData = "client_id=1328943562382309"
        . "&client_secret=034bccf05019b5c967c7cd40464d4a23"
        . "&grant_type=authorization_code"
        . "&redirect_uri=https://almalki.sa/get-token.php"
        . "&code=" . $code;
    
    $ch = curl_init('https://api.instagram.com/oauth/access_token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h2>HTTP Code: $httpCode</h2>";
    echo "<h2>Result:</h2><pre>$response</pre>";
    
    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
        $ch2 = curl_init('https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=034bccf05019b5c967c7cd40464d4a23&access_token=' . $data['access_token']);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $long = curl_exec($ch2);
        curl_close($ch2);
        echo '<h2>Long-Lived Token:</h2><pre>' . $long . '</pre>';
        echo '<h2>User ID:</h2><pre>' . $data['user_id'] . '</pre>';
    }
} else {
    echo 'No code';
}