<?php
if (isset($_GET['code'])) {
    $secret = '2f1a345657457b5521ea18e1b900da98';
    
    // Step 1: Get short-lived token
    $ch = curl_init('https://api.instagram.com/oauth/access_token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'client_id'     => '1328943562382309',
        'client_secret' => $secret,
        'grant_type'    => 'authorization_code',
        'redirect_uri'  => 'https://almalki.sa/get-token.php',
        'code'          => $_GET['code'],
    ]);
    $r1 = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($r1, true);
    echo "<h2>Short-lived:</h2><pre>$r1</pre>";
    
// Step 2: GET with params in URL (not body)
    if (isset($data['access_token'])) {
        $url = 'https://graph.instagram.com/access_token'
             . '?grant_type=ig_exchange_token'
             . '&client_secret=' . urlencode($secret)
             . '&access_token=' . urlencode($data['access_token']);
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $url);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPGET, true);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $r2 = curl_exec($ch2);
        $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        $error = curl_error($ch2);
        curl_close($ch2);
        
        echo "<h2>Long-lived (HTTP $httpCode):</h2><pre>$r2</pre>";
        if ($error) echo "<h2>Curl Error:</h2><pre>$error</pre>";
        echo "<h2>User ID:</h2><pre>{$data['user_id']}</pre>";
    }
} else {
    echo 'No code';
}