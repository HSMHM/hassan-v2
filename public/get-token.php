<?php
$clientId     = '1328943562382309';
$clientSecret = '2f1a345657457b5521ea18e1b900da98';
$redirectUri  = 'https://almalki.sa/get-token.php';

if (isset($_GET['code'])) {
    // Step 1: Exchange code for short-lived token
    $ch = curl_init('https://api.instagram.com/oauth/access_token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'grant_type'    => 'authorization_code',
        'redirect_uri'  => $redirectUri,
        'code'          => $_GET['code'],
    ]);
    $r1 = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($r1, true);
    echo "<h2>Step 1 — Short-lived token:</h2><pre>" . htmlspecialchars($r1) . "</pre>";

    if (!isset($data['access_token'])) {
        die('<p style="color:red">Failed to get short-lived token.</p>');
    }

    // Step 2: Exchange short-lived for long-lived token (try multiple methods)
    $shortToken = $data['access_token'];
    $longData = null;

    // Method A: GET graph.facebook.com/access_token (ig_exchange_token)
    $urlA = 'https://graph.facebook.com/v21.0/access_token'
          . '?grant_type=ig_exchange_token'
          . '&client_secret=' . urlencode($clientSecret)
          . '&access_token=' . urlencode($shortToken);
    $chA = curl_init($urlA);
    curl_setopt($chA, CURLOPT_RETURNTRANSFER, true);
    $rA = curl_exec($chA);
    curl_close($chA);
    $dataA = json_decode($rA, true);
    echo "<h2>Method A (facebook /access_token):</h2><pre>" . htmlspecialchars($rA) . "</pre>";
    if (isset($dataA['access_token'])) { $longData = $dataA; }

    // Method B: GET graph.facebook.com/oauth/access_token (fb_exchange_token)
    if (!$longData) {
        $urlB = 'https://graph.facebook.com/v21.0/oauth/access_token'
              . '?grant_type=fb_exchange_token'
              . '&client_id=' . urlencode($clientId)
              . '&client_secret=' . urlencode($clientSecret)
              . '&fb_exchange_token=' . urlencode($shortToken);
        $chB = curl_init($urlB);
        curl_setopt($chB, CURLOPT_RETURNTRANSFER, true);
        $rB = curl_exec($chB);
        curl_close($chB);
        $dataB = json_decode($rB, true);
        echo "<h2>Method B (facebook /oauth fb_exchange):</h2><pre>" . htmlspecialchars($rB) . "</pre>";
        if (isset($dataB['access_token'])) { $longData = $dataB; }
    }

    // Method C: POST graph.instagram.com/access_token
    if (!$longData) {
        $chC = curl_init('https://graph.instagram.com/access_token');
        curl_setopt($chC, CURLOPT_POST, true);
        curl_setopt($chC, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chC, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type'    => 'ig_exchange_token',
            'client_secret' => $clientSecret,
            'access_token'  => $shortToken,
        ]));
        $rC = curl_exec($chC);
        curl_close($chC);
        $dataC = json_decode($rC, true);
        echo "<h2>Method C (POST instagram):</h2><pre>" . htmlspecialchars($rC) . "</pre>";
        if (isset($dataC['access_token'])) { $longData = $dataC; }
    }

    // Method D: GET graph.instagram.com (no version, explicit)
    if (!$longData) {
        $urlD = 'https://graph.instagram.com/access_token'
              . '?grant_type=ig_exchange_token'
              . '&client_secret=' . urlencode($clientSecret)
              . '&access_token=' . urlencode($shortToken);
        $chD = curl_init($urlD);
        curl_setopt($chD, CURLOPT_RETURNTRANSFER, true);
        $rD = curl_exec($chD);
        curl_close($chD);
        $dataD = json_decode($rD, true);
        echo "<h2>Method D (GET instagram):</h2><pre>" . htmlspecialchars($rD) . "</pre>";
        if (isset($dataD['access_token'])) { $longData = $dataD; }
    }

    // Summary
    echo "<hr><h2>Your .env values:</h2><pre>";
    echo "INSTAGRAM_APP_ID=" . htmlspecialchars($clientId) . "\n";
    echo "INSTAGRAM_APP_SECRET=" . htmlspecialchars($clientSecret) . "\n";
    if ($longData) {
        echo "INSTAGRAM_ACCESS_TOKEN=" . htmlspecialchars($longData['access_token']) . "\n";
        echo "# LONG-LIVED (60 days) — expires_in: " . ($longData['expires_in'] ?? 'unknown') . " seconds\n";
    } else {
        echo "INSTAGRAM_ACCESS_TOKEN=" . htmlspecialchars($data['access_token']) . "  # SHORT-LIVED (1hr)\n";
    }
    echo "INSTAGRAM_ACCOUNT_ID=" . htmlspecialchars($data['user_id']) . "\n";
    echo "</pre>";

} else {
    // No code — redirect to Instagram authorization
    $scopes = implode(',', [
        'instagram_business_basic',
        'instagram_business_content_publish',
        'instagram_business_manage_comments',
        'instagram_business_manage_insights',
    ]);
    $authUrl = "https://www.instagram.com/oauth/authorize"
             . "?client_id={$clientId}"
             . "&redirect_uri=" . urlencode($redirectUri)
             . "&response_type=code"
             . "&scope={$scopes}"
             . "&force_reauth=true";

    echo "<p><a href='" . htmlspecialchars($authUrl) . "'>Click here to authorize Instagram</a></p>";
}