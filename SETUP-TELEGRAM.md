# Telegram Bot Setup

## 1. Create Telegram Bot

1. Open Telegram, search for **@BotFather**
2. Send `/newbot`
3. Choose a name: `Hassan News Bot`
4. Choose a username: `almalki_news_bot` (or any available name)
5. Copy the token to `.env`:
   ```
   TELEGRAM_BOT_TOKEN=your-bot-token-here
   ```

## 2. Get Your Chat ID

1. Send any message to your new bot in Telegram
2. Open in browser:
   ```
   https://api.telegram.org/bot<TOKEN>/getUpdates
   ```
3. Find `"chat":{"id": XXXXXXX}` in the response
4. Copy the number to `.env`:
   ```
   TELEGRAM_CHAT_ID=your-chat-id
   ```

## 3. Set Webhook Secret

Add a random string to `.env`:
```
TELEGRAM_WEBHOOK_SECRET=your-random-secret-string
```

## 4. Set Webhook

```bash
php artisan telegram:set-webhook
```

## 5. Test

- In Telegram, send `/start` to your bot — should reply with command list
- Send `خبر جديد` — should search for news
- Or test connectivity:
  ```bash
  php artisan news:test-platform telegram
  ```

## Available Commands (in Telegram)

| Command | Action |
|---------|--------|
| `خبر جديد` or `/news` | Search for latest Claude AI news |
| `publish` or `نشر` | Publish pending news to all platforms |
| `edit: instructions` | Edit content with AI |
| `skip` or `تجاوز` | Skip pending news |
| `/status` | Show system status |
| `/start` | Show help |

## Remove Webhook (if needed)

```bash
php artisan telegram:set-webhook --delete
```
