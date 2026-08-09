# Deploying the apps in this repo

This repo contains **three unrelated apps**. They deploy to different places.

## FeedbackIQ (`app/`, `lib/`, `server/`, ...)

The survey/analytics app described in `CLAUDE.md`. Its feature set (surveys, responses,
analytics) runs entirely client-side (Zustand + AsyncStorage), so it can be deployed as a
static site with no backend/database required.

### Deploy to Vercel

1. Go to [vercel.com/new](https://vercel.com/new) and import this GitHub repo.
2. Vercel will read `vercel.json` at the repo root automatically:
   - `buildCommand`: `pnpm build:web` (runs `expo export --platform web`)
   - `outputDirectory`: `dist`
   - `rewrites`: map the dynamic routes (`/survey/:id`, `/survey/:id/fill`, `/response/:id`)
     to their exported HTML files, since Expo's static export writes those as literal
     `[id].html` files.
3. Leave "Root Directory" as the repo root and "Framework Preset" as "Other" — no
   framework-specific settings needed, `vercel.json` covers it.
4. Deploy. No environment variables are required for the survey features to work.

You can reproduce the production build locally with:

```bash
pnpm install
pnpm build:web     # expo export --platform web -> dist/
npx serve dist      # preview the static build
```

### Notes

- `server/` (tRPC + Drizzle + MySQL + Manus OAuth) is template boilerplate not wired into
  the survey features yet — it is **not** deployed by the Vercel config above. If you later
  wire survey/response data to the real backend, this file and `CLAUDE.md`'s "entirely
  client-side" note both need updating, and you'd deploy `server/` separately (e.g. as a
  Vercel serverless function, or its own Node host) with a real `DATABASE_URL`.
- `metro.config.js` sets `forceWriteFileSystem` to `true` only when `NODE_ENV === "development"`.
  That NativeWind option (fixes iOS dev styling) previously broke `expo export` because Metro
  hashes the CSS cache file mid-write; keeping it dev-only fixes the export without losing the
  iOS workaround.

## MARBOHUB POS (`marbohub-pos/`)

A separate single-file POS/stock app (`index.html` + `api.php`), unrelated to FeedbackIQ. It
needs PHP shared hosting, not Vercel. See `marbohub-pos/README.md` for deploy steps and
security caveats (PIN is hardcoded, `.htaccess` blocks direct access to the data file on
Apache hosts).

## Tiger88 Expense System (`tiger88/`)

A separate PHP + MySQL expense-tracking app (Thai UI, "เสือกินเส้น 88") for
[thetiger.one](https://thetiger.one), unrelated to the other two apps. It runs on cPanel
shared hosting (PHP + MySQL/phpMyAdmin, deployed via File Manager upload) and syncs new
expense rows daily from a published Google Sheet ("Paypers") via a cron-triggered
`sync.php`. Full step-by-step deploy instructions are in `tiger88/README_DEPLOY.md`.

Because this repo is public, the committed copy is sanitized:

- `tiger88/config.php` contains `CHANGE_ME_*` placeholders — real DB credentials, the
  login password hash, the sync secret key, and the Paypers sheet URL must be filled in
  before upload (kept privately, outside git).
- `tiger88/seed_data.json` (real expense records used by `seed_import.php` for the one-time
  initial import) is intentionally **not committed** (see `tiger88/.gitignore`).
