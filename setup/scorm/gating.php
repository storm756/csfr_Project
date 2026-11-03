<?php
// Cisco-style progression. For modules 1-9:
//   - the lesson gets MANUAL completion (the in-page gate bar marks it done once the learner has engaged + scrolled);
//   - the module assessment is restricted: available only when that module's lesson is complete;
//   - the next module's lesson is restricted: available only when the previous module's assessment is passed.
// The final assessment is restricted to "Module 9 assessment passed" (which, through the chain, means all nine done).
// Safe to run again.
// Run: docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/scorm/gating.php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/completionlib.php');

$courseid = 14;
$course = get_course($courseid);
$modinfo = get_fast_modinfo($course);

// find each module's lesson (the mod/page named "Lesson: ..." in that section)
function lesson_cm($modinfo, $section) {
    foreach ($modinfo->get_cms() as $cm) {
        if ($cm->modname === 'page' && (int)$cm->sectionnum === $section && strpos($cm->name, 'Lesson:') === 0) {
            return $cm;
        }
    }
    return null;
}
function assess_cmid($DB, $courseid, $idnumber) {
    return $DB->get_field('course_modules', 'id', ['idnumber' => $idnumber, 'course' => $courseid]);
}
function restrict_to_completion(int $cmid, int $reqcmid) {
    global $DB;
    $av = json_encode(['op' => '&', 'c' => [['type' => 'completion', 'cm' => $reqcmid, 'e' => COMPLETION_COMPLETE]], 'showc' => [true]]);
    $DB->set_field('course_modules', 'availability', $av, ['id' => $cmid]);
}

$lessoncm = [];
for ($n = 1; $n <= 9; $n++) {
    $cm = lesson_cm($modinfo, $n);
    if (!$cm) { cli_writeln("module $n: lesson not found, skipped"); continue; }
    $lessoncm[$n] = $cm->id;
    // manual completion on the lesson
    $DB->update_record('course_modules', (object)['id' => $cm->id, 'completion' => COMPLETION_TRACKING_MANUAL,
        'completionview' => 0, 'completiongradeitemnumber' => null, 'completionexpected' => 0]);
    cli_writeln("module $n: lesson cmid={$cm->id} set to manual completion");
}

for ($n = 1; $n <= 9; $n++) {
    $acmid = assess_cmid($DB, $courseid, "csfr_assess$n");
    if ($acmid && !empty($lessoncm[$n])) {
        restrict_to_completion((int)$acmid, (int)$lessoncm[$n]);       // assessment needs its lesson complete
        cli_writeln("module $n: assessment restricted to lesson complete");
    }
    if ($n >= 2 && !empty($lessoncm[$n])) {
        $prev = assess_cmid($DB, $courseid, 'csfr_assess' . ($n - 1));
        if ($prev) {
            restrict_to_completion((int)$lessoncm[$n], (int)$prev);    // this lesson needs previous assessment passed
            cli_writeln("module $n: lesson restricted to module " . ($n - 1) . ' assessment passed');
        }
    }
}
// final assessment needs module 9 assessment passed (end of the chain => all nine done)
$finalcmid = assess_cmid($DB, $courseid, 'csfr_final');
$a9 = assess_cmid($DB, $courseid, 'csfr_assess9');
if ($finalcmid && $a9) {
    restrict_to_completion((int)$finalcmid, (int)$a9);
    cli_writeln('final: restricted to module 9 assessment passed');
}

rebuild_course_cache($courseid, true);
cli_writeln('done');
