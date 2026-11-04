<?php
// Demo-only: make Asha Desai's showcase account look fully complete.
// Run: docker compose exec -u www-data web php /opt/csfr-setup/demo-showcase-complete.php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

$user = $DB->get_record('user', ['username' => 'demo.learner', 'deleted' => 0], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => 14], '*', MUST_EXIST);
$modinfo = get_fast_modinfo($course);
$completion = new completion_info($course);
$now = time();
$completed = 0;
cli_writeln('Starting demo completion for user ' . $user->id . ' / course ' . $course->id);

foreach ($modinfo->get_cms() as $activity) {
    if (!$activity->uservisible || !$activity->completion) { continue; }
    $completion->update_state($activity, COMPLETION_COMPLETE, $user->id);
    $completed++;
}
cli_writeln('Completion records updated: ' . $completed);

// The progress page reads quiz_grades for the nine module checks.
for ($n = 1; $n <= 9; $n++) {
    $checkcm = $DB->get_record('course_modules', ['course' => $course->id, 'idnumber' => "csfr_check$n"]);
    $quiz = $checkcm ? $DB->get_record('quiz', ['id' => $checkcm->instance]) : false;
    if (!$quiz) { continue; }
    $old = $DB->get_record('quiz_grades', ['quiz' => $quiz->id, 'userid' => $user->id]);
    $row = (object)['quiz' => $quiz->id, 'userid' => $user->id, 'grade' => 9.0, 'timemodified' => $now];
    if ($old) { $row->id = $old->id; $DB->update_record('quiz_grades', $row); }
    else { $DB->insert_record('quiz_grades', $row); }
}
cli_writeln('Module check grades updated');

// Give every SCORM assessment a high passing mark in the gradebook.
$items = $DB->get_records_sql("SELECT gi.* FROM {grade_items} gi
    WHERE gi.courseid = :course AND gi.itemtype = 'mod' AND gi.itemmodule = 'scorm' AND gi.itemnumber = 0",
    ['course' => $course->id]);
foreach ($items as $item) {
    $gradeitem = grade_item::fetch(['id' => $item->id]);
    if ($gradeitem) { $gradeitem->update_final_grade($user->id, min(94, (float)$item->grademax * 0.88), 'demo-showcase'); }
}
cli_writeln('SCORM gradebook updated');

// Give all five practicum logs dummy submitted/verified records.
$assessor = $DB->get_record('user', ['username' => 'demo.assessor', 'deleted' => 0], '*', MUST_EXIST);
foreach ($DB->get_records_sql("SELECT a.* FROM {assign} a JOIN {course_modules} cm ON cm.instance = a.id
        JOIN {modules} mo ON mo.id = cm.module AND mo.name = 'assign'
        WHERE cm.course = :course AND cm.idnumber LIKE 'csfr_log%'", ['course' => $course->id]) as $assignment) {
    $sub = $DB->get_record('assign_submission', ['assignment' => $assignment->id, 'userid' => $user->id, 'latest' => 1]);
    if (!$sub) {
        $DB->insert_record('assign_submission', (object)['assignment' => $assignment->id, 'userid' => $user->id,
            'timecreated' => $now, 'timemodified' => $now, 'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED,
            'attemptnumber' => 0, 'latest' => 1, 'groupid' => 0, 'attemptreopenmethod' => 'none']);
    } else { $DB->set_field('assign_submission', 'status', ASSIGN_SUBMISSION_STATUS_SUBMITTED, ['id' => $sub->id]); }
    $grade = $DB->get_record('assign_grades', ['assignment' => $assignment->id, 'userid' => $user->id, 'attemptnumber' => -1]);
    $row = (object)['assignment' => $assignment->id, 'userid' => $user->id, 'grader' => $assessor->id,
        'timecreated' => $now, 'timemodified' => $now, 'grade' => 2, 'attemptnumber' => -1];
    if ($grade) { $row->id = $grade->id; $DB->update_record('assign_grades', $row); }
    else { $DB->insert_record('assign_grades', $row); }
}
cli_writeln('Practicum records updated');

// Also give the practical, portfolio and viva assignment items demo passing marks.
foreach ($DB->get_records_sql("SELECT a.*, cm.id AS cmid FROM {assign} a
        JOIN {course_modules} cm ON cm.instance = a.id
        JOIN {modules} mo ON mo.id = cm.module AND mo.name = 'assign'
        WHERE cm.course = :course AND cm.idnumber IN ('csfr_practical', 'csfr_portfolio', 'csfr_viva')",
        ['course' => $course->id]) as $assignment) {
    $item = grade_item::fetch(['courseid' => $course->id, 'itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $assignment->id]);
    if ($item) { $item->update_final_grade($user->id, min(88, (float)$item->grademax * 0.88), 'demo-showcase'); }
}
cli_writeln('Final assignment grades updated');

grade_regrade_final_grades($course->id);
(new \core\task\completion_regular_task())->execute();
$cert = $DB->get_record('customcert', ['course' => $course->id], '*', MUST_EXIST);
if (!$DB->record_exists('customcert_issues', ['customcertid' => $cert->id, 'userid' => $user->id])) {
    \mod_customcert\certificate::issue_certificate($cert->id, $user->id);
}
$DB->set_field('customcert_issues', 'code', 'DEMO-ASH-2026', ['customcertid' => $cert->id, 'userid' => $user->id]);
cli_writeln("Demo showcase ready for Asha Desai (demo.learner). Completed activities: $completed.");
cli_writeln('Demo code: DEMO-ASH-2026. All marks and certificate data are dummy demo data; do not use as a production credential.');
