<?php
// Sanity check: every practice question loads, starts and grades its own right answer as fully correct.
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/questionlib.php');
$bad = 0;
foreach (range(1, 9) as $n) {
    $cm = $DB->get_record('course_modules', ['idnumber' => "csfr_practice$n"], '*', MUST_EXIST);
    $DB->set_field('quiz', 'intro', '<p>Quick tasks in different formats: safe or risky, spot the warning signs, the evidence bag, who do I call, '
        . 'fill the gaps and talk to the victim. Try as often as you like; your best score is kept.</p>', ['id' => $cm->instance]);
    $slots = $DB->get_records_sql("SELECT qv.questionid FROM {quiz_slots} s JOIN {question_references} r ON r.itemid = s.id AND r.component = 'mod_quiz'
        AND r.questionarea = 'slot' JOIN {question_versions} qv ON qv.questionbankentryid = r.questionbankentryid WHERE s.quizid = ?", [$cm->instance]);
    $types = [];
    foreach ($slots as $s) {
        $q = question_bank::load_question($s->questionid);
        $q->start_attempt(new question_attempt_step(), 1);
        $right = $q->get_correct_response();
        [$fraction] = $q->grade_response($right);
        $types[] = $q->get_type_name();
        if (abs($fraction - 1) > 0.001) { $bad++; cli_writeln("module $n: {$q->name} grades its right answer as $fraction"); }
    }
    cli_writeln("module $n: " . count($types) . ' ok · ' . implode(', ', array_count_values($types) ? array_map(fn($k, $v) => "$k×$v", array_keys(array_count_values($types)), array_count_values($types)) : []));
}
cli_writeln($bad ? "$bad problems" : 'all practice questions load and mark correctly');
rebuild_course_cache(get_course($cm->course)->id, true);
