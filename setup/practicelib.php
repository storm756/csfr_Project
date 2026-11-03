<?php
// Graded module practice (practice.php) and the assessor rubric for the practical stations and capstone.
// Shared by build.php (fresh course) and assessments.php (add to the live course).

defined('CLI_SCRIPT') || defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/grade/grading/lib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/grade/grade_category.php');

/** Moodle only accepts fractions from its own list; the nearest for 1/k. */
function csfr_fraction(int $k, bool $neg = false): string {
    $f = [1 => '1.0', 2 => '0.5', 3 => '0.3333333', 4 => '0.25', 5 => '0.2', 6 => '0.1666667', 7 => '0.1428571'][$k] ?? '0.1';
    return $neg ? '-' . $f : $f;
}

/** Saves one practice item as a Moodle question in $catid and returns the question. */
function csfr_practice_question(array $it, int $catid, int $i): stdClass {
    $html = fn($t) => ['text' => $t, 'format' => FORMAT_HTML];
    $base = ['category' => $catid, 'defaultmark' => 1, 'penalty' => 0.3333333, 'numhints' => 0, 'idnumber' => null,
        'status' => \core_question\local\bank\question_version_status::QUESTION_STATUS_READY];
    $stem = $it[1];
    $name = sprintf('P%02d %s', $i + 1, core_text::substr(strip_tags($stem), 0, 40));
    $combined = ['correctfeedback' => $html('Correct.'), 'partiallycorrectfeedback' => $html('Partly right.'),
        'incorrectfeedback' => $html('Not quite.'), 'shownumcorrect' => 1];
    switch ($it[0]) {
        case 'tf':
            $qtype = 'truefalse';
            $form = $base + ['name' => $name, 'questiontext' => $html('<p>' . s($stem) . '</p><p><b>Safe / true, or risky / false?</b></p>'),
                'generalfeedback' => $html(s($it[3])), 'correctanswer' => $it[2] ? 1 : 0, 'penalty' => 1, 'showstandardinstruction' => 0,
                'feedbacktrue' => $html($it[2] ? 'Right.' : 'Not quite.'), 'feedbackfalse' => $html($it[2] ? 'Not quite.' : 'Right.')];
            break;
        case 'multi':
            $qtype = 'multichoice';
            $right = count(array_filter($it[2], fn($o) => $o[1]));
            $form = $base + $combined + ['name' => $name, 'questiontext' => $html('<p>' . s($stem) . '</p>'), 'generalfeedback' => $html(s($it[3])),
                'single' => '0', 'shuffleanswers' => 1, 'answernumbering' => 'none', 'showstandardinstruction' => 0,
                'noanswers' => count($it[2]), 'answer' => [], 'fraction' => [], 'feedback' => []];
            foreach ($it[2] as [$o, $ok]) {
                $form['answer'][] = ['text' => $o, 'format' => FORMAT_PLAIN];
                $form['fraction'][] = $ok ? csfr_fraction($right) : csfr_fraction($right, true);
                $form['feedback'][] = $html('');
            }
            break;
        case 'match':
            $qtype = 'match';
            $form = $base + $combined + ['name' => $name, 'questiontext' => $html('<p>' . s($stem) . '</p>'), 'generalfeedback' => $html(s($it[3])),
                'shuffleanswers' => 1, 'noanswers' => count($it[2]), 'subquestions' => [], 'subanswers' => []];
            foreach ($it[2] as [$l, $r]) {
                $form['subquestions'][] = $html(s($l));
                $form['subanswers'][] = $r;
            }
            break;
        case 'gap':
            $qtype = 'gapselect';
            $form = $base + $combined + ['name' => $name, 'questiontext' => $html('<p>' . s($stem) . '</p>'), 'generalfeedback' => $html(s($it[3])),
                'shuffleanswers' => 1, 'noanswers' => count($it[2]), 'choices' => []];
            foreach ($it[2] as [$o, $g]) {
                $form['choices'][] = ['answer' => $o, 'choicegroup' => $g];
            }
            break;
        default: // mc
            $qtype = 'multichoice';
            $form = $base + $combined + ['name' => $name, 'questiontext' => $html('<p>' . s($stem) . '</p>'), 'generalfeedback' => $html(s($it[4])),
                'single' => '1', 'shuffleanswers' => str_starts_with($stem, 'Talk to the victim') || count($it[2]) === 4 && $it[2][0] === 'Red' ? 0 : 1,
                'answernumbering' => 'none', 'showstandardinstruction' => 0, 'noanswers' => count($it[2]), 'answer' => [], 'fraction' => [], 'feedback' => []];
            foreach ($it[2] as $j => $o) {
                $form['answer'][] = ['text' => $o, 'format' => FORMAT_PLAIN];
                $form['fraction'][] = $j === $it[3] ? '1.0' : '0.0';
                $form['feedback'][] = $html('');
            }
    }
    return question_bank::get_qtype($qtype)->save_question((object)['qtype' => $qtype, 'category' => $catid, 'createdby' => 0, 'idnumber' => null], (object)$form);
}

