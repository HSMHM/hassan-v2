# Instagram Token Setup

## What was changed

The Instagram token flow in this project was adjusted to match the current Meta Instagram Login behavior.

### 1. `public/get-token.php`

- Exchanges the Instagram authorization `code` for a **short-lived token**.
- Shows the current values needed for `.env`.
- No longer tries to force a long-lived token exchange from this flow, because this login flow currently returns a short-lived token only.

### 2. `app/Services/InstagramService.php`

- Uses the token stored in the `platform_tokens` table first.
- Falls back to `INSTAGRAM_ACCESS_TOKEN` from `.env` only if no database token exists.
- Keeps token refresh support through `refresh_access_token`.
- Stores refreshed tokens back into the database.

### 3. `app/Console/Commands/InstagramStoreToken.php`

Added a new Artisan command:

```bash
php artisan instagram:store-token YOUR_LONG_LIVED_TOKEN
```

This stores the Instagram token in the database so the publishing flow uses a stable token source.

### 4. `app/Console/Commands/RefreshTokens.php`

- Updated to save refreshed Instagram tokens through the shared token storage helper.


## Important conclusion

The token returned from this URL:

```text
https://www.instagram.com/oauth/authorize?... 
```

is currently a **short-lived token** in this integration flow.

That means:

- It is valid temporarily, roughly 1 hour.
- It is useful for quick testing.
- It is **not** the permanent token workflow for production publishing.


## Permanent workflow for this project

Use a **long-lived token from the Meta App Dashboard**, then store it in the database.

### Step 1

Open your Meta app dashboard and go to:

```text
Instagram > API setup with Instagram business login > Generate token
```

Generate the token for the target Instagram professional account.

### Step 2

Store it in the project:

```bash
php artisan instagram:store-token YOUR_LONG_LIVED_TOKEN
```

Optional custom expiry:

```bash
php artisan instagram:store-token YOUR_LONG_LIVED_TOKEN --expires-in=5184000
```

### Step 3

Test publishing:

```bash
php artisan news:test-platform instagram
```


## Runtime behavior after the fix

When the project publishes to Instagram:

1. It first checks `platform_tokens` for the `instagram` token.
2. If found, it uses that token.
3. If not found, it falls back to `INSTAGRAM_ACCESS_TOKEN` in `.env`.

This means the project does **not** need to generate a new token for every Telegram publish request.


## Refresh behavior

There is already a refresh schedule in the project.

- `routes/console.php` schedules Instagram token refresh.
- `tokens:refresh` refreshes supported platform tokens.
- Refreshed Instagram tokens are stored back into the database.


## Current limitation

The local database was not available during implementation, so migrations and seed-related verification could not be executed from this environment.

The last migration attempt failed because MySQL was not running.


## Recommended next steps

1. Start MySQL / Laragon.
2. Generate the long-lived Instagram token from Meta App Dashboard.
3. Store it using:

```bash
php artisan instagram:store-token YOUR_LONG_LIVED_TOKEN
```

4. Test with:

```bash
php artisan news:test-platform instagram
```

5. If publishing still fails, inspect the returned API error. At that point the problem will likely be one of:

- missing app permission approval
- missing Page Publishing Authorization (PPA)
- unsupported media URL
- invalid Instagram professional account setup
