# Changelog

Newest at the top. **Every agent/person: add an entry for each change — date, what, which files, why — then
commit and push.** See `HANDOFF.md` for the full project picture.

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
