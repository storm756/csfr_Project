# CSFR / CCFR — Project Handoff

**Certificate in Cyber Incident First Response (Pilot 2026)** — a free NSQF-Level-4 cyber-first-responder
certificate built on **Moodle 5.3**, by CyberPeace Foundation × SAKEC, Mumbai. This file is the single source of
truth for anyone (human or AI agent) continuing the work. Read it fully before changing anything.

> **For AI agents continuing this project:** append every change you make to `CHANGELOG.md` (newest at top, with
> date, what you changed, which files, and why), then commit and push. Do not rewrite history. Keep this HANDOFF
> accurate — if you change how something works, update the relevant section here in the same commit.

---

## 1. What this is

A Moodle course (course id **14**, shortname **CSFR-PILOT-2026**) that teaches 9 learning modules + a capstone +
final assessment. Everything is themed to look like the HOD's printed handbook (cream paper, navy ink, four
accent colours). The whole thing runs locally in Docker for the demo. Content is English with a Hindi / Marathi /
Gujarati layer in progress.

## 2. How it runs (local demo)

- **Stack:** Docker Compose. `docker compose up -d` from `D:\csfr-lms`. Containers: `csfr-lms-web-1` (Moodle/PHP),
  `csfr-lms-db-1` (MariaDB), `csfr-lms-landing-1` (the landing page), `csfr-lms-mail-1` (Mailpit).
- **URLs:** Moodle = **http://localhost:8000**, landing page = **http://localhost:8090**. (Port 8080 is NOT used.)
- **One-click start:** `START-SITE.bat` on the Desktop (opens Docker-started site).
- **Moodle core** lives inside the web container at `/var/www/html/public` (Moodle 5.3 puts the app under
  `public/`; CLI scripts are at `/var/www/html/admin/cli/...`). Core is NOT in this repo.
- **Deploy custom code:** `./deploy-custom.sh` — compiles the theme SCSS (fails loudly on errors) and copies
  `custom/` into the container. Run it after any theme/plugin change. Then usually
  `docker exec -u www-data csfr-lms-web-1 php /var/www/html/admin/cli/purge_caches.php`.
- **Run a setup script:** `MSYS_NO_PATHCONV=1 docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/<script>.php`
  — `setup/` is mounted **read-only** in the container as `/opt/csfr-setup`.
- **Secrets:** `.env` (NOT in git). Keys: `MOODLE_ADMIN_USER/PASSWORD`, `DEMO_USER_PASSWORD` (all demo learners +
  `demo.lead` share this), DB passwords. Never print these; read them inside a command only.

## 3. Repository map

```
custom/
  local/csfr/      Local plugin: handout.php (booklet/PDF page images + text), video.php (streams films),
                   db/events.php + classes/language.php (map profile language -> user.lang), version.php
  theme/csfr/      Theme (parent: boost). scss/pre.scss (tokens, $course-content-maxwidth=1500),
                   scss/post.scss (all component CSS: lessons, gate bar, helpline strip, booklet text, fonts),
                   classes/hook_callbacks.php (helpline strip), config.php, version.php
setup/             Build + content scripts (run inside the container as /opt/csfr-setup). See section 4.
  files/           SOURCE MEDIA (NOT in git): booklet.pdf (303pp), modules/m1-11.pdf, videos/m1-9.mp4+jpg+json,
                   participant-handbook.pdf. booklet.pdf is the master; derived JSON below is committed.
  i18n/            Translation data (committed): ui.json (framework labels hi/mr/gu), booklet_i18n.json
                   (teammate booklet translations), module_i18n.json (lesson-text translations, when returned),
                   translate/ (teammate CSV template exports)
  scorm/           SCORM assessment pipeline: src/m01..m09,m11 (packaged teammate assessments), import_*.php,
                   grades.php, gating.php
landing/           Static landing page (index.html). media/ (mp4) NOT in git.
docker/, docker-compose.yml, deploy-custom.sh, shots.mjs (headless screenshot tool)
booklet_text.json, bank_booklet.json, modules.php, modules_ext.json  <- derived/authored content (committed)
```

## 4. The content pipeline (setup scripts)

- **`modules.php`** — modules 1-9 data (title, tagline, theory, outcomes, what, meet, how, blocks, ask, callout,
  stop/save/report, protect, rules, remember, myths, game, story) from the teammates' module PDFs.
