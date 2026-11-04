# CSFR Academy: local demo

A working local copy of the learning platform for the **Community Cyber First Responder** certificate
(CyberPeace Foundation × SAKEC). Everything runs in Docker on this laptop, and all its data lives on D:.
All learners, grades and dates are **dummy data**.

## Open it

| What | Address |
| --- | --- |
| Public landing page (custom front end) | http://localhost:8090 |
| Learning platform (Moodle 5.3 LTS) | http://localhost:8000 |
| Test mailbox (catches every email the site sends) | http://localhost:8025 |
| Sample certificate PDF | `D:\csfr-lms\sample-certificate.pdf` |

**Start:** open Docker Desktop and wait for "Engine running", then double-click `START-SITE.bat` in this folder. It waits briefly for Docker, starts the services, and opens Moodle at http://localhost:8000 and the landing page at http://localhost:8090. The PowerShell equivalent is `cd D:\csfr-lms; docker compose up -d`.
**Stop:** `docker compose stop` (data is kept). Delete everything: `docker compose down -v`.

Commands below are for PowerShell. In Git Bash, put `MSYS_NO_PATHCONV=1 ` in front of each `docker compose exec` line.

## Demo for the HOD (about 10 minutes)

### Before the meeting

1. Start the stack (above), then put the showcase learner back to "consent not yet given":

   ```
   docker compose exec -u www-data web php /opt/csfr-setup/reset-showcase.php
   ```

   Run it again after any rehearsal: once Asha has answered, the consent pages don't show again.
   To prepare Asha as a fully completed showcase learner with dummy marks, practicum records and a certificate, run:
   `docker compose exec -u www-data web php /opt/csfr-setup/demo-showcase-complete.php`
   This changes only `demo.learner`; the certificate data is explicitly demo-only.
2. Open http://localhost:8000 once so the first page in the meeting loads quickly.
3. You'll need two sign-ins at once: a normal Chrome window for the learner and an Incognito window
   (Ctrl+Shift+N) for the programme lead. Moodle keeps one sign-in per browser.

### 1. A learner's first day: sign in as `demo.learner` (normal window)

Asha Desai is a teacher from Mumbai Suburban in Batch 02 (Govandi), part-way through the course.

- **Consent comes first.** She reads three policies, one page each: the DPDP Act 2023 privacy notice, the
  First Responder code of conduct, and the optional research use of anonymised data. Then she answers them on
  one page: the two required ones are ticked, and research is a free yes or no. Nothing else opens until she has answered.
- **My progress** is her home page: about 16 of 60 hours, split into classroom 15, online 15, field
  practicum 24, and portfolio and viva 6. Next to it is her next step. Below are her lessons with module-check
  scores, practicum logs (one verified by her mentor, one waiting), classroom days, and the final assessment weights.
- **Course** (top bar): every module opens with its classroom handout, which is that module's 3 pages from the
  HOD's handbook and opens right in the page. Then come the online lesson and the module check. Modules 4–6 also
  have the extended notes and practice exercises. The handbook's answer key is a hidden trainer resource, so
  learners never see it.

### 2. Programme admin: sign in as `demo.lead` (Incognito window)

Ananya Rao is the programme lead and the privacy officer. She lands on **Programme admin**:

- Four figures: 25 learners, 8 certified, knowledge gain (Batch 01: 43% against the 25% target), and average hours.
- **Where learners are**: how many reached each stage, from enrolled to certified.
- **Batches**: Batch 01 has finished; Batch 02 is mid-course.
- **Learners**: everyone's 60-hour bar, which you can filter by batch, status or name.
- Open **Asha Desai**: her whole record on one page. Under **Consent and data** are the answers she gave a
  minute ago, with today's date. That's the DPDP evidence: who agreed to what, and when.
- Open **Mohammed Ansari** (certified) → **Verify certificate**: the public check, which works without signing in.
- **Download CSV**: the per-learner evidence file for CyberPeace's CSR reporting.

### 3. The public side

- http://localhost:8090, the landing page. Its **Verify a certificate** box takes the code shown on a learner's record.
- If you're asked about access: sign in as `demo.mentor1`, who sees only Batch 01.

## Logins

Passwords are in `D:\csfr-lms\.env` (local test values only, never reuse them on a real server).
Admin uses `MOODLE_ADMIN_PASSWORD`; every `demo.*` account uses `DEMO_USER_PASSWORD`.

| Username | Who | Try this |
| --- | --- | --- |
| `admin` | Site administrator | Everything, including Site administration |
| `demo.lead` | Ananya Rao: programme lead, privacy and grievance officer | Programme admin, data requests |
| `demo.learner` | Asha Desai: showcase learner, Batch 02 | First login with the consent pages, then My progress |
| `demo.trainer1`, `demo.trainer2` | Trainers, Batch 01 and Batch 02 | Take attendance, grade |
| `demo.mentor1`, `demo.mentor2` | Mentors (Tier 3), Batch 01 and Batch 02 | Verify practicum logs; each sees **only** their own batch |
| `demo.assessor` | Independent assessor | Grade the practical, portfolio and viva |
| `demo.b01.learner01` … `learner12` | Batch 01 learners (finished; 8 certified) | Progress, grades, certificate |
| `demo.b02.learner01` … `learner12` | Batch 02 learners (mid-course) | What a learner sees part-way |

