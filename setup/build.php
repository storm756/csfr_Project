<?php
// Builds the CSFR demo course with dummy learners.
//
// Run inside the web container:
//   php /opt/csfr-setup/build.php [--reset]
// --reset deletes the demo course and demo.* users first, so the script can be re-run.

define('CLI_SCRIPT', true);

require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/attendance/locallib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria_grade.php');
require_once($CFG->dirroot . '/completion/completion_aggregation.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/group/lib.php');

[$options] = cli_get_params(['reset' => false]);

const COURSE_SHORTNAME = 'CSFR-PILOT-2026';

\core\session\manager::set_user(get_admin());
$gen = new testing_data_generator();
require_once(__DIR__ . '/videolib.php');
require_once(__DIR__ . '/lessonlib.php');
require_once(__DIR__ . '/practicelib.php');
$practiceitems = require(__DIR__ . '/practice.php');
$bookletbank = (array)json_decode((string)@file_get_contents(__DIR__ . '/bank_booklet.json'), true);
$content = require(__DIR__ . '/content.php');
$demopassword = getenv('DEMO_USER_PASSWORD') ?: 'Demo-2026!';
mt_srand(2026);

if ($existing = $DB->get_record('course', ['shortname' => COURSE_SHORTNAME])) {
    if (!$options['reset']) {
        cli_error('The demo course already exists. Re-run with --reset to rebuild it.');
    }
    cli_writeln('Deleting the existing demo course and users...');
    delete_course($existing, false);
    foreach ($DB->get_records_select('user', "username LIKE 'demo.%' AND deleted = 0") as $u) {
        delete_user($u);
    }
}

// ---------------------------------------------------------------- Site settings.
cli_writeln('Site settings...');
set_config('theme', 'csfr');
set_config('timezone', 'Asia/Kolkata');
set_config('forcetimezone', 'Asia/Kolkata');
set_config('country', 'IN');
set_config('smtphosts', 'mail:1025');
set_config('noreplyaddress', 'noreply@csfr.local');
set_config('enablecompletion', 1);
set_config('forceloginforprofiles', 1);
set_config('enablemobilewebservice', 1);
set_config('registerauth', 'email');
set_config('auth', 'email');
set_config('frontpage', '6');
set_config('frontpageloggedin', '5');
set_config('debugdisplay', 0);
set_config('forcelogin', 0);
set_config('enablemyhome', 1);
// After login everyone lands on My progress (staff are sent on to Programme admin); Moodle's generic dashboard is off.
set_config('defaulthomepage', '/local/csfr/home.php');
set_config('enabledashboard', 0);
set_config('auth_instructions', '<h2>Be the first call after a scam.</h2>'
    . '<p>A free 60-hour certificate from CyberPeace Foundation and SAKEC, Mumbai.</p>'
    . '<p>New here? Choose <strong>Sign up</strong>. You will read and accept the privacy notice before you start.</p>');

// No image logo: the theme sets the wordmark in Epilogue, as on the landing page.
$syscontext = context_system::instance();
$fs = get_file_storage();
foreach (['logo', 'logocompact'] as $area) {
    $fs->delete_area_files($syscontext->id, 'core_admin', $area);
    unset_config($area, 'core_admin');
}
$DB->set_field('course', 'shortname', 'Cyber First Responder Academy', ['id' => SITEID]);

$hero = '<div class="csfr-hero"><h2>Be the first call after a scam.</h2>'
    . '<p class="lead">A free 60-hour certificate from CyberPeace Foundation and SAKEC, Mumbai.</p>'
    . '<p class="csfr-actions"><a class="btn btn-primary btn-lg" href="' . $CFG->wwwroot . '/login/signup.php">Register free</a> '
    . '<a class="btn btn-secondary btn-lg" href="' . $CFG->wwwroot . '/login/index.php">Log in</a></p></div>';
$DB->set_field('course', 'summary', $hero, ['id' => SITEID]);
$DB->set_field('course', 'summaryformat', FORMAT_HTML, ['id' => SITEID]);
$DB->delete_records('course_format_options', ['courseid' => SITEID, 'name' => 'numsections']);
$DB->insert_record('course_format_options', ['courseid' => SITEID, 'format' => 'site', 'sectionid' => 0,
    'name' => 'numsections', 'value' => 1]);
course_create_sections_if_missing(SITEID, [0, 1]);
if ($frontsection = $DB->get_record('course_sections', ['course' => SITEID, 'section' => 1])) {
    $DB->update_record('course_sections', (object)['id' => $frontsection->id, 'summary' => $hero, 'summaryformat' => FORMAT_HTML]);
}

// ---------------------------------------------------------------- Profile fields (who our beneficiaries are).
cli_writeln('Profile fields...');
if (!$DB->record_exists('user_info_category', ['name' => 'CSFR programme'])) {
    $gen->create_custom_profile_field_category(['name' => 'CSFR programme']);
}
$fields = [
    ['shortname' => 'district', 'name' => 'District', 'datatype' => 'text', 'required' => 1],
    ['shortname' => 'beneficiarygroup', 'name' => 'Which group are you from?', 'datatype' => 'menu', 'required' => 1,
        'param1' => "Student\nTeacher\nCommunity volunteer\nPanchayat staff\nBanking correspondent\nNGO worker\n"
            . "Frontline official\nWomen's self-help group\nSenior citizen\nSmall trader\nOther"],
    ['shortname' => 'language', 'name' => 'Preferred language', 'datatype' => 'menu', 'required' => 1,
        'param1' => "English\nHindi\nMarathi\nGujarati"],
    ['shortname' => 'age16', 'name' => 'I confirm I am 16 years or older', 'datatype' => 'checkbox', 'required' => 1],
];
foreach ($fields as $f) {
    if (!$DB->record_exists('user_info_field', ['shortname' => $f['shortname']])) {
        $gen->create_custom_profile_field($f + ['category' => 'CSFR programme', 'signup' => 1, 'visible' => 1]);
    }
}

