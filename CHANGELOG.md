# Changelog

Newest at the top. **Every agent/person: add an entry for each change — date, what, which files, why — then
commit and push.** See `HANDOFF.md` for the full project picture.

## 2026-11-04 — Keep the landing triage interaction on the first page (Codex)
- **Landing first screen:** moved the existing “Something just happened. What first?” interactive triage panel into
  the hero flow in `landing/index.html` and adjusted `landing/styles.css`. The partner-logo row, title, statistics and
  actions stay first; the practical interaction now follows them in the same first-page section instead of appearing
  as a disconnected second section. All later landing sections were left unchanged.

## 2026-11-04 — Landing page redesign for AICTE meeting (Claude, Opus 4.6)
- **Hero overhaul:** replaced the dark split-layout hero with a clean white centered layout. All four partner
  logos (AICTE, SAKEC, NCSRC, CyberPeace) displayed large and centered at the top. Title, description, stats
  and enrol button all fit in one viewport without scrolling. Removed the "CyberPeace x SAKEC" wordmark from
  navbar, simplified to just "CCFR".
- **Triage widget relocated:** moved the interactive incident-triage control out of the hero into its own
  band section below the fold, styled as a dark card on a light band background.
- **"What you will get" section:** replaced the old "Your certificate" heading. Added three highlight cards:
  Joint Certificate, 2 Academic Credits (NSQF Level 4), and Qualification Title. Below the cards, a certificate
  image slideshow auto-rotates every 3 seconds with manual dot navigation (both front and back of certificate).
- **Partner logos and people:** updated "Who runs it" section with the four new logos (AICTE, SAKEC, NCSRC,
  CyberPeace) in a 4-column grid, replacing old SAKEC/CyberPeace-CoE/NTRO logos. Added Maj. Vineet Kumar's
  actual photo, replacing the "VK" monogram placeholder.
- **Removed sections:** helpline banner ("Lost money? Call 1930"), free two-hour workshops section,
  "A QR code never pays you" advisories section.
- **Footer:** added the four partner logos, removed advisories link, simplified wordmark.
- **New assets:** `landing/img/` now includes aicte.webp, sakec-logo.webp, ncsrc.webp, cyberpeace-logo.webp,
  vineet-kumar-photo.webp, cert-front.jpg, cert-back.jpg.
- **Files:** `landing/index.html`, `landing/styles.css`, 7 new images in `landing/img/`.

## 2026-11-04 — Custom assessment viewer, bypass SCORM player (Claude, Opus 4.6)
- **Custom assess.php:** created `custom/local/csfr/assess.php`, a PHP wrapper that serves the teammate's
  HTML/JS assessment content as full-page Moodle content under the embedded layout, without the SCORM player
  chrome (no iframe, no "Review mode", no TOC toggle, no "All modules"/"Theme" buttons). Requires Moodle login,
  validates module param against a whitelist, serves sub-resources (CSS/JS/images) through a `?f=` parameter
  with security checks (realpath must be within the assess directory). Rewrites relative paths in the HTML.
- **Assessment source files:** copied all 10 SCORM source directories (m01-m09, m11) to
  `custom/local/csfr/assess/` for direct serving. Each contains index.html, css/ccfr.css, js/scorm-api.js,
  js/engine.js, js/questions.js, modules.js.
- **SCORM height reporter:** added a postMessage-based height reporter script to all 10 SCORM source HTML files
  (`setup/scorm/src/m01-m09,m11/index.html`) and created `setup/scorm/update_packages.php` to re-package and
  re-parse SCORM packages in Moodle after source changes.
- **SCORM player CSS:** extended `custom/theme/csfr/scss/post.scss` with rules to hide Review mode heading,
  TOC toggle, page header, fixed heights, and navigation chrome on the SCORM player page.
- **Moodle left panel cleanup:** added course-index drawer CSS in `post.scss` to remove completion checkboxes,
  collapse toggles, and activity type icons for a cleaner NetAcad-style left panel. Updated
  `hook_callbacks.php` to remove portfolio/viva/certificate activities from the course index.
