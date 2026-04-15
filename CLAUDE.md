# CLAUDE.md — hassan-v2 (almalki.sa)

Behavioral guidelines for Claude when working on this project. Merges Karpathy-style LLM discipline with project-specific conventions.

**Tradeoff:** These guidelines bias toward caution over speed. For trivial tasks, use judgment.

---

## Project snapshot

- **Stack:** Laravel 13 + Inertia.js + Vue 3 + Filament v5 + Tailwind/custom CSS
- **Primary deploy:** Forge → `/home/forge/almalki.sa/current` (Ubuntu, MySQL, zero-downtime releases)
- **Languages:** Arabic (primary, RTL) + English — bilingual everywhere
- **Automation:** Telegram bot drives news discovery/publishing; Claude API (Haiku by default) generates bilingual content; Whapi, Twitter, Instagram, LinkedIn, Snapchat for distribution

## 1. Think Before Coding

**Don't assume. Don't hide confusion. Surface tradeoffs.**

- State assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them — don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask.
- For exploratory questions ("what could we do about X?"), answer with 2-3 sentences and a recommendation, not a full plan.

## 2. Simplicity First

**Minimum code that solves the problem. Nothing speculative.**

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility"/"configurability" that wasn't requested.
- No error handling for scenarios that can't happen. Trust framework guarantees; only validate at system boundaries.
- No backwards-compatibility shims or feature flags when you can just change the code.
- If you write 200 lines and it could be 50, rewrite it.

Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

## 3. Surgical Changes

**Touch only what you must. Clean up only your own mess.**

When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style — match Laravel conventions and the project's existing patterns, even if you'd personally do it differently.
- If you notice unrelated dead code, mention it — don't delete it.

When your changes create orphans:
- Remove imports/variables/functions that YOUR changes made unused.
- Don't remove pre-existing dead code unless asked.

Every changed line should trace directly to the user's request.

## 4. Goal-Driven Execution

**Define success criteria. Loop until verified.**

Transform tasks into verifiable goals:
- "Add validation" → "Write tests for invalid inputs, then make them pass"
- "Fix the bug" → "Reproduce it, then make it stop happening"
- "Refactor X" → "Ensure existing tests pass before and after"

For multi-step tasks, state a brief plan:
```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
```

For UI/frontend changes, start the dev server and verify in a browser. Type-check and tests verify correctness, not feature-correctness — if you can't test the UI yourself, say so explicitly instead of claiming success.

---

## Project-specific conventions

### Code style & structure
- **No inline styles.** All CSS lives in `resources/css/app.css`.
- **No comments describing WHAT code does** — only WHY when non-obvious. Don't write multi-line comment blocks or multi-paragraph docstrings.
- **No emojis in code or files** unless the user explicitly asks.
- **Bilingual everywhere:** every user-facing string must exist in both AR and EN; check existing patterns (e.g. `title_ar/title_en`, `$isAr = app()->getLocale() === 'ar'`).
- **RTL-safe CSS:** use logical properties (`inset-inline-end`, `padding-inline-start`) not `right`/`left`.

### Laravel patterns
- Services live in `app/Services/*` and are injected, not instantiated.
- Jobs must be idempotent (queue retries are enabled, `tries = 2-3`).
- Filament resources follow the existing structure under `app/Filament/Resources/`.
- Migrations: one concern per migration; never edit a shipped migration — create a new one.
- HTML from AI-generated content must pass through `clean_html()` before render.

### Secrets & credentials
- Never hardcode. Always go through `config()` + `env()`. Add new env vars to `.env.example`.
- Never log API keys, Telegram/Whapi tokens, or personal phone numbers.

### Git & deploy
- **Only create commits when the user explicitly asks** — even after a big change.
- Use HEREDOC for commit messages so multi-line formatting survives.
- Never `git push --force`, never skip hooks, never amend published commits.
- Deploy happens on Forge — assume the user triggers it manually. Don't touch deployment config without asking.
- After server-side changes: remind the user to `npm run build` (Vite manifest) + `php artisan migrate` + `php artisan config:cache` as applicable. Don't auto-run destructive or remote commands.

### AI / Claude usage in the app
- Discovery uses `askWithWebSearch` — expect Haiku to wrap JSON in prose; parse defensively (see `NewsDiscoveryService::extractJson`).
- Prefer Haiku (`claude-haiku-4-5-20251001`) unless quality demands Sonnet — respect rate-limit and cost constraints.
- Every AI call that can fail silently must leave a trail (cache reason, log preview of raw response) so the user can debug from Telegram or logs.

### Image generation (`OgImageService`)
- Uses Intervention Image **v4** — API: `decodePath()`, `insert()`, `scale(height: X)`.
- Arabic text must pass through `shapeArabic()` (ArPHP) before `->text()` or it renders disconnected/reversed.
- Line-spacing: render each line separately with manual Y offsets; GD + Arabic doesn't respect `lineHeight()`.

### Testing & verification
- Local SQLite driver is flaky — test failures from `SQLSTATE[HY000] Connection: sqlite` are pre-existing and not caused by your changes.
- For anything user-visible: verify on the live dev server before reporting done.

---

## When in doubt

Ask. The cost of one clarifying question is tiny compared to a wrong-direction refactor in a production project.