// ---------------------------------------------------------------- Roles.
cli_writeln('Roles...');
$roleids = [];
foreach ([
    'mentor' => ['Mentor (Tier 3)', 'Verifies field practicum logs and supports learners in one batch.'],
    'assessor' => ['Independent assessor', 'Grades the practical, portfolio and viva; is not the learner\'s trainer.'],
] as $shortname => [$name, $description]) {
    if (!$roleid = $DB->get_field('role', 'id', ['shortname' => $shortname])) {
        $roleid = create_role($name, $shortname, $description, 'teacher');
        reset_role_capabilities($roleid);
        set_role_contextlevels($roleid, [CONTEXT_COURSE, CONTEXT_MODULE]);
    }
    $roleids[$shortname] = $roleid;
}
$roleids['trainer'] = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);
$roleids['learner'] = $DB->get_field('role', 'id', ['shortname' => 'student']);
$roleids['manager'] = $DB->get_field('role', 'id', ['shortname' => 'manager']);

// ---------------------------------------------------------------- Course and sections.
cli_writeln('Course...');
if (!$category = $DB->get_record('course_categories', ['idnumber' => 'CSFR'])) {
    $category = $gen->create_category(['name' => 'CSFR Programme', 'idnumber' => 'CSFR',
        'description' => 'Courses of the Cyber Safety Skilling, First Responder Capacity Building and Citizen Cyber Resilience Programme.']);
}
$course = $gen->create_course([
    'fullname' => 'Certificate in Cyber Incident First Response (Pilot 2026)',
    'shortname' => COURSE_SHORTNAME,
    'category' => $category->id,
    'format' => 'topics',
    'numsections' => 14,
    'enablecompletion' => 1,
    'showcompletionconditions' => 1,
    'groupmode' => SEPARATEGROUPS,
    'startdate' => make_timestamp(2026, 10, 1),
    'enddate' => make_timestamp(2027, 1, 31),
    'summary' => '<p>The Tier 2 certificate of the CyberPeace First Responder Competency Framework: 15 hours in class, '
        . '15 hours online, 24 hours of field practicum and 6 hours of portfolio and viva. NSQF level and credits will be '
        . 'shown only after qualification approval.</p>',
    'summaryformat' => FORMAT_HTML,
], ['createsections' => true]);
$coursecontext = context_course::instance($course->id);

$DB->insert_record('role_names', ['roleid' => $roleids['trainer'], 'contextid' => $coursecontext->id, 'name' => 'Trainer']);
$DB->insert_record('role_names', ['roleid' => $roleids['learner'], 'contextid' => $coursecontext->id, 'name' => 'Learner']);

$sectionnames = [0 => 'Start here'];
foreach ($content['modules'] as $n => $m) {
    $sectionnames[$n] = "Module $n: " . $m['title'];
}
$sectionnames += [
    10 => 'Module 10: Capstone simulation',
    11 => 'Module 11: Final assessment',
    12 => 'Field practicum (24 hours)',
    13 => 'Portfolio and viva (6 hours)',
    14 => 'Your certificate',
];
foreach ($sectionnames as $num => $name) {
    $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $num], '*', MUST_EXIST);
    course_update_section($course, $section, ['name' => $name]);
}

// ---------------------------------------------------------------- Helpers.

/**
 * Adds a module and returns its instance record (with ->cmid).
 */
function csfr_add(string $modname, int $section, array $data): stdClass {
    global $gen, $course;
    return $gen->create_module($modname, ['course' => $course->id, 'section' => $section] + $data);
}

/**
 * Creates a multiple-choice quiz from bank entries.
 */
function csfr_quiz(int $section, string $name, string $intro, array $questions, array $settings = []): stdClass {
    global $gen;
    $quiz = csfr_add('quiz', $section, $settings + [
        'name' => $name, 'intro' => $intro, 'introformat' => FORMAT_HTML,
        'grade' => 100, 'questionsperpage' => 5, 'shuffleanswers' => 1, 'preferredbehaviour' => 'deferredfeedback',
        'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionusegrade' => 1,
    ]);
    $qgen = $gen->get_plugin_generator('core_question');
    $cat = $qgen->create_question_category(['contextid' => context_module::instance($quiz->cmid)->id, 'name' => $name]);
    foreach (array_values($questions) as $i => $q) {
        $answers = $fractions = $feedback = [];
        foreach ($q['o'] as $j => $option) {
            $answers[] = ['text' => $option, 'format' => FORMAT_PLAIN];
            $fractions[] = $j === $q['a'] ? '1.0' : '0.0';
            $feedback[] = ['text' => '', 'format' => FORMAT_HTML];
        }
        $answers[] = ['text' => '', 'format' => FORMAT_PLAIN];
        $fractions[] = '0.0';
        $feedback[] = ['text' => '', 'format' => FORMAT_HTML];
        // Same form data as the multichoice "one_of_four" test helper, which needs PHPUnit to load.
        $overall = fn($text) => ['text' => $text, 'format' => FORMAT_HTML];
        $fromform = (object)[
            'category' => $cat->id,
            'name' => sprintf('Q%02d %s', $i + 1, core_text::substr($q['q'], 0, 40)),
            'questiontext' => ['text' => $q['q'], 'format' => FORMAT_HTML],
            'generalfeedback' => ['text' => 'Correct answer: ' . $q['o'][$q['a']], 'format' => FORMAT_HTML],
            'defaultmark' => 1, 'penalty' => 0.3333333, 'noanswers' => count($answers), 'numhints' => 0,
            'status' => \core_question\local\bank\question_version_status::QUESTION_STATUS_READY,
            'single' => '1', 'shuffleanswers' => 1, 'answernumbering' => 'abc', 'showstandardinstruction' => 0,
            'shownumcorrect' => 1, 'correctfeedback' => $overall('Correct.'),
            'partiallycorrectfeedback' => $overall('Partly correct.'), 'incorrectfeedback' => $overall('Not quite.'),
            'answer' => $answers, 'fraction' => $fractions, 'feedback' => $feedback,
        ];
        $question = question_bank::get_qtype('multichoice')->save_question(
            (object)['qtype' => 'multichoice', 'category' => $cat->id, 'createdby' => 0, 'idnumber' => null], $fromform);
        quiz_add_quiz_question($question->id, $quiz, 0, 1);
    }
    quiz_repaginate_questions($quiz->id, $quiz->questionsperpage);
    \mod_quiz\quiz_settings::create($quiz->id)->get_grade_calculator()->recompute_quiz_sumgrades();
    $quiz->questionsdata = array_values($questions);
    return $quiz;
}