- **Home page:** removed LinkedIn showcase card and portfolio/viva/certificate cards from `home.php`.
- **Files:** `custom/local/csfr/assess.php` (new), `custom/local/csfr/assess/` (new, 10 module dirs),
  `setup/scorm/update_packages.php` (new), `custom/theme/csfr/scss/post.scss`,
  `custom/theme/csfr/classes/hook_callbacks.php`, `custom/local/csfr/home.php`,
  `custom/local/csfr/classes/learner.php`, `setup/scorm/src/*/index.html` (10 files).

## 2026-11-04 — Add a reproducible Windows startup batch file (Codex)
- **Startup:** added `START-SITE.bat` at the project root. After Docker Desktop is opened, it waits for the Docker
  engine, runs `docker compose up -d`, and opens Moodle and the landing page. Updated `README.md` so the documented
  one-click startup path matches the file that actually exists.

## 2026-11-04 — Add learner-controlled LinkedIn sharing showcase (Codex)
- **Showcase feature:** added a LinkedIn awareness/achievement card to `custom/local/csfr/home.php`, with a
  personalised, editable draft based on the learner's completed hours, a copy button, and a safe external link to
  LinkedIn's share flow. `custom/local/csfr/styles.css` supplies the responsive presentation.
- **Safety and production path:** the demo never requests LinkedIn credentials, holds no access token and never
  publishes automatically. It uses the public cybercrime.gov.in awareness page as the demo share URL. A production
  version needs a registered LinkedIn app, OAuth consent and `w_member_social`, plus a public CSFR/certificate URL
  with share-preview metadata.

## 2026-11-04 — Complete the shared left-drawer labels in three languages (Codex)
- **Left navigation:** extended the CSFR course-label dictionary in `custom/theme/csfr/classes/hook_callbacks.php`
  to include the course title, Collapse all, Start here, its visible orientation activities, attendance and the
  day-one pre-test. This closes the obvious English-only portion above the Module 1–9 navigation; trainer-only
  items remain deliberately outside the learner-facing scope.

## 2026-11-04 — Translate Modules 1–9 course and left-menu labels (Codex)
- **Course navigation:** added the CSFR theme’s small rendered-label language layer in
  `custom/theme/csfr/classes/hook_callbacks.php`. With Hindi, Marathi or Gujarati selected, it translates Module
  1–9 section titles, lesson/handout/practice/check/assessment labels, assessment scenario names, course cards,
  breadcrumbs and the asynchronously loaded left course drawer. It does not alter Moodle records, links, IDs,
  completion, grades or question content.
- **Why this route:** this Moodle installation has neither of its built-in multilingual text filters enabled or
  installed. The layer is restricted to learner-facing course-navigation strings and runs after the drawer loads,
  keeping behaviour reversible and avoiding accidental changes to quiz/lesson body text.

## 2026-11-04 — Make Modules 1–9 web handouts follow the browser language (Codex)
- **Browser multilingual proof:** updated `custom/local/csfr/handout.php` so the readable web-text portion of
  Handouts 1–9 uses the existing reviewed English→Hindi/Marathi/Gujarati booklet map when a learner selects that
  Moodle language. Topic headings, paragraphs, lists, tables, safety steps and case/remember boxes switch on page
  load; no API key, external request, assessment logic or learner data is involved.
- **Scope intentionally visible:** translated handout framing labels were added too. The separate learner-overview
  metadata, original scanned pages, module checks and SCORM assessment packages remain the next multilingual work
  streams, rather than being misleadingly labelled complete.

## 2026-11-04 — Give SCORM assessments a full course-page layout (Codex)
- **Assessment presentation:** added `setup/scorm/presentation.php` and aligned both SCORM import scripts so every
  Module 1–9 assessment and the final assessment hides Moodle's redundant single-item table of contents, uses the
  available course width and gets a tall, responsive player frame. The package itself, SCORM API bridge, attempts,
  grades, pass status and gating are untouched.
- **Theme:** added the matching `#page-mod-scorm-player` layout rules in `custom/theme/csfr/scss/post.scss` to remove
  the cramped embedded-window appearance while retaining the Moodle frame required for safe SCORM tracking.

