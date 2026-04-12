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
    $r2 = '';

    // Method A: GET graph.instagram.com (no version)
    $urlA = 'https://graph.instagram.com/access_token'
          . '?grant_type=ig_exchange_token'
          . '&client_secret=' . urlencode($clientSecret)
          . '&access_token=' . urlencode($shortToken);
    $chA = curl_init($urlA);
    curl_setopt($chA, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chA, CURLOPT_HTTPGET, true);
    $rA = curl_exec($chA);
    curl_close($chA);
    $dataA = json_decode($rA, true);
    echo "<h2>Method A (GET instagram, no version):</h2><pre>" . htmlspecialchars($rA) . "</pre>";
    if (isset($dataA['access_token'])) { $longData = $dataA; $r2 = $rA; }

    // Method B: POST graph.instagram.com (no version)
    if (!$longData) {
        $chB = curl_init('https://graph.instagram.com/access_token');
        curl_setopt($chB, CURLOPT_POST, true);
        curl_setopt($chB, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chB, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type'    => 'ig_exchange_token',
            'client_secret' => $clientSecret,
            'access_token'  => $shortToken,
        ]));
        $rB = curl_exec($chB);
        curl_close($chB);
        $dataB = json_decode($rB, true);
        echo "<h2>Method B (POST instagram, no version):</h2><pre>" . htmlspecialchars($rB) . "</pre>";
        if (isset($dataB['access_token'])) { $longData = $dataB; $r2 = $rB; }
    }

    // Method C: GET graph.facebook.com/v21.0
    if (!$longData) {
        $urlC = 'https://graph.facebook.com/v21.0/oauth/access_token'
              . '?grant_type=ig_exchange_token'
              . '&client_secret=' . urlencode($clientSecret)
              . '&access_token=' . urlencode($shortToken);
        $chC = curl_init($urlC);
        curl_setopt($chC, CURLOPT_RETURNTRANSFER, true);
        $rC = curl_exec($chC);
        curl_close($chC);
        $dataC = json_decode($rC, true);
        echo "<h2>Method C (GET facebook v21.0):</h2><pre>" . htmlspecialchars($rC) . "</pre>";
        if (isset($dataC['access_token'])) { $longData = $dataC; $r2 = $rC; }
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