function csfr_assign(int $section, string $name, string $intro, array $settings): stdClass {
    return csfr_add('assign', $section, $settings + [
        'name' => $name, 'intro' => $intro, 'introformat' => FORMAT_HTML,
        'assignsubmission_file_enabled' => 0, 'assignsubmission_onlinetext_enabled' => 0,
        'assignfeedback_comments_enabled' => 1, 'submissiondrafts' => 0, 'sendnotifications' => 0,
        'completion' => COMPLETION_TRACKING_AUTOMATIC,
    ]);
}

function csfr_file_resource(int $section, string $name, string $intro, string $path, int $visible = 1,
        int $display = RESOURCELIB_DISPLAY_DOWNLOAD, ?string $filename = null): stdClass {
    global $USER;
    $draftid = file_get_unused_draft_itemid();
    get_file_storage()->create_file_from_pathname([
        'contextid' => context_user::instance($USER->id)->id, 'component' => 'user', 'filearea' => 'draft',
        'itemid' => $draftid, 'filepath' => '/', 'filename' => $filename ?? basename($path),
    ], $path);
    return csfr_add('resource', $section, ['name' => $name, 'intro' => $intro, 'introformat' => FORMAT_HTML,
        'files' => $draftid, 'visible' => $visible, 'display' => $display]);
}

/**
 * Some pages of a PDF ("3-5", "1-32,35"), as a smaller PDF (images at e-book resolution, so it opens fast on a phone).
 * Split once with Ghostscript and kept in moodledata, so rebuilds reuse it.
 */
function csfr_pdf_pages(string $source, string $pages): string {
    global $CFG;
    $dir = $CFG->dataroot . '/csfr-split';
    check_dir_exists($dir);
    $out = sprintf('%s/%s-p%s.pdf', $dir, pathinfo($source, PATHINFO_FILENAME), str_replace(',', '_', $pages));
    if (!is_readable($out)) {
        $cmd = sprintf('gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -sPageList=%s '
            . '-sOutputFile=%s %s 2>&1', escapeshellarg($pages), escapeshellarg($out), escapeshellarg($source));
        exec($cmd, $output, $status);
        if ($status !== 0 || !is_readable($out)) {
            throw new coding_exception("Could not split $source: " . implode("\n", $output));
        }
    }
    return $out;
}

/** The HOD's participant handbook and the teammates' extended Modules 4 to 6, as given. */
const CSFR_HANDBOOK = __DIR__ . '/files/participant-handbook.pdf';
const CSFR_EXTENDED = __DIR__ . '/files/modules-4-6-extended.pdf';

/**
 * Attaches pages of a source PDF to a section as a download. Only the trainers' answer key uses this now.
 */
function csfr_handout(int $section, string $name, string $intro, string $source, string $pages, string $filename,
        int $visible = 1): stdClass {
    return csfr_file_resource($section, $name, $intro, csfr_pdf_pages($source, $pages), $visible,
        RESOURCELIB_DISPLAY_DOWNLOAD, $filename);
}

/**
 * A handout learners open and read straight away: the handbook pages as full-width pictures on the site
 * (local/csfr/handout.php), with a PDF link at the end only for printing. $key: 0, help, 1-11 or ext4-ext6.
 */