## 2026-11-04 — Protect pre-multilingual/assessment-layout baseline (Codex)
- **Rollback:** added `.gitignore` protection for local platform snapshots and `BACKUP_AND_ROLLBACK.md` with the
  two-layer rollback process: a pushed Git tag for source and a local Docker/database snapshot for live Moodle
  state. This baseline is created before assessment UI or multilingual changes so those changes can be reversed
  without losing the current platform.

## 2026-11-04 — Add learner module overviews to web handouts (Codex)
- **Handouts:** updated `custom/local/csfr/handout.php` to add a consistent learner-facing overview to all web
  handouts for Modules 1–9: module title, notional hours, plain-language learning outcomes and a collapsible topic
  list. It reuses the existing curated module data and does not render NSQF/NOS mapping, trainer plans, assessment
  materials or answer keys.
- **Presentation:** updated `custom/local/csfr/styles.css` with a responsive teal overview panel that keeps the
  handbook visual system intact on desktop and phones.

## 2026-11-04 — Protect trainer answer keys in handouts (Codex)
- **Access control:** changed `custom/local/csfr/handout.php` so learners cannot view or download Module 1 p.43
  or Module 2 p.70, both explicitly labelled trainer answer keys. The restriction applies to the displayed original
  pages, a direct image request, and the generated handout PDF. Course staff with activity-management access retain
  access for facilitation.
- **Why:** the handout coverage audit found that the full booklet page ranges accidentally exposed these answers to
  normal learners.

## 2026-11-04 — Readable web handouts for Modules 1–9 (Codex)
- **Handouts:** changed `custom/local/csfr/handout.php` to render the curated `setup/booklet_text.json` content as
  selectable, responsive web text for Modules 1–9. Each now keeps an expandable original-page view and the existing
  printable PDF download, so the faithful source layout is never lost.
- **Presentation:** added the handbook-style web-handout layout to `custom/local/csfr/styles.css` (paper surface,
  mustard border, heading hierarchy, responsive tables, stop/save/report cards and callouts). This replaces page
  images as the default reading experience without altering the lesson pages or source PDFs.
- **Scope safeguard:** Modules 10–11 stay on the existing original-page view because equivalent structured text is
  not available; this avoids guessed formatting or accidental exposure of assessment-only material.

## 2026-11-04 — Local stack started (Codex)
- **Operations:** started the local Docker Compose stack from `D:\csfr-lms` at the owner's request, so the Moodle
  learning platform and public landing page can be viewed locally. No application source or configuration was changed.
- **Change-record convention:** this file is now the permanent log for future work; every change will state the date,
  what changed, the affected files or service, and the reason.

## 2026-11-04 — Initial push + multilingual + assessments + gating (Claude, Opus)
- **GitHub:** first push of the whole project to `storm756/csfr_Project`. Added `HANDOFF.md`, this changelog,
  `.gitignore` (excludes `.env`, big media, caches).
- **Assessments → graded SCORM:** packaged the teammates' 9 module games + final as SCORM 1.2
  (`setup/scorm/`), importing as graded activities. Gradebook set to **50% module assessments + 50% final**,
  everything else 0% (`setup/scorm/grades.php`). Verified scores reach `grade_grades`.
- **Cisco-style gating** (`setup/scorm/gating.php` + `csfr_gate_script()` in `setup/lessonlib.php` + `.csfr-gate`
  CSS): lessons need full engagement + scroll-to-end before the assessment unlocks; next module locked until the
  current assessment is passed. Verified end-to-end.
- **Booklet → web text:** the in-lesson "topics" now render sharp text from `setup/booklet_text.json`
  (`csfr_booklet_blocks()`), original page one tap away.
- **Multilingual:** `csfr_l10n()` renders per-language lesson variants; loaded teammate booklet translations
  (`setup/i18n/booklet_i18n.json`, 635 strings ×3) and translated the gate bar. Exported the remaining 1,643
  lesson-text strings to `Desktop/lesson-text-translation-files/` for the teammates; loader wired for
  `setup/i18n/module_i18n.json`.
- **UI:** widened content to fill wide screens; lessons as a centred reading column; helpline strip now
  course-only + dismissible.

(Earlier history — films, module rebuild, brighter UI, practice quizzes, rubric — predates this changelog; see
`HANDOFF.md`.)