/**
 * Creates "Module N practice" in section $n, before $beforecmid if given. Interactive with several tries (hints after a
 * wrong answer), unlimited attempts, highest grade kept, completion on a passing grade of 50%. Returns the quiz.
 */
function csfr_practice_quiz($gen, stdClass $course, int $n, array $items, ?int $beforecmid = null): stdClass {
    $intro = '<p>About ten quick tasks in different formats: safe or risky, spot the warning signs, the evidence bag, '
        . 'who do I call, fill the gaps and talk to the victim. Try as often as you like; your best score is kept.</p>';
    $quiz = $gen->create_module('quiz', ['course' => $course->id, 'section' => $n, 'name' => "Module $n practice",
        'intro' => $intro, 'introformat' => FORMAT_HTML, 'grade' => 10, 'gradepass' => 5, 'questionsperpage' => 1,
        'shuffleanswers' => 1, 'preferredbehaviour' => 'interactive', 'attempts' => 0, 'grademethod' => QUIZ_GRADEHIGHEST,
        'idnumber' => "csfr_practice$n", 'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionusegrade' => 1, 'completionpassgrade' => 1]);
    $cat = $gen->get_plugin_generator('core_question')->create_question_category([
        'contextid' => context_module::instance($quiz->cmid)->id, 'name' => "Module $n practice"]);
    foreach (array_values($items) as $i => $it) {
        quiz_add_quiz_question(csfr_practice_question($it, $cat->id, $i)->id, $quiz, 0, 1);
    }
    quiz_repaginate_questions($quiz->id, 1);
    \mod_quiz\quiz_settings::create($quiz->id)->get_grade_calculator()->recompute_quiz_sumgrades();
    if ($beforecmid) {
        $cm = get_coursemodule_from_id('quiz', $quiz->cmid, 0, false, MUST_EXIST);
        moveto_module($cm, get_fast_modinfo($course)->get_section_info($n), get_coursemodule_from_id(null, $beforecmid, 0, false, MUST_EXIST));
    }
    csfr_practice_grade_category($course, $quiz);
    return $quiz;
}

/** Practice is recorded but never counts towards the certificate: its own grade category, weighted 0. */
function csfr_practice_grade_category(stdClass $course, stdClass $quiz): void {
    $cat = grade_category::fetch(['courseid' => $course->id, 'fullname' => 'Module practice (recorded, not counted)']);
    if (!$cat) {
        $cat = new grade_category(['courseid' => $course->id, 'fullname' => 'Module practice (recorded, not counted)'], false);
        $cat->aggregation = GRADE_AGGREGATE_SUM;
        $cat->insert();
        $item = $cat->load_grade_item();
        $item->aggregationcoef2 = 0;
        $item->weightoverride = 1;
        $item->update();
    }
    $gi = grade_item::fetch(['courseid' => $course->id, 'itemtype' => 'mod', 'itemmodule' => 'quiz', 'iteminstance' => $quiz->id, 'itemnumber' => 0]);
    if ($gi) {
        $gi->set_parent($cat->id);
    }
}

