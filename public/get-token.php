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
    
// Step 2: Try via graph.facebook.com instead
    if (isset($data['access_token'])) {
        // Attempt 1: graph.facebook.com with ig_exchange_token
        $url1 = 'https://graph.facebook.com/v25.0/access_token'
              . '?grant_type=ig_exchange_token'
              . '&client_secret=' . urlencode($secret)
              . '&access_token=' . urlencode($data['access_token']);
        $ch2 = curl_init($url1);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $r2 = curl_exec($ch2);
        curl_close($ch2);
        echo "<h2>Attempt 1 (facebook/ig_exchange):</h2><pre>$r2</pre>";
        
        // Attempt 2: graph.facebook.com with fb_exchange_token
        $url2 = 'https://graph.facebook.com/v25.0/oauth/access_token'
              . '?grant_type=fb_exchange_token'
              . '&client_id=1328943562382309'
              . '&client_secret=' . urlencode($secret)
              . '&fb_exchange_token=' . urlencode($data['access_token']);
        $ch3 = curl_init($url2);
        curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
        $r3 = curl_exec($ch3);
        curl_close($ch3);
        echo "<h2>Attempt 2 (facebook/fb_exchange):</h2><pre>$r3</pre>";

        // Attempt 3: graph.instagram.com with version
        $url3 = 'https://graph.instagram.com/v25.0/access_token'
              . '?grant_type=ig_exchange_token'
              . '&client_secret=' . urlencode($secret)
              . '&access_token=' . urlencode($data['access_token']);
        $ch4 = curl_init($url3);
        curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
        $r4 = curl_exec($ch4);
        curl_close($ch4);
        echo "<h2>Attempt 3 (instagram/v25.0):</h2><pre>$r4</pre>";
        
        echo "<h2>User ID:</h2><pre>{$data['user_id']}</pre>";
    }
} else {
    echo 'No code';
}