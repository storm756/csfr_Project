# Changelog

Newest at the top. **Every agent/person: add an entry for each change — date, what, which files, why — then
commit and push.** See `HANDOFF.md` for the full project picture.

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