- **`modules_ext.json`** — pages 4-8 per module (steps, cards, lab, cases) parsed by `tools/parse_modules.py`.
- **`booklet.php`** — which booklet pages each module may show + topic page ranges.
- **`booklet_text.json`** — the booklet turned into clean web text (made by `scratchpad/booktext.mjs`, pdftotext +
  a parser). Block types: h, p, ul, kv, cols, ssr, case, remember. Rendered by `csfr_booklet_blocks()`.
- **`bank_booklet.json`** — 405 MCQ items (45/module) parsed from the booklet question bank.
- **`lessonlib.php`** — the lesson builder. Key functions: `csfr_lesson()`, `csfr_topics()` +
  `csfr_booklet_blocks()` (booklet web text), `csfr_l10n()` (per-language variants — see section 7),
  `csfr_gate_script()` (the Cisco-style gate — see section 6), crossword, flip cards, stepper, rubric, capstone.
- **`build.php`** — full course build from scratch. **`lessons.php`** — refresh the 9 live lessons + capstone
  content WITHOUT a rebuild (use this for content/theme iteration; it re-runs csfr_lesson + csfr_l10n + gate).
- **`practice.php` / `practicelib.php` / `assessments.php`** — the graded practice quizzes, the 11-criterion
  practical rubric, the hidden station pack.
- **`videolib.php` / `videos.php`** — the module films (poster + transcript, streamed via local/csfr/video.php).

## 5. What is built and LIVE

- **9 module films** (lemo/opuscar pipeline at `D:\tools\opuscar`, edge-tts voices), on the lessons with posters
  + transcripts, streamed from the plugin.
- **9 interactive lessons** (modules 1-9) + capstone + the booklet as **sharp web text** (the "Learn it: topics"
  section) with the original page one tap away. Brighter handbook UI.
- **Web handout language switch:** the readable web-text body of Handouts 1–9 now uses the already-reviewed
  booklet translations when Moodle is set to Hindi, Marathi or Gujarati. `handout.php` reads the selected Moodle
  language; it does not need or expose a third-party translation API key. Overview metadata, checks and SCORM remain
  separate work items.
- **Course-index language switch:** Modules 1–9 labels now also switch in the Moodle course page, breadcrumb,
  activity header and left course drawer through a narrow CSFR theme layer. It is display-only: activity URLs,
  completion rules, scores and grades retain their original Moodle records.
- **Assessments = the teammates' interactive games as graded SCORM** (section 6).
- **Assessment presentation:** every single-SCO assessment uses the full course canvas, with Moodle's redundant TOC
  hidden (`setup/scorm/presentation.php` + theme SCSS). This is a presentation change only; do not remove the SCORM
  player/bridge, because it records score, pass/fail and completion.
- **Dashboards / Programme-admin page, batches, certificate** (customcert).
- **Multilingual layer** (section 7): Moodle UI, framework labels, full booklet text, and the gate bar are in
  hi/mr/gu. (Module-authored lesson text + films are not yet — section 8.)
