# Deployment Guide — Hassan Almalki Portfolio

Complete walkthrough for pushing this project to GitHub and setting up **Laravel Forge** to auto-deploy every push to `main`.

**Stack:** Laravel 13 · Inertia.js v3 · Vue 3 · Filament v5 · MySQL 8 · Vite 5 · Queue (database) · Scheduler

---

## 0. Prerequisites

- [ ] GitHub account + empty repository created (e.g. `HSMHM/hassan-v2`)
- [ ] Laravel Forge account ([forge.laravel.com](https://forge.laravel.com))
- [ ] A server provisioned via Forge (DigitalOcean, Hetzner, AWS EC2, etc.) running Ubuntu 22.04 + PHP 8.3 + MySQL 8 + Nginx
- [ ] Domain name pointed to the server's IP (A record for `almalki.sa` and `www.almalki.sa`)

---

## 1. Local: commit and push everything

### 1.1 — Confirm `.gitignore` excludes secrets and build artifacts

Already configured in this repo — verify:
```bash
cat .gitignore
```

Must include (or equivalent): `.env`, `/vendor`, `/node_modules`, `/public/build`, `/public/hot`, `/public/storage`, `/storage/*.key`, `*.log`, `.phpunit.result.cache`

### 1.2 — Initialize git if not already

```bash
cd c:/laragon/www/hassan-v2
git status                       # confirm you're in a repo
git branch -M main               # ensure default branch is main
```

If this folder isn't yet a git repo:
```bash
git init
git branch -M main
```

### 1.3 — Stage and commit

```bash
# Review what will be committed
git status
git diff --stat

# Stage everything respecting .gitignore
git add .

# First commit (or incremental commit)
git commit -m "feat: Laravel + Inertia + Filament migration with performance optimizations"
```

### 1.4 — Connect to GitHub and push

```bash
# Add remote (only needed once)
git remote add origin git@github.com:HSMHM/hassan-v2.git

# Or with HTTPS:
# git remote add origin https://github.com/HSMHM/hassan-v2.git

# Push
git push -u origin main
```

### 1.5 — Rotate exposed secrets BEFORE pushing

⚠️ **Critical** — the following secrets from earlier sessions are in the commit history and must be rotated before making the repo public or deploying:

- Font Awesome npm token (was in `.npmrc`)
- IndexNow key (was in `src/config.js` and `/public/a1b2c3d4...txt`)
- Google Sheets webhook URL (was in `functions/index.js`)
- Proposal passwords from the old JSON files

See the rotation list in the main migration summary. Generate new values and store them **only** in Forge's encrypted environment variables (step 3.3 below), never in the repo.

---

## 2. Forge: provision a server

Skip this step if you already have a Forge server.

1. Go to https://forge.laravel.com → **Create Server**
2. Pick a provider (DigitalOcean, Hetzner, Vultr, AWS, etc.)
3. Choose a region close to your users (for Saudi Arabia: Frankfurt, Bahrain, or Singapore)
4. Server size: **2 GB RAM minimum** (Filament + queue worker + Vite build comfortably fit). 4 GB recommended.
5. PHP version: **8.3**
6. Database: **MySQL 8**
7. Click **Create Server** → wait ~5–10 minutes

Forge will send you the root/sudo credentials and the database root password by email. Save them.

---

## 3. Forge: create the site

### 3.1 — Site basics

1. In your Forge server dashboard → **Sites** → **New Site**
2. **Root Domain**: `almalki.sa`
3. **Aliases**: `www.almalki.sa`
4. **Project Type**: `General PHP / Laravel`
5. **Web Directory**: `/public` (Forge's default — leave it)
6. **PHP Version**: 8.3
7. Click **Add Site**

### 3.2 — Install the repository

1. Open the new site → **Apps** → **Git Repository**
2. **Provider**: GitHub (authorize if first time)
3. **Repository**: `HSMHM/hassan-v2`
4. **Branch**: `main`
5. **Install Composer Dependencies**: ✅ checked
6. Click **Install Repository**

Forge will clone the repo, run `composer install --no-dev --optimize-autoloader`, and set correct file permissions.

### 3.3 — Environment variables

1. Site dashboard → **Environment** tab
2. Forge pre-fills a template — replace the entire thing with this (adjust values):

```env
APP_NAME="Hassan Almalki Portfolio"
APP_ENV=production
APP_KEY=                            # will be generated in step 3.5
APP_DEBUG=false
APP_URL=https://almalki.sa

APP_LOCALE=ar
APP_FALLBACK_LOCALE=ar
APP_FAKER_LOCALE=ar_SA

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hassan_portfolio
DB_USERNAME=forge
DB_PASSWORD=                        # Forge auto-fills this from server creation

# Cache + session: file driver is faster than database for small apps
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue: keep database unless you provision Redis on the server
QUEUE_CONNECTION=database

# Mail (Gmail SMTP with app password — rotate the password first)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=hassan@almalki.sa
MAIL_PASSWORD=your-rotated-gmail-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hassan@almalki.sa
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"

# Integrations — fill the real rotated values, leave blank if not used yet
GOOGLE_SHEETS_WEBHOOK_URL=
GOOGLE_SHEETS_SECRET=
INDEXNOW_KEY=

# News automation (Step 4 of the original task spec)
ANTHROPIC_API_KEY=
ANTHROPIC_MODEL=claude-sonnet-4-6-20250514
ANTHROPIC_MAX_TOKENS=4096

TWITTER_API_KEY=
TWITTER_API_SECRET=
TWITTER_ACCESS_TOKEN=
TWITTER_ACCESS_TOKEN_SECRET=
TWITTER_BEARER_TOKEN=

INSTAGRAM_APP_ID=
INSTAGRAM_APP_SECRET=
INSTAGRAM_ACCESS_TOKEN=
INSTAGRAM_ACCOUNT_ID=

LINKEDIN_CLIENT_ID=
LINKEDIN_CLIENT_SECRET=
LINKEDIN_ACCESS_TOKEN=
LINKEDIN_PERSON_URN=

SNAPCHAT_CLIENT_ID=
SNAPCHAT_CLIENT_SECRET=
SNAPCHAT_ACCESS_TOKEN=
SNAPCHAT_ORGANIZATION_ID=
SNAPCHAT_PROFILE_ID=

WHAPI_API_TOKEN=
WHAPI_BASE_URL=https://gate.whapi.cloud
WHATSAPP_OWNER_PHONE=966596966667

NEWS_DISCOVERY_ENABLED=false
NEWS_DISCOVERY_INTERVAL_HOURS=6
```

3. Click **Save**

### 3.4 — Create the MySQL database

1. Server dashboard → **Database** tab
2. **Database Name**: `hassan_portfolio`
3. **User**: `forge` (default) — or create `hassan` with its own password
4. Click **Add Database**
5. Copy the generated password back into the site's `DB_PASSWORD` in step 3.3 if needed

### 3.5 — Generate the app key

1. Site dashboard → **Commands** tab
2. Run:
```bash
php artisan key:generate --force
```
3. This writes `APP_KEY=base64:...` to the site's `.env` on the server

### 3.6 — First-time setup: migrations + seed + storage link

Still in **Commands** tab:
```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

Then build the frontend:
```bash
cd $FORGE_SITE_PATH
npm ci
npm run build
```

And warm all caches:
```bash
php artisan optimize:all
```

---

## 4. Deploy script (runs on every push)

1. Site dashboard → **Deployments** tab
2. Replace the default **Deploy Script** with this:

```bash
cd $FORGE_SITE_PATH

git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Frontend build
npm ci
npm run build

# Laravel
( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

$FORGE_PHP artisan migrate --force
$FORGE_PHP artisan optimize:all          # config/route/view/event/icons cache + warm settings
$FORGE_PHP artisan queue:restart          # signal queue workers to reload new code
```

3. Click **Save**

---

## 5. Enable auto-deploy on `git push`

1. In **Deployments** tab → toggle **"Quick Deploy"** to **ON**
2. Forge installs a GitHub webhook automatically — every push to `main` triggers the deploy script above
3. Watch deploys under **Deployments** → live log output per run

Manual deploy at any time: click **Deploy Now** button.

---

## 6. SSL certificate (Let's Encrypt, free)

1. Site dashboard → **SSL** tab
2. Pick **LetsEncrypt**
3. **Domains**: `almalki.sa`, `www.almalki.sa`
4. Click **Obtain Certificate**
5. Forge configures Nginx + auto-renews every 60 days
6. Verify: visit `https://almalki.sa` → padlock icon, HTTPS enforced

---

## 7. Queue worker daemon

Mail (contact form) and any news automation jobs go through a queue. Forge runs the worker as a supervised daemon.

1. Site dashboard → **Queue** tab
2. Click **New Worker**
3. **Connection**: `database`
4. **Queue**: `default`
5. **Processes**: `1` (bump to 2–3 if traffic grows)
6. **Max Tries**: `3`
7. **Backoff**: `60`
8. **Timeout**: `300`
9. Leave **Sleep**, **Balance** at defaults
10. Click **Create**

Forge supervises the worker with Supervisor — it auto-restarts on crash and reloads on `queue:restart` (called by the deploy script).

---

## 8. Scheduler (cron for `schedule:run`)

News discovery (if enabled) + any future scheduled jobs run via Laravel's scheduler.

1. Server dashboard → **Scheduler** tab
2. Click **New Cron Job**
3. **User**: `forge`
4. **Frequency**: Every Minute
5. **Command**:
```bash
php /home/forge/almalki.sa/artisan schedule:run >> /dev/null 2>&1
```
(Replace `almalki.sa` with whatever path Forge uses for your site — it's shown in the site's **Meta** tab as "Site Root Path")

6. Click **Save**

Laravel checks every minute whether any scheduled task is due to run.

---

## 9. Post-deploy checks

After the first successful deploy, verify:

- [ ] `https://almalki.sa/` → 200, hero renders with logo + animated job titles
- [ ] `https://almalki.sa/en` → English version loads, layout flips LTR
- [ ] `https://almalki.sa/sitemap.xml` → valid XML with ~44 URL entries
- [ ] `https://almalki.sa/robots.txt` → served by the Laravel route (blocks `/proposals`)
- [ ] `https://almalki.sa/admin/login` → Filament login page in dark theme
- [ ] Log in with the admin account → dashboard loads → "Site Settings" page appears under System group
- [ ] Change the phone number in Site Settings → save → refresh frontend → new number shows in footer
- [ ] Contact form submits successfully (test email arrives)
- [ ] Article/Portfolio/Workshop slider animations play on the home page
- [ ] Switching language via the header button updates `<html lang>` and `<html dir>` and flips the layout

---

## 10. Ongoing workflow (after initial setup)

Day-to-day from your local machine:

```bash
# Make changes locally
# ... edit files ...

# Stage + commit
git add .
git commit -m "fix: improve hero animation"

# Push
git push origin main
```

Forge's webhook triggers within ~5 seconds, the deploy script runs (~1–3 minutes for a typical deploy), and the new code is live. Watch the deploy log in Forge's **Deployments** tab.

If you need to roll back:
```bash
# Locally
git revert <commit-sha>
git push origin main
```
(Forge auto-deploys the revert.)

---

## 11. Useful commands on the server (SSH)

Forge gives you an SSH key — connect with:
```bash
ssh forge@<server-ip>
cd ~/almalki.sa
```

Once inside the site directory:

```bash
# Tail Laravel logs in real time
tail -f storage/logs/laravel.log

# Re-run migrations only
php artisan migrate --force

# Re-seed site settings (if you add new defaults)
php artisan db:seed --class=SiteSettingsSeeder --force

# Clear every cache manually
php artisan optimize:all --clear

# Re-build all caches
php artisan optimize:all

# Tinker — inspect data
php artisan tinker

# Check queue workers
php artisan queue:work --once      # process one job and exit
php artisan queue:failed           # list failed jobs
php artisan queue:retry all        # retry failed jobs

# Manually trigger news discovery (if configured)
php artisan news:discover --sync

# Test a social platform (once you've filled env credentials)
php artisan news:test-platform twitter
```

---

## 12. Backup strategy

Forge offers native backups for databases:

1. Site dashboard → **Backups** tab
2. Configure daily MySQL backups to S3 / DigitalOcean Spaces / Backblaze B2
3. Retention: 30 days

For file uploads (`public/uploads/`), either:
- Mirror to S3 via `spatie/laravel-backup` package, or
- Use your server provider's snapshot feature (DigitalOcean/Linode daily snapshots)

---

## 13. Monitoring (optional but recommended)

- **Uptime**: [UptimeRobot](https://uptimerobot.com) (free) — ping `/up` every 5 min
- **Errors**: [Sentry](https://sentry.io) free plan — `composer require sentry/sentry-laravel`
- **Logs**: [Laravel Pail](https://github.com/laravel/pail) for local tailing, or Papertrail for remote

---

## 14. Troubleshooting

| Symptom | Fix |
|---|---|
| 500 error after deploy | SSH to server → `tail -100 storage/logs/laravel.log` |
| "Class not found" after pulling new code | `composer dump-autoload && php artisan optimize:all` |
| New env variable not picked up | `php artisan config:clear && php artisan optimize:all` |
| `npm run build` fails on Forge with "Cannot find module" | `rm -rf node_modules package-lock.json && npm install && npm run build` |
| File upload fails with permissions error | `chmod -R 775 storage bootstrap/cache public/uploads && chown -R forge:www-data storage bootstrap/cache public/uploads` |
| Queue jobs stuck | Site → Queue tab → click "Restart" on each worker |
| SSL renewal failed | Site → SSL → click "Clone Existing" or re-issue |
| 413 Request Entity Too Large on image upload | Edit nginx config in Forge: `client_max_body_size 20M;` |
| Filament admin blank page | `php artisan filament:upgrade && php artisan optimize:all` |
| Changed site setting but frontend still shows old value | `php artisan cache:clear` (or Forge "Commands" tab) |

---

## 15. Zero-downtime deploys (optional, later)

Once the site is stable, enable Forge's zero-downtime deploys:

1. Site dashboard → **Meta** tab → toggle **Zero Downtime Deploys** ON
2. Forge creates a `current` symlink pointing to the latest release. Rollback becomes instant.
3. Update the deploy script so long-running commands (migrations, etc.) reference `$FORGE_DEPLOY_PATH` instead of `$FORGE_SITE_PATH` when needed. Forge's docs walk through this.

---

## Quick reference — complete command sequence

### First time (on local machine)
```bash
cd c:/laragon/www/hassan-v2
git init
git branch -M main
git add .
git commit -m "Initial migration to Laravel + Inertia + Filament"
git remote add origin git@github.com:HSMHM/hassan-v2.git
git push -u origin main
```

### First time (on Forge, via site Commands tab)
```bash
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
npm ci && npm run build
php artisan optimize:all
```

### Every subsequent deploy
```bash
# local only — Forge does the rest via webhook
git add .
git commit -m "..."
git push origin main
```

### Emergency rollback
```bash
git revert HEAD
git push origin main
```

### Rebuild everything after a broken deploy (SSH to server)
```bash
cd ~/almalki.sa
git fetch && git reset --hard origin/main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize:all
php artisan queue:restart
sudo service php8.3-fpm reload
```

---

**Deploy credentials to keep in a password manager:**
- Forge account
- GitHub (2FA on, SSH key for deploys)
- Server root SSH key (Forge generated)
- `.env` values (Anthropic, Twitter, Instagram, LinkedIn, Snapchat, Whapi, Gmail app password)
- Filament admin password (change from the seeded default immediately)
- DB root password
