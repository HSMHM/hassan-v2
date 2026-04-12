<?php
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    $ch = curl_init('https://api.instagram.com/oauth/access_token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Use array = multipart/form-data (like -F in curl)
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'client_id'     => '1328943562382309',
        'client_secret' => '034bccf05019b5c967c7cd40464d4a23',
        'grant_type'    => 'authorization_code',
        'redirect_uri'  => 'https://almalki.sa/get-token.php',
        'code'          => $code,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo "<h2>Result:</h2><pre>$response</pre>";
    
    $data = json_decode($response, true);
    if (isset($data['data'][0]['access_token'])) {
        $token = $data['data'][0]['access_token'];
        $userId = $data['data'][0]['user_id'];
        
        $ch2 = curl_init("https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=034bccf05019b5c967c7cd40464d4a23&access_token=$token");
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $long = curl_exec($ch2);
        curl_close($ch2);
        
        echo "<h2>User ID (= INSTAGRAM_ACCOUNT_ID):</h2><pre>$userId</pre>";
        echo "<h2>Long-Lived Token:</h2><pre>$long</pre>";
    }
} else {
    echo 'No code';
}