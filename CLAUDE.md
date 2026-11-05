# CLAUDE.md — Project instructions for AI agents

Read `HANDOFF.md` fully before changing anything. It is the single source of truth for this project.

## What this is

Certificate in Cyber Incident First Response (CCFR) — a free 60-hour NSQF Level 4 cyber-safety certification by CyberPeace Foundation × SAKEC, Mumbai. Built on Moodle 5.3 in Docker. 9 modules with interactive lessons, SCORM assessments, multilingual support (en/hi/mr/gu), video lessons, certificates.

## Project location

- **Local dev:** `D:\csfr-lms` (Docker Compose — `docker compose up -d`)
- **GitHub:** https://github.com/storm756/csfr_Project (branch: main)
- **VPS:** Hostinger KVM 2 (Ubuntu) — see `VPS-DEPLOY.md` for setup guide
- **Landing page (live):** https://storm756.github.io/csfr_Project/ (GitHub Pages, auto-deploys from `landing/`)

## Key files

| File | Purpose |
|------|---------|
| `HANDOFF.md` | **READ FIRST.** Full project architecture, content pipeline, conventions, gotchas |
| `CHANGELOG.md` | All changes, newest first. **You MUST add an entry for every change you make** |
| `VPS-DEPLOY.md` | Step-by-step VPS deployment guide (Hostinger KVM 2, Ubuntu, Docker, Nginx, SSL) |
| `docker-compose.yml` | Container definitions (web, db, landing, mail) |
| `.env` | Secrets (DB passwords, admin creds). **NEVER commit.** Never print values. Read inside commands only |
| `deploy-custom.sh` | Deploys custom theme/plugin code into the running container |
| `START-SITE.bat` | One-click local start (Windows) |

## Directory structure

- `custom/local/csfr/` — Moodle local plugin (dashboard, handouts, videos, language switching)
- `custom/theme/csfr/` — Moodle theme (parent: boost). SCSS in `scss/pre.scss` and `scss/post.scss`
- `setup/` — Build & content scripts (mounted read-only in container at `/opt/csfr-setup`)
- `setup/scorm/` — SCORM assessment packages (9 module assessments + final)
- `setup/i18n/` — Translation data (ui.json, booklet_i18n.json, translate/ CSVs)
- `setup/files/` — Source media (PDFs, video thumbnails/metadata). Large files (.mp4, .pdf) are gitignored
- `landing/` — Static landing page (HTML/CSS/JS, deployed to GitHub Pages)

## How to make changes

### Landing page
Edit files in `landing/`, commit, push to main. GitHub Pages auto-deploys in ~1 minute via `.github/workflows/pages.yml`.

### Moodle theme/plugin (custom/ directory)
1. Edit files in `custom/`
2. Run `./deploy-custom.sh` (compiles SCSS, copies into container)
3. Purge caches: `docker exec -u www-data csfr-lms-web-1 php /var/www/html/admin/cli/purge_caches.php`

### Content (lessons, assessments, translations)
Run setup scripts inside the container:
```bash
MSYS_NO_PATHCONV=1 docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/<script>.php
```
Key scripts: `lessons.php` (refresh lesson content), `build.php` (full rebuild), `setup/scorm/import_all.php` (reimport assessments).

### Moodle config
Container config at `/var/www/html/config.php`. For wwwroot/SSL changes:
```bash
docker exec csfr-lms-web-1 sed -i "s|OLD_URL|NEW_URL|" /var/www/html/config.php
```

## Current deployment status (as of 2026-11-05)

- **Cloudflare tunnel** was used for the AICTE demo (temporary, laptop-dependent)
- **VPS (Hostinger KVM 2)** has been purchased for permanent deployment
- The VPS needs to be set up — see `VPS-DEPLOY.md` for the full guide
- Decision needed: deploy with Moodle on VPS, or explore alternatives. The team should decide based on what's best for 300-400 users in batches

## Rules

- `.env` must NEVER be committed. Never print credentials.
- Keep everything on `D:\` locally
- Don't run Docker Desktop control commands without asking the user
- Teammates own assessment + translation content; the user does security audit
- Append every change to `CHANGELOG.md` (date, what, which files, why), commit and push
- Do not rewrite git history
- If you change how something works, update `HANDOFF.md` in the same commit
- scssphp compiles `grid-area: 1 / 1` as division — use `grid-row`/`grid-column` instead
- Content scripts in page activities DO run (noclean is on for mod/page)
- Stable handles for activities: `csfr_check1..9`, `csfr_practice1..9`, `csfr_assess1..9`, `csfr_final`

## Pending work (see HANDOFF.md §8 for details)

1. VPS deployment (Hostinger KVM 2 — Ubuntu, Docker, Nginx, SSL, domain)
2. Lesson-text translations (hi/mr/gu) — CSVs with teammates, loader already wired
3. Film voice-overs (hi/mr/gu) — teammate video job
4. Translated booklet/handout PDFs
5. Native-speaker review of all translations
6. Post-demo: rebuild with 405-item question bank (`build.php`)