Only Asha's first login shows the consent pages. The other demo accounts have already answered, and about 70%
of them said yes to the optional research use.

## What the brief asks for, and where it is

| Brief requirement | In this demo |
| --- | --- |
| 2-credit, 60-hour blended programme | Course with Start here + 11 modules + practicum + portfolio/viva + certificate |
| Course content | Each module's 3 pages from the HOD's handbook as an in-page PDF; the full handbook (without the answer key) in Start here; extended notes for Modules 4–6 |
| 15 h online self-learning | One online lesson + module check per module (lessons condensed from the handbook) |
| Pre/post assessment, ≥25% knowledge gain | Day 1 pre-test and final MCQ (same 25 handbook questions); gain per batch in Programme admin |
| Assessment 20/30/30/20, independent assessor, 50% pass | Gradebook weights; separate *Independent assessor* role |
| Field practicum: 2 sessions + 3 case logs, mentor-verified | 5 upload activities graded *Verified / Needs rework* by the *Mentor* role |
| Attendance sheets, hours per participant (means of verification) | Attendance plugin + hours per learner in Programme admin + CSV export |
| Certificate with hours and outcomes, no NSQF claim before approval | Custom certificate with code + QR; public verification page |
| DPDP: minimum data, consent | Consent pages at first login, with the optional research use as a free choice; a dated consent record per learner; privacy officer; data export and deletion requests; 3-year retention |
| Free public advisories and workshops | Landing page sections (placeholder content) |

## Folder map (all on D:)

| Path | What |
| --- | --- |
| `docker-compose.yml`, `docker/` | The four containers: Moodle (PHP 8.3), MariaDB 11.4, Mailpit, landing page |
| `custom/theme/csfr` | Our theme (Boost child): the HOD handbook look across Moodle (cream, navy, teal, mustard, terracotta; Baloo 2 and Hind), the consent pages, the 1930 helpline band |
| `custom/local/csfr` | Our plugin: My progress, Programme admin, one learner's record, CSV export |
| `setup/content.php` | Course text and the 25-question bank: **edit this to change content** |
| `setup/files/` | The PDFs: the HOD's handbook and the extended Modules 4–6 (the build splits them per module) |
| `setup/files/videos/` | The nine module films (`mN.mp4` web copy, `mN.jpg` poster, `mN.json` transcript); `local/csfr/video.php` streams them to signed-in users, with seeking |
| `setup/videolib.php`, `setup/videos.php` | The film block at the top of each lesson page (title, length, poster, transcript); `videos.php` puts it into the live course without a rebuild |
| `setup/build.php` | Builds the course and dummy data |
| `setup/privacy.php` | DPDP set-up (policies, privacy officer, retention, the demo accounts' consent answers) |
| `setup/strings.php` | Plain wording for the consent pages (Moodle's own language customisation, no core edits) |
| `setup/reset-showcase.php` | Puts Asha back to "consent not yet given" before a demo |
| `deploy-custom.sh` | Copies `custom/` into Moodle after you change the theme or plugin, and stops if the theme fails to compile (Git Bash) |
| `shots.mjs` | Screenshots any page, signed in as a test account, for checking the design |
| `landing/index.html` | The public landing page |

**Rebuild from scratch** (new dummy data; user and course ids change):

```
docker compose exec -u www-data web php /opt/csfr-setup/build.php --reset
docker compose exec -u www-data web php /opt/csfr-setup/privacy.php
docker compose exec -u www-data web php /opt/csfr-setup/strings.php
```

The build puts the films in by itself. To swap a film on the live course, replace its files in `setup/files/videos/` and run
`docker compose exec -u www-data web php /opt/csfr-setup/videos.php` (browsers cache films for a week).

Moodle's own code is cloned from GitHub straight into Docker's storage (also on D:), so we never edit Moodle core.
Updates stay safe.

## Not done yet

- **Marathi and Hindi:** Moodle has language packs for both, but they aren't installed here and the course text is English only.
- **Policy texts are drafts.** CyberPeace's legal team must review them. Under the DPDP Act anyone **under 18 needs verifiable parental consent**, so the brief's "16+" entry needs a parental-consent step.
- **Online hours are estimated** from activity logs (30-minute gap rule). Agree this method with CyberPeace before reporting it.
- Tier 3 (mentor) and Tier 4 (Master Trainer) courses, the citizen help-desk dashboard (Objective 5.4) and workshop registration aren't built.
- The certificate layout has been checked as text only; look at `sample-certificate.pdf` and adjust positions if needed.

## Moving to Hostinger later

Ask Hostinger to confirm: PHP 8.3+, **MariaDB 11.4+ or MySQL 8.4+**, `max_input_vars` = 5000, a cron job every minute,
and a subdomain pointed at Moodle's `public/` folder. Then install Moodle 5.3 there, add the same plugins
(`mod_customcert`, `mod_attendance`, our `theme_csfr` and `local_csfr`) and run the setup scripts, or move the course with a Moodle backup.
The films and handbook PDFs are read from `/opt/csfr-setup/files/`; on shared hosting, put that folder outside the web root
and change the one path in `local/csfr/video.php` and `handout.php`.