/** The assessor rubric for the practical stations (Module 11) and the capstone (Module 10), on the 30% assignment. */
function csfr_practical_rubric(int $cmid): void {
    $context = context_module::instance($cmid);
    $gm = get_grading_manager($context, 'mod_assign', 'submissions');
    $gm->set_active_method('rubric');
    $ctl = $gm->get_controller('rubric');
    $lid = 0;
    $crit = function (string $d, array $levels) use (&$lid) {
        $ls = [];
        foreach ($levels as [$score, $def]) {
            $ls['NEWID' . (++$lid)] = ['definition' => $def, 'score' => $score];
        }
        return ['description' => $d, 'levels' => $ls];
    };
    $c = [
        $crit('Station 1 · Triage: four case cards, category, colour, first action and channel', [[0, 'Fewer than 3 right, or a RED case missed'], [2, '3 of 4 right, every RED case right'], [3, 'All 4 right, with the first action and channel']]),
        $crit('Station 2 · Evidence: list the 8 most important items and fill the evidence log', [[0, 'UTR or numbers missing'], [1, 'UTR and numbers, but gaps in URLs or times'], [2, 'UTR, beneficiary ID, URLs, numbers and times all captured; log complete']]),
        $crit('Station 3 · Reporting: the portal financial-fraud form and what you would tell 1930', [[0, 'Wrong category or mandatory fields missing'], [1, 'Category right, some fields missing'], [2, 'Category and all mandatory fields right; a clear 1930 script']]),
    ];
    foreach (['Calm, empathetic, no blame', 'Correct category and urgency', 'Stopped further loss; secured accounts', 'Evidence preserved (UTRs, URLs, numbers)',
            'Correct channels (1930, portal, bank, others)', 'Spotted the recovery scam', 'Consent and privacy respected', 'Clear written next steps'] as $d) {
        $c[] = $crit("Capstone · $d", [[0, 'Not shown'], [1, 'Partly shown'], [2, 'Clearly shown']]);
    }
    $criteria = [];
    foreach ($c as $i => $cr) {
        $criteria['NEWID' . ($i + 1)] = $cr + ['sortorder' => $i + 1];
    }
    $ctl->update_definition((object)[
        'name' => 'Practical stations and capstone',
        'description_editor' => ['text' => '<p>Stations 1-3 (Module 11) and the capstone observer rubric (Module 10). Score each row; Moodle turns the total into the grade.</p>', 'format' => FORMAT_HTML],
        'status' => gradingform_controller::DEFINITION_STATUS_READY,
        'rubric' => ['criteria' => $criteria, 'options' => ['sortlevelsasc' => 1, 'lockzeropoints' => 1, 'alwaysshowdefinition' => 1,
            'showdescriptionteacher' => 1, 'showdescriptionstudent' => 1, 'showscoreteacher' => 1, 'showscorestudent' => 1,
            'enableremarks' => 1, 'showremarksstudent' => 1]],
    ]);
}