- **UI:** content widened to fill wide screens (`$course-content-maxwidth: 1500px`); lessons are a centred reading
  column (`#page-mod-page-view`); the 1930 helpline strip shows only inside courses and is dismissible
  (hook_callbacks.php + .csfr-helpstrip, keyed off `$PAGE->pagelayout` because `$PAGE->course` isn't set that early).

## 6. Assessments + Cisco-style gating (the big recent build)

- Each module's assessment is your teammate's standalone HTML/JS game, wrapped as a **SCORM 1.2 package** so the
  score reaches Moodle's gradebook. Source in `setup/scorm/src/mNN/`; `js/scorm-api.js` is a bridge that finds the
  LMS API and reports `cmi.core.score.raw` + pass/fail (a safety-gate choice forces "failed"). A one-line hook in
  `js/engine.js` calls `window.CCFR_scorm(pct, gates)` on the result screen.
- Imported by `setup/scorm/import_m01.php` (M1) and `import_all.php` (M2-9 + final). idnumbers: `csfr_assess1..9`,
  `csfr_final`. Grade /100, highest attempt, completion = "passed".
- **Gradebook** (`setup/scorm/grades.php`): 9 module assessments = **50% total (equal, ~5.56% each)**, final = 50%,
  everything else 0%. Course total shows as a percentage. **Natural aggregation, flat, per-item weight overrides**
  (`weightoverride=1`, `aggregationcoef2`). Verified: 80 on M1 only = 4.44%; full marks = 100%.
  **NB:** Moodle 5.x stores SCORM tracking in `scorm_attempt` / `scorm_scoes_value` (NOT the old
  `scorm_scoes_track` — that table doesn't exist; grades are in `grade_grades`).
- **Gating** (`setup/scorm/gating.php` + `csfr_gate_script()` in lessonlib):
  - Lessons = **manual completion**. A fixed bottom progress bar (`.csfr-gate`) tracks every flip card, tab,
    stepper, lab, self-check and myth/game choice (scoped to the visible language variant) + scroll-to-end (an
    IntersectionObserver + capture-phase scroll on a `.csfr-gate__end` sentinel — Moodle's drawers layout scrolls
    an inner element, so `window.scrollY` alone does NOT work).
  - "Finish lesson" calls `core_completion_update_activity_completion_status_manually` -> marks the lesson complete.
  - Access restrictions (`availability` JSON, completion condition): assessment locked until its lesson complete;
    module N+1 lesson locked until module N assessment passed; final locked until M9 passed.
  - Verified end-to-end with a real learner (engage -> finish -> assessment unlocks).

## 7. How multilingual works (important + non-obvious)

- Moodle's multilang filter breaks on nested spans, so it is NOT used. Instead `csfr_l10n($html)` renders the
  lesson **once per language**, each wrapped in `<div lang="xx" class="csfr-l10n">`; `post.scss` shows only the
  variant matching `<html lang>` (English fallback). Radio/checkbox ids get a `-hi/-mr/-gu` suffix so the CSS-only
  controls in each copy stay independent.
- Translation is done by **text replacement**: `csfr_l10n` builds a map `{English -> translated}` per language from
  `ui.json` (framework labels), `booklet_i18n.json` (booklet) and `module_i18n.json` (lesson text), then replaces
  matching text nodes. **So translations are keyed by the exact English string.** To add translations, fill the
  worksheets (English column unchanged), parse to the right JSON, re-run `lessons.php`.
- Language packs hi/mr/gu installed; `langmenu=1`, `langlist=en,hi,mr,gu`. The local_csfr plugin strings have
  draft hi/mr/gu files. The gate bar strings live in `csfr_gate_script()` keyed by `document.documentElement.lang`.
- **Gotcha:** `demo.learner` (Asha)'s profile language is Marathi — a UI language switch is expected for her.

## 8. What is PENDING (hand to other agents / teammates)

1. **Lesson-text translation** — 1,643 strings (~123K chars), exported as per-module CSVs in
   `Desktop/lesson-text-translation-files/` for the teammates. When returned: parse (English->hi/mr/gu) to
   `setup/i18n/module_i18n.json` (same shape as booklet_i18n.json), then `lessons.php`. Loader is already wired.
2. **Films** — Hindi/Marathi/Gujarati voice-overs + on-screen text (teammate video job). Drop `mN-<lang>.mp4` in
   `setup/files/videos/`; the plugin can serve per-language once wired.
3. **Booklet / handout PDFs** — translated PDFs per language, same page order, in `setup/files/`.
4. **Native-speaker review** of ALL machine/draft translations before go-public (safety-critical wording).
5. **Standalone Handout page -> web text** — completed for Modules 1–9 in `local/csfr/handout.php`, using
   `booklet_text.json` with handbook-style responsive CSS. The original pages remain behind an expandable reference
   section and the printable PDF remains available. Modules 10–11 still use the original pages because equivalent
   structured text is not available; do not infer it from assessment material. Learners are excluded from the Module
   1 and 2 trainer answer-key pages (booklet pp.43 and 70); course staff may still view them. Every web handout
   now begins with a learner-facing module overview (hours, plain-language outcomes and topic list), rather than
   standards mapping or trainer-facing planning content.
6. After the demo, a full `build.php` rebuild switches the module checks to the 405-item booklet bank and the
   improved attempt simulator (deliberately left off so demo dashboards don't break).

## 9. Conventions, gotchas, tooling

- **scssphp** compiles `grid-area: 1 / 1` as a division — use `grid-row`/`grid-column`.
- **Screenshots:** `shots.mjs` (headless Chrome via CDP). `SHOT_USER` + `SHOT_PASS` env log in; supports `--eval=`,
  `--mobile`, `--dark`. Output to `.hallmark/shots/` (not in git).
- **idnumbers** are the stable handles: `csfr_check1..9`, `csfr_practice1..9`, `csfr_assess1..9`, `csfr_final`.
- **Content scripts** in page activities DO run (noclean is on for mod/page) — the crossword and the gate bar rely
  on this.
- Keep everything on `D:`. Don't run Docker Desktop control commands without asking the user. Teammates own the
  assessment + translation content; the user does the security audit.
