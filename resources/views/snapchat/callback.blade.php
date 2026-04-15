<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Snapchat OAuth — Success</title>
    <style>
        body { font-family: monospace; background: #121212; color: #eee; padding: 2rem; max-width: 900px; margin: auto; }
        h1 { color: #FFFC00; }
        code { display: block; background: #1e1e1e; padding: 1rem; border-radius: 6px; word-break: break-all; margin: 0.5rem 0 1.5rem; white-space: pre-wrap; }
        .warn { background: #5c1a1a; padding: 1rem; border-radius: 6px; margin-top: 2rem; }
        label { color: #aaa; font-size: 0.9rem; }
    </style>
</head>
<body>
    <h1>✅ Snapchat OAuth Success</h1>

    <p>Copy these into <code>.env</code> on the server, then run <code>php artisan config:cache</code>.</p>

    <label>SNAPCHAT_ACCESS_TOKEN (expires in {{ $expiresIn }} seconds)</label>
    <code>SNAPCHAT_ACCESS_TOKEN={{ $access }}</code>

    <label>SNAPCHAT_REFRESH_TOKEN (long-lived — keep it safe)</label>
    <code>SNAPCHAT_REFRESH_TOKEN={{ $refresh }}</code>

    <div class="warn">
        ⚠️ Access tokens live only ~60 minutes. Use <code>php artisan snapchat:refresh</code>
        to get a new one using the refresh token — or the auto-refresh kicks in when the service hits a 401.
    </div>
</body>
</html>
