<?php
// Throwaway test: the booklet bank quiz draws 15 of 45, attempts work, then the test course is deleted.
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/lessonlib.php');
require_once(__DIR__ . '/practicelib.php');
\core\session\manager::set_user(get_admin());
$gen = testing_util::get_data_generator();
$bank = json_decode(file_get_contents(__DIR__ . '/bank_booklet.json'), true);
$course = $gen->create_course(['fullname' => 'ZZ bank test', 'shortname' => 'zzbanktest' . time(), 'numsections' => 2]);
try {
    $quiz = csfr_bank_quiz($gen, $course, 1, $bank[1], 15);
    $user = $gen->create_user();
    $gen->enrol_user($user->id, $course->id, 'student');
    \core\session\manager::set_user($user);
    $a = $gen->get_plugin_generator('mod_quiz')->create_attempt($quiz->id, $user->id);
    $att = \mod_quiz\quiz_attempt::create($a->id);
    $post = [];
    foreach ($att->get_slots() as $slot) {
        $qa = $att->get_question_attempt($slot);
        $post[$qa->get_control_field_name('sequencecheck')] = (string)$qa->get_sequence_check_count();
        foreach ($qa->get_question()->get_correct_response() as $k => $v) { $post[$qa->get_qt_field_name($k)] = $v; }
    }
    $att->process_submitted_actions(time(), false, $post);
    $att->process_submit(time(), false);
    $att->process_grade_submission(time());
    $att = \mod_quiz\quiz_attempt::create($a->id);
    cli_writeln('slots ' . count($att->get_slots()) . ', distinct questions ' . count(array_unique(array_map(fn($s) => $att->get_question_attempt($s)->get_question()->id, $att->get_slots())))
        . ', sumgrades ' . $att->get_sum_marks() . ' of ' . $att->get_quiz()->sumgrades);
} finally {
    \core\session\manager::set_user(get_admin());
    delete_course($course->id, false);
    cli_writeln('test course deleted');
}