/** A hidden page for assessors: the station case pack (not shown to learners before the assessment). */
function csfr_station_pack(): string {
    $cases = [['A caller on a video call says the citizen is under "digital arrest" and is still on the line.', 'Red · financial fraud / extortion', 'End the call; 112; stay with the person'],
        ['₹22,000 left a shopkeeper\'s account by UPI 10 minutes ago.', 'Amber · financial fraud', '1930 now, then the bank'],
        ['A fake profile uses a teacher\'s photo; nobody has asked for money.', 'Yellow · impersonation', 'Platform report + cybercrime.gov.in today'],
        ['A suspicious "KYC expired" SMS; nothing clicked.', 'Green · advisory', 'Chakshu + hygiene advice']];
    $html = '<div class="csfr-hb"><p class="hb-tagline">Assessors only: hidden from learners until the assessment day.</p>'
        . csfr_pill('Station 1 · Triage case cards', 'red') . '<div class="hb-tablewrap"><table class="hb-table"><thead><tr><th>Card</th><th>Expected</th></tr></thead><tbody>';
    foreach ($cases as $i => [$q, $c, $a]) {
        $html .= '<tr><th scope="row">' . ($i + 1) . '. ' . s($q) . '</th><td><b>' . s($c) . '</b><br>' . s($a) . '</td></tr>';
    }
    $html .= '</tbody></table></div><p class="hb-text">Pass: 3 of 4 right, including the RED card.</p>'
        . csfr_pill('Station 2 · Evidence case pack', 'navy') . '<p class="hb-text">Use the Module 10 Scenario A case (Mr. Kulkarni, fake trading app). '
        . 'Expected items: UTRs for every payment, dates and times, amounts, beneficiary UPI IDs or accounts + IFSC, group name and invite link, admin '
        . 'numbers, app name and download link, exported chat, "profit" and "tax" screenshots.</p>'
        . csfr_pill('Station 3 · Reporting', 'teal') . '<p class="hb-text">A paper copy of the cybercrime.gov.in "Report Other Cyber Crime" '
        . 'financial-fraud form: category, date, time, platform, what happened, bank or wallet, UTR, amount, the fraudster\'s account or UPI ID, '
        . 'suspect details, evidence list. Then a 30-second script of what they would tell 1930.</p></div>';
    return $html;
}

/**
 * The module check from the booklet (T1): the module's 45-item bank as a question category, and a quiz that draws
 * $draw questions from it at random for every attempt (options shuffled). Keeps the ID number csfr_check$n, which the
 * dashboards use. The returned quiz carries no fixed question list (the draw differs per attempt).
 */
function csfr_bank_quiz($gen, stdClass $course, int $n, array $bank, int $draw = 15): stdClass {
    $quiz = $gen->create_module('quiz', ['course' => $course->id, 'section' => $n, 'name' => "Module $n check",
        'intro' => "<p>$draw questions drawn at random from this module's bank of " . count($bank) . ' (the booklet\'s T1 quiz). '
            . 'Every attempt is a new mix. You can retry it as often as you like.</p>', 'introformat' => FORMAT_HTML,
        'grade' => 100, 'questionsperpage' => 5, 'shuffleanswers' => 1, 'preferredbehaviour' => 'deferredfeedback',
        'attempts' => 0, 'grademethod' => QUIZ_GRADEHIGHEST, 'idnumber' => "csfr_check$n",
        'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionusegrade' => 1]);
    $cat = $gen->get_plugin_generator('core_question')->create_question_category([
        'contextid' => context_module::instance($quiz->cmid)->id, 'name' => "Module $n bank (booklet)"]);
    foreach ($bank as $i => $q) {
        $it = ['mc', $q['q'], $q['o'], $q['a'], 'Correct answer: ' . $q['o'][$q['a']] . '.'];
        $question = csfr_practice_question($it, $cat->id, $i);
        $question->name = $q['id'] . ' · ' . $q['pc'] . ' · ' . core_text::substr($q['q'], 0, 40);
    }
    $settings = \mod_quiz\quiz_settings::create($quiz->id);
    $settings->get_structure()->add_random_questions(0, $draw, ['filter' => ['category' => [
        'jointype' => \core\output\datafilter::JOINTYPE_ANY, 'values' => [$cat->id], 'filteroptions' => ['includesubcategories' => false]]]]);
    quiz_repaginate_questions($quiz->id, 5);
    $settings->get_grade_calculator()->recompute_quiz_sumgrades();
    $quiz->questionsdata = [];
    return $quiz;
}
