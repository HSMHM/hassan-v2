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

    // Business Login for Instagram returns a short-lived token only.
    // For a long-lived token, generate it from the Meta App Dashboard:
    // Instagram > API setup with Instagram business login > Generate token.
    echo "<hr><h2>Your .env values:</h2><pre>";
    echo "INSTAGRAM_APP_ID=" . htmlspecialchars($clientId) . "\n";
    echo "INSTAGRAM_APP_SECRET=" . htmlspecialchars($clientSecret) . "\n";
    echo "INSTAGRAM_ACCESS_TOKEN=" . htmlspecialchars($data['access_token']) . "  # SHORT-LIVED (1hr)\n";
    echo "INSTAGRAM_ACCOUNT_ID=" . htmlspecialchars($data['user_id']) . "\n";
    echo "</pre>";

    echo '<p><strong>Permanent setup:</strong> generate a 60-day token from the Meta App Dashboard, then store it with <code>php artisan instagram:store-token YOUR_LONG_LIVED_TOKEN</code>.</p>';

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