function csfr_handout_view(int $section, string $name, string $key): stdClass {
    global $CFG;
    return csfr_add('url', $section, ['name' => $name, 'externalurl' => $CFG->wwwroot . '/local/csfr/handout.php?n=' . $key,
        'display' => RESOURCELIB_DISPLAY_OPEN, 'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => 1]);
}

// ---------------------------------------------------------------- Section 0: start here.
cli_writeln('Start here...');
$howitworks = '<div class="csfr-lesson"><p class="lead">Welcome. This certificate trains you to help people in your community '
    . 'prevent, recognise, respond to and report cybercrime.</p>'
    . '<h3>How your 60 hours are spent</h3><table class="table table-sm"><thead><tr><th>Part</th><th>Hours</th><th>Where</th>'
    . '<th>Counts towards your result</th></tr></thead><tbody>'
    . '<tr><td>A. Classroom: Modules 1 to 11</td><td>15</td><td>SAKEC or a community centre</td><td>Practical stations and capstone: 30%</td></tr>'
    . '<tr><td>B. Online self-learning</td><td>15</td><td>This website, at home</td><td>Final online MCQ: 20%</td></tr>'
    . '<tr><td>C. Field practicum</td><td>24</td><td>Your own community, with a mentor</td><td>Practicum portfolio: 30%</td></tr>'
    . '<tr><td>D. Portfolio and viva</td><td>6</td><td>Online upload + 10-minute viva</td><td>Viva: 20%</td></tr></tbody></table>'
    . '<p>You pass with 50% overall. The practical, portfolio and viva are marked by an assessor who is not your trainer.</p>'
    . '<h3>Your first steps</h3><ol><li>Take the <strong>Day 1 pre-test</strong> below. It does not count; it shows how much you '
    . 'learn.</li><li>Open each module after its class: read the online lesson, do the activity, take the module check.</li>'
    . '<li>Start your field practicum after Module 7 and upload each log in the practicum section.</li></ol>'
    . '<div class="csfr-callout">Your data: we collect only what the programme needs, with your consent, under the DPDP Act, '
    . '2023. See the privacy notice in the footer, where you can also ask for a copy or deletion of your data.</div></div>';
csfr_add('page', 0, ['name' => 'How this course works', 'content' => $howitworks, 'contentformat' => FORMAT_HTML,
    'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => 1]);
// Learner copies leave out pages 33 and 34: the question bank and its answer key, which the tests use.
csfr_handout_view(0, 'Participant handbook, all 11 modules', '0');
csfr_handout_view(0, 'Help card: helplines and portals', 'help');
$attendance = csfr_add('attendance', 0, ['name' => 'Classroom attendance (15 contact hours)', 'grade' => 0, 'idnumber' => 'csfr_attendance',
    'intro' => '<p>Your trainer marks attendance at each classroom day. This is one of the programme\'s required records.</p>',
    'introformat' => FORMAT_HTML]);
$pretest = csfr_quiz(0, 'Day 1 pre-test (does not count)',
    '<p>25 questions, about 20 minutes. Answer honestly: this measures what you know before the course.</p>',
    $content['bank'], ['attempts' => 1, 'completionusegrade' => 1, 'idnumber' => 'csfr_pretest']);

// ---------------------------------------------------------------- Modules 1-9.
cli_writeln('Modules 1-9...');
$modulequizzes = [];
foreach ($content['modules'] as $n => $m) {
    // The lesson is the handbook's pages drawn as a web page; the PDF stays as a printable download.
    $practice = array_slice(array_values(array_filter($content['extra'], fn($q) => $q['m'] === $n)), 0, 3);
    $page = csfr_add('page', $n, ['name' => "Lesson: {$m['title']}", 'idnumber' => "csfr_lesson$n",
        'content' => csfr_l10n(csfr_with_film(csfr_lesson($n, $m, $practice), $n)) . csfr_gate_script(), 'contentformat' => FORMAT_HTML,
        'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => 1, 'printintro' => 0, 'displayoptions' => serialize(['printintro' => 0, 'printlastmodified' => 0])]);
    csfr_handout_view($n, "Handout, Module $n", (string)$n);
    $content['modules'][$n]['pagecmid'] = $page->cmid;
    $content['modules'][$n]['pageid'] = $page->id;
    // (The extended notes for Modules 4-6 are now part of the module PDFs and lessons.)
    $questions = array_merge(
        array_filter($content['bank'], fn($q) => $q['m'] === $n),
        array_filter($content['extra'], fn($q) => $q['m'] === $n)
    );
    csfr_practice_quiz($gen, $course, $n, $practiceitems[$n]);
    // The module check (T1 in the booklet): 15 questions drawn at random from the module's 45-item bank.
    $modulequizzes[$n] = $bookletbank[$n] ?? null
        ? csfr_bank_quiz($gen, $course, $n, $bookletbank[$n], 15)
        : csfr_quiz($n, "Module $n check", '<p>A short check on this module. You can retry it as often as you like.</p>',
            $questions, ['attempts' => 0, 'grademethod' => QUIZ_GRADEHIGHEST, 'idnumber' => "csfr_check$n"]);
}
csfr_file_resource(1, 'Trainer guide: Module 1 session plan (trainers only)',
    '<p>Hidden from learners. Session plan, talking points, practical activity and worksheets.</p>',
    __DIR__ . '/files/module1-trainer-guide.docx', 0);

// ---------------------------------------------------------------- Module 10 capstone, Module 11 assessment.
cli_writeln('Capstone and assessment...');
$capstone = csfr_capstone();
csfr_handout_view(10, 'Handout, Module 10', '10');
csfr_add('page', 10, ['name' => 'Capstone scenario and observer rubric', 'content' => $capstone,
    'contentformat' => FORMAT_HTML, 'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => 1]);
csfr_handout_view(11, 'Handout, Module 11', '11');
csfr_handout(11, 'Question bank and answer key (trainers and assessors only)',
    '<p>Hidden from learners: the 25 questions are the pre-test and the final online test.</p>',
    CSFR_HANDBOOK, '33-34', 'csfr-question-bank-answer-key.pdf', 0);

$posttest = csfr_quiz(11, 'Final online MCQ (post-test, 20%)',
    '<p>The same 25 questions as Day 1, shuffled. This is your theory result and measures how much you learned.</p>',
    $content['bank'], ['attempts' => 1, 'gradepass' => 50, 'completionpassgrade' => 0, 'idnumber' => 'csfr_posttest']);
$practical = csfr_assign(11, 'Practical stations and capstone (30%, assessor)',
    '<p>Marked in class by the independent assessor. Station 1: triage four case cards. Station 2: evidence log from a '
    . 'fraud case pack. Station 3: fill a paper copy of the cybercrime.gov.in financial-fraud form. Plus your capstone '
    . 'rubric score.</p>', ['grade' => 100, 'completionusegrade' => 1, 'idnumber' => 'csfr_practical']);
csfr_practical_rubric($practical->cmid);
csfr_add('page', 11, ['name' => 'Station case pack (assessors only)', 'content' => csfr_station_pack(), 'contentformat' => FORMAT_HTML,
    'visible' => 0, 'printintro' => 0]);

// ---------------------------------------------------------------- Practicum, portfolio, viva.
cli_writeln('Practicum, portfolio and viva...');
$scale = $gen->create_scale(['name' => 'Mentor verification', 'scale' => 'Needs rework,Verified', 'courseid' => $course->id]);
$practicumguide = '<div class="csfr-lesson"><p class="lead">24 hours in your own community, supervised by your mentor.</p>'
    . '<h3>What you must complete</h3><ul><li><strong>2 community cyber safety sessions</strong> (for example in a school, '
    . 'self-help group or housing society). Upload the attendance sheet and one dated photo.</li><li><strong>3 supervised '
    . 'case-handling exercises</strong>, real (with consent) or simulated. Upload the intake form for each.</li></ul>'
    . '<h3>Intake form fields</h3>' . csfr_list(['Case ID and date', 'Consent recorded (yes/no) and safe contact method',
        'The six answers: what, when, money, danger, still happening, evidence', 'Category and urgency colour, with reason',
        'Actions taken, with times', 'Referral: channel and acknowledgement number', 'Escalated to mentor? (always for Red, '
        . 'child-related or large loss)', 'Follow-up date']) .
    '<div class="csfr-warning">Never write down an OTP, PIN or password. Never upload intimate images or any child sexual '
    . 'material. Remove names and phone numbers before uploading; use the case ID instead.</div></div>';
csfr_add('page', 12, ['name' => 'Practicum guide and intake form', 'content' => $practicumguide,
    'contentformat' => FORMAT_HTML, 'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => 1]);
$logs = [];
foreach ([
    'Community session 1' => 'Upload the attendance sheet, one dated photo and three lines on how it went.',
    'Community session 2' => 'Upload the attendance sheet, one dated photo and three lines on how it went.',
    'Case log 1' => 'Upload the anonymised intake form for your first case.',
    'Case log 2' => 'Upload the anonymised intake form for your second case.',
    'Case log 3' => 'Upload the anonymised intake form for your third case.',
] as $name => $intro) {
    $logs[] = csfr_assign(12, "Practicum: $name", "<p>$intro Your mentor marks it Verified or Needs rework.</p>", [
        'assignsubmission_file_enabled' => 1, 'assignsubmission_file_maxfiles' => 5,
        'assignsubmission_file_maxsizebytes' => 10 * 1024 * 1024, 'assignsubmission_onlinetext_enabled' => 1,
        'grade' => -$scale->id, 'completionsubmit' => 1, 'idnumber' => 'csfr_log' . (count($logs) + 1),
    ]);
}
$portfolio = csfr_assign(13, 'Practicum portfolio and report (30%, assessor)',
    '<p>Combine your two session records and three case logs with a one-page reflection. Upload one PDF.</p>', [
        'assignsubmission_file_enabled' => 1, 'assignsubmission_file_maxfiles' => 3,
        'assignsubmission_file_maxsizebytes' => 20 * 1024 * 1024, 'assignsubmission_onlinetext_enabled' => 1,
        'grade' => 100, 'completionsubmit' => 1, 'idnumber' => 'csfr_portfolio',
    ]);
$viva = csfr_assign(13, 'Viva (20%, assessor)', '<p>A 10-minute conversation with the assessor at the end of your practicum.</p>',
    ['grade' => 100, 'completionusegrade' => 1, 'idnumber' => 'csfr_viva']);

// ---------------------------------------------------------------- Certificate.
cli_writeln('Certificate...');
$cert = csfr_add('customcert', 14, [
    'name' => 'Certificate in Cyber Incident First Response', 'idnumber' => 'csfr_certificate',
    'intro' => '<p>Unlocks when you pass (50% overall) and your viva is graded. Each certificate has a code anyone can verify.</p>',
    'introformat' => FORMAT_HTML,
]);
// Anyone with a code can check it (the landing page's Verify box), without signing in.
$DB->set_field('customcert', 'verifyany', 1, ['id' => $cert->id]);
$templateid = $DB->get_field('customcert', 'templateid', ['id' => $cert->id]);
$page = $DB->get_record('customcert_pages', ['templateid' => $templateid], '*', IGNORE_MULTIPLE);
$DB->update_record('customcert_pages', (object)['id' => $page->id, 'width' => 297, 'height' => 210,
    'leftmargin' => 0, 'rightmargin' => 0]);
$style = fn($font, $size, $colour = '0d0d0d') => ['font' => $font, 'fontsize' => $size, 'colour' => $colour, 'width' => 250];
$elements = [
    ['border', 'Border', ['width' => 1, 'colour' => '0d0d0d'], 0, 0, 0, 'L'],
    ['text', 'Issuers', ['text' => 'CyberPeace Foundation and Shah & Anchor Kutchhi Engineering College (SAKEC), Mumbai'] + $style('freesans', 12, '00746d'), 148, 22, 1, 'C'],
    ['text', 'Title', ['text' => 'Certificate of Completion'] + $style('freesansb', 30), 148, 38, 1, 'C'],
    ['text', 'Intro', ['text' => 'This certifies that'] + $style('freesans', 14, '333333'), 148, 60, 1, 'C'],
    ['studentname', 'Learner name', $style('freesansb', 26, '0d0d0d'), 148, 72, 1, 'C'],
    ['text', 'Programme', ['text' => 'has completed the Certificate in Cyber Incident First Response (Cyber Incident First Responder)'] + $style('freesans', 14, '333333'), 148, 92, 1, 'C'],
    ['text', 'Hours', ['text' => '60 notional learning hours in two micro-credentials: MC-1 Knowledge and Skills (30 h) and MC-2 Supervised Community Practice (30 h)'] + $style('freesans', 11, '333333'), 148, 104, 1, 'C'],
    ['text', 'Outcomes', ['text' => 'Learning outcomes: cyber hygiene; fraud, deepfake and malware recognition; incident triage; evidence preservation; reporting and escalation; victim support.'] + $style('freesans', 10, '555555'), 148, 116, 1, 'C'],
    ['text', 'NSQF note', ['text' => 'NSQF level and credits will be shown after qualification approval.'] + $style('freesans', 9, '777777'), 148, 128, 1, 'C'],
    ['date', 'Issue date', ['dateitem' => '-1', 'dateformat' => 'strftimedate'] + $style('freesans', 11, '333333'), 40, 160, 0, 'L'],
    ['code', 'Verification code', $style('freesans', 11, '333333'), 40, 170, 0, 'L'],
    ['qrcode', 'QR code', ['width' => 32, 'height' => 32], 240, 150, 0, 'L'],
    ['text', 'Signatories', ['text' => 'Programme Lead, CyberPeace Foundation          Head of Department, SAKEC'] + $style('freesans', 10, '333333'), 148, 185, 1, 'C'],
];
foreach ($elements as $seq => [$type, $name, $data, $x, $y, $refpoint, $align]) {
    $DB->insert_record('customcert_elements', (object)[
        'pageid' => $page->id, 'element' => $type, 'name' => $name, 'data' => json_encode($data),
        'posx' => $x, 'posy' => $y, 'refpoint' => $refpoint, 'alignment' => $align, 'sequence' => $seq + 1,
        'timecreated' => time(), 'timemodified' => time(),
    ]);
}

// ---------------------------------------------------------------- Gradebook: 20/30/30/20.
cli_writeln('Gradebook...');
$top = grade_category::fetch_course_category($course->id);
$top->aggregation = GRADE_AGGREGATE_WEIGHTED_MEAN;
$top->update();
$final = $gen->create_grade_category(['courseid' => $course->id, 'fullname' => 'Final assessment (counts)',
    'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN, 'aggregateonlygraded' => 0]);
$formative = $gen->create_grade_category(['courseid' => $course->id, 'fullname' => 'Formative checks and records (not counted)',
    'aggregation' => GRADE_AGGREGATE_MEAN]);
$catitem = fn($cat) => grade_item::fetch(['courseid' => $course->id, 'itemtype' => 'category', 'iteminstance' => $cat->id]);
foreach ([[$final, 1], [$formative, 0]] as [$cat, $weight]) {
    $item = $catitem($cat);
    $item->aggregationcoef = $weight;
    $item->update();
}
$modgradeitem = fn(string $mod, int $instance) => grade_item::fetch(['courseid' => $course->id, 'itemtype' => 'mod',
    'itemmodule' => $mod, 'iteminstance' => $instance, 'itemnumber' => 0]);
foreach ([[$posttest, 'quiz', 20], [$practical, 'assign', 30], [$portfolio, 'assign', 30], [$viva, 'assign', 20]] as [$inst, $mod, $w]) {
    $item = $modgradeitem($mod, $inst->id);
    $item->set_parent($final->id);
    $item->aggregationcoef = $w;
    $item->update();
}
$formativeitems = array_merge([[$pretest, 'quiz']], array_map(fn($q) => [$q, 'quiz'], $modulequizzes),
    array_map(fn($a) => [$a, 'assign'], $logs));
foreach ($formativeitems as [$inst, $mod]) {
    if ($item = $modgradeitem($mod, $inst->id)) {
        $item->set_parent($formative->id);
    }
}
$coursetotal = grade_item::fetch_course_item($course->id);
$coursetotal->gradepass = 50;
$coursetotal->update();

// ---------------------------------------------------------------- Course completion and certificate unlock.
cli_writeln('Completion rules...');
$required = array_merge([$posttest->cmid, $practical->cmid, $portfolio->cmid, $viva->cmid], array_map(fn($l) => $l->cmid, $logs));
$data = (object)['id' => $course->id, 'criteria_activity' => array_fill_keys($required, 1),
    'criteria_grade' => 1, 'criteria_grade_value' => 50];
(new completion_criteria_activity())->update_config($data);
(new completion_criteria_grade())->update_config($data);
foreach ([null, COMPLETION_CRITERIA_TYPE_ACTIVITY] as $type) {
    (new completion_aggregation(['course' => $course->id, 'criteriatype' => $type, 'method' => COMPLETION_AGGREGATION_ALL]))->save();
}
$availability = ['op' => '&', 'showc' => [true, true], 'c' => [
    ['type' => 'grade', 'id' => (int)$coursetotal->id, 'min' => 50],
    ['type' => 'completion', 'cm' => (int)$viva->cmid, 'e' => COMPLETION_COMPLETE],
]];
$DB->set_field('course_modules', 'availability', json_encode($availability), ['id' => $cert->cmid]);
rebuild_course_cache($course->id, true);

// ---------------------------------------------------------------- People and batches.
cli_writeln('People and batches...');
$people = [
    'staff' => [
        ['demo.lead', 'Ananya', 'Rao', 'manager', null],
        ['demo.trainer1', 'Rahul', 'Deshmukh', 'trainer', 1],
        ['demo.trainer2', 'Fatima', 'Shaikh', 'trainer', 2],
        ['demo.mentor1', 'Vikram', 'Patil', 'mentor', 1],
        ['demo.mentor2', 'Neha', 'Joshi', 'mentor', 2],
        ['demo.assessor', 'Suresh', 'Iyer', 'assessor', null],
    ],
    'learners' => [
        1 => ['Aarav Kulkarni', 'Priya Nair', 'Mohammed Ansari', 'Sneha Pawar', 'Rohan Gupta', 'Kavita Jadhav',
              'Imran Qureshi', 'Pooja Shinde', 'Arjun Menon', 'Meera Pillai', 'Sanjay Kamble', 'Lakshmi Reddy'],
        2 => ['Tanvi Sawant', 'Rajesh Yadav', 'Ayesha Khan', 'Nikhil More', 'Deepa Chavan', 'Farhan Siddiqui',
              'Swati Bhosale', 'Vinod Mishra', 'Rekha Gaikwad', 'Karan Malhotra', 'Shabana Sayyed', 'Ganesh Naik'],
    ],
];
$groups = [
    1 => $gen->create_group(['courseid' => $course->id, 'name' => 'Batch 01, SAKEC Chembur, Oct 2026', 'idnumber' => 'B01']),
    2 => $gen->create_group(['courseid' => $course->id, 'name' => 'Batch 02, Govandi community centre, Oct 2026', 'idnumber' => 'B02']),
];
$districts = ['Mumbai Suburban', 'Mumbai City', 'Thane', 'Raigad'];
$benefgroups = ['Student', 'Teacher', 'Community volunteer', 'Panchayat staff', 'Banking correspondent', 'NGO worker',
    'Frontline official', "Women's self-help group", 'Small trader'];
$makeuser = function(string $username, string $first, string $last) use ($gen, $demopassword, $districts, $benefgroups) {
    $user = $gen->create_user(['username' => $username, 'firstname' => $first, 'lastname' => $last,
        'email' => "$username@example.com", 'password' => $demopassword, 'city' => 'Mumbai', 'country' => 'IN',
        'timezone' => 'Asia/Kolkata']);
    $user->profile_field_district = $districts[mt_rand(0, 3)];
    $user->profile_field_beneficiarygroup = $benefgroups[mt_rand(0, count($benefgroups) - 1)];
    $user->profile_field_language = ['English', 'Marathi', 'Hindi'][mt_rand(0, 2)];
    $user->profile_field_age16 = 1;
    profile_save_data($user);
    return $user;
};
// The admin account needs the required profile fields too, or Moodle forces a profile edit at login.
$admin = get_admin();
$admin->profile_field_district = 'Mumbai Suburban';
$admin->profile_field_beneficiarygroup = 'Other';
$admin->profile_field_language = 'English';
$admin->profile_field_age16 = 1;
profile_save_data($admin);
$staff = [];
foreach ($people['staff'] as [$username, $first, $last, $role, $batch]) {
    $user = $makeuser($username, $first, $last);
    $staff[$username] = $user;
    if ($role === 'manager') {
        role_assign($roleids['manager'], $user->id, context_coursecat::instance($category->id)->id);
        continue;
    }
    $gen->enrol_user($user->id, $course->id, $roleids[$role]);
    if ($batch) {
        groups_add_member($groups[$batch]->id, $user->id);
    }
}
$learners = [1 => [], 2 => []];
foreach ($people['learners'] as $batch => $names) {
    foreach ($names as $i => $fullname) {
        [$first, $last] = explode(' ', $fullname, 2);
        $user = $makeuser(sprintf('demo.b%02d.learner%02d', $batch, $i + 1), $first, $last);
        $gen->enrol_user($user->id, $course->id, $roleids['learner']);
        groups_add_member($groups[$batch]->id, $user->id);
        $learners[$batch][] = $user;
    }
}
// The showcase learner for demonstrations: Batch 02, part-way through, and has not yet given consent,
// so the first login shows the DPDP consent screen.
$showcase = $makeuser('demo.learner', 'Asha', 'Desai');
$showcase->profile_field_district = 'Mumbai Suburban';
$showcase->profile_field_beneficiarygroup = 'Teacher';
$showcase->profile_field_language = 'Marathi';
profile_save_data($showcase);
$gen->enrol_user($showcase->id, $course->id, $roleids['learner']);
groups_add_member($groups[2]->id, $showcase->id);
$learners[2][] = $showcase;

// ---------------------------------------------------------------- Attendance sessions.
cli_writeln('Attendance...');
$attcm = get_coursemodule_from_instance('attendance', $attendance->id, $course->id, false, MUST_EXIST);
$attrecord = $DB->get_record('attendance', ['id' => $attendance->id], '*', MUST_EXIST);
$attstructure = new mod_attendance_structure($attrecord, $attcm, $course, context_module::instance($attcm->id));
$batchstart = [1 => make_timestamp(2026, 10, 12, 10), 2 => make_timestamp(2026, 10, 26, 10)];
$days = ['Day 1: Modules 1 and 2', 'Day 2: Modules 3 and 4', 'Day 3: Modules 5 and 6', 'Day 4: Modules 7 to 9', 'Day 5: Modules 10 and 11'];
$statuses = $DB->get_records('attendance_statuses', ['attendanceid' => $attendance->id, 'deleted' => 0], 'grade DESC');
$present = reset($statuses);
$absent = end($statuses);
$trainer1 = $staff['demo.trainer1'];
foreach ($batchstart as $batch => $start) {
    foreach ($days as $d => $label) {
        // Batch 02 meets twice in October, then weekly from 7 November: its last three days are still ahead.
        $when = $start + $d * DAYSECS + ($batch === 2 && $d >= 2 ? (10 + 6 * ($d - 2)) * DAYSECS : 0);
        $sess = (object)['sessdate' => $when, 'duration' => 3 * HOURSECS, 'groupid' => $groups[$batch]->id,
            'description' => $label, 'descriptionformat' => FORMAT_HTML, 'descriptionitemid' => 0, 'timemodified' => time(),
            'statusset' => 0, 'studentscanmark' => 0, 'calendarevent' => 0];
        $sessid = $attstructure->add_session($sess);
        // Batch 01 has finished class; Batch 02 has done its first two days.
        if ($batch === 1 || $d < 2) {
            foreach ($learners[$batch] as $l) {
                $status = mt_rand(1, 100) <= 93 ? $present : $absent;
                $DB->insert_record('attendance_log', ['sessionid' => $sessid, 'studentid' => $l->id, 'statusid' => $status->id,
                    'statusset' => implode(',', array_keys($statuses)), 'timetaken' => $sess->sessdate + 3 * HOURSECS,
                    'takenby' => $staff["demo.trainer$batch"]->id, 'remarks' => '']);
            }
            $DB->update_record('attendance_sessions', (object)['id' => $sessid, 'lasttaken' => $sess->sessdate + 3 * HOURSECS,
                'lasttakenby' => $staff["demo.trainer$batch"]->id]);
        }
    }
}

// ---------------------------------------------------------------- Simulated progress (dummy data).
cli_writeln('Simulating learner progress...');
$quizgen = $gen->get_plugin_generator('mod_quiz');
// Answers each question (right with probability $pcorrect) and submits, through the public attempt API
// (the quiz generator's submit_responses() only runs under PHPUnit).
$attempt = function(stdClass $quiz, stdClass $user, int $pcorrect) use ($quizgen) {
    global $USER;
    $saved = $USER;
    \core\session\manager::set_user($user);
    $a = $quizgen->create_attempt($quiz->id, $user->id);
    $attemptobj = \mod_quiz\quiz_attempt::create($a->id);
    $postdata = [];
    // Each slot's actual question (fixed or drawn at random): its own right answer, or one of its wrong ones.
    foreach ($attemptobj->get_slots() as $slot) {
        $qa = $attemptobj->get_question_attempt($slot);
        $qq = $qa->get_question();
        $postdata[$qa->get_control_field_name('sequencecheck')] = (string)$qa->get_sequence_check_count();
        if (mt_rand(1, 100) <= $pcorrect) {
            $response = $qq->get_correct_response();
        } else {
            $wrong = array_values(array_filter($qq->answers, fn($ans) => $ans->fraction <= 0 && $ans->answer !== ''));
            $response = $qq->prepare_simulated_post_data(['answer' => $wrong[array_rand($wrong)]->answer]);
        }
        foreach ($response as $name => $value) {
            $postdata[$qa->get_qt_field_name($name)] = $value;
        }
    }
    $attemptobj->process_submitted_actions(time(), false, $postdata);
    $attemptobj->process_submit(time(), false);
    $attemptobj->process_grade_submission(time());
    \core\session\manager::set_user($saved);
};
$modinfo = get_fast_modinfo($course);
$completion = new completion_info($course);
$assessor = $staff['demo.assessor'];
$gradeassign = function(stdClass $inst, int $userid, $grade, stdClass $grader) use ($course) {
    global $USER;
    $cm = get_coursemodule_from_instance('assign', $inst->id, $course->id, false, MUST_EXIST);
    $assign = new assign(context_module::instance($cm->id), $cm, $course);
    $saved = $USER;
    \core\session\manager::set_user($grader);
    $assign->save_grade($userid, (object)['grade' => $grade, 'attemptnumber' => -1, 'applytoall' => 0,
        'sendstudentnotifications' => 0, 'workflowstate' => '']);
    \core\session\manager::set_user($saved);
};
$assigngen = $gen->get_plugin_generator('mod_assign');
$certified = [];
$studied = [];
foreach ($learners as $batch => $users) {
    foreach ($users as $i => $user) {
        if ($user->id === $showcase->id) {
            continue;
        }
        $ability = mt_rand(40, 70);
        $attempt($pretest, $user, $ability);
        $modulesdone = $batch === 1 ? 9 : mt_rand(2, 4);
        $studied[$user->id] = [$batch, $modulesdone];
        for ($n = 1; $n <= $modulesdone; $n++) {
            $completion->set_module_viewed($modinfo->get_cm($content['modules'][$n]['pagecmid']), $user->id);
            $attempt($modulequizzes[$n], $user, min(95, $ability + 20));
        }
        if ($batch !== 1 || $i >= 10) {
            continue;
        }
        // Batch 1: most finish the course; two are still working on the practicum.
        $attempt($posttest, $user, min(97, $ability + 30));
        $gradeassign($practical, $user->id, mt_rand(55, 92), $assessor);
        foreach ($logs as $log) {
            $assigngen->create_submission(['userid' => $user->id, 'cmid' => $log->cmid,
                'onlinetext' => 'Dummy record for the demo. Session held at the housing society hall; 23 attendees.',
                'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED]);
            $gradeassign($log, $user->id, 2, $staff['demo.mentor1']);
        }
        if ($i < 8) {
            $assigngen->create_submission(['userid' => $user->id, 'cmid' => $portfolio->cmid,
                'onlinetext' => 'Dummy portfolio summary: two sessions, three anonymised case logs and a reflection.',
                'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED]);
            $gradeassign($portfolio, $user->id, mt_rand(58, 90), $assessor);
            $gradeassign($viva, $user->id, mt_rand(55, 90), $assessor);
            $certified[] = $user;
        }
    }
}
// The showcase learner: ahead of her batch online (six modules), first practicum log verified, the second waiting.
$attempt($pretest, $showcase, 52);
for ($n = 1; $n <= 6; $n++) {
    $completion->set_module_viewed($modinfo->get_cm($content['modules'][$n]['pagecmid']), $showcase->id);
    $attempt($modulequizzes[$n], $showcase, 80);
}
$studied[$showcase->id] = [2, 6];
foreach (array_slice($logs, 0, 2) as $k => $log) {
    $assigngen->create_submission(['userid' => $showcase->id, 'cmid' => $log->cmid,
        'onlinetext' => 'Dummy record for the demo. Session for the parents of Class 8 at a municipal school; 31 attendees.',
        'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED]);
    if ($k === 0) {
        $gradeassign($log, $showcase->id, 2, $staff['demo.mentor2']);
    }
}
// Dummy online study sessions, one evening per module, so the dashboard's hours column has realistic values.
$rows = [];
foreach ($studied as $userid => [$batch, $done]) {
    for ($n = 1; $n <= $done; $n++) {
        $m = $content['modules'][$n];
        $ctx = context_module::instance($m['pagecmid']);
        $t = $batchstart[$batch] + (int)ceil($n / 2) * DAYSECS + 9 * HOURSECS + mt_rand(0, 3600);
        for ($k = 0, $views = mt_rand(10, 16); $k < $views; $k++) {
            $rows[] = ['eventname' => '\\mod_page\\event\\course_module_viewed', 'component' => 'mod_page', 'action' => 'viewed',
                'target' => 'course_module', 'objecttable' => 'page', 'objectid' => $m['pageid'], 'crud' => 'r',
                'edulevel' => \core\event\base::LEVEL_PARTICIPATING, 'contextid' => $ctx->id, 'contextlevel' => CONTEXT_MODULE,
                'contextinstanceid' => $m['pagecmid'], 'userid' => $userid, 'courseid' => $course->id, 'relateduserid' => null,
                'anonymous' => 0, 'other' => 'null', 'timecreated' => $t, 'origin' => 'web', 'ip' => '127.0.0.1', 'realuserid' => null];
            $t += mt_rand(240, 540);
        }
    }
}
$DB->insert_records('logstore_standard_log', $rows);

grade_regrade_final_grades($course->id);
(new \core\task\completion_regular_task())->execute();

foreach ($certified as $user) {
    \mod_customcert\certificate::issue_certificate($cert->id, $user->id);
}

// ---------------------------------------------------------------- Knowledge gain summary.
$means = [];
foreach ([1, 2] as $batch) {
    $ids = array_map(fn($u) => $u->id, $learners[$batch]);
    [$insql, $params] = $DB->get_in_or_equal($ids);
    foreach (['pre' => $pretest, 'post' => $posttest] as $k => $quiz) {
        $means[$batch][$k] = $DB->get_field_sql("SELECT AVG(grade) FROM {quiz_grades} WHERE quiz = ? AND userid $insql",
            array_merge([$quiz->id], $params));
    }
}
purge_all_caches();
cli_writeln('');
cli_writeln("Done. Course id {$course->id}: {$CFG->wwwroot}/course/view.php?id={$course->id}");
foreach ($means as $batch => $m) {
    $gain = ($m['pre'] && $m['post']) ? round(($m['post'] - $m['pre']) / $m['pre'] * 100) . '%' : 'n/a (post-test not taken yet)';
    cli_writeln(sprintf('Batch %02d: pre-test mean %s, post-test mean %s, knowledge gain %s', $batch,
        $m['pre'] ? round($m['pre']) : '-', $m['post'] ? round($m['post']) : '-', $gain));
}
cli_writeln('Certificates issued: ' . count($certified));
