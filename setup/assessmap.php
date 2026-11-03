<?php
// Adds a visible "How you are assessed" page to the top of the course: every assessment type and where it lives.
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
\core\session\manager::set_user(get_admin());
$course = get_course(14);
if ($DB->record_exists('page', ['course' => 14, 'name' => 'How you are assessed'])) { exit("already there\n"); }
$rows = [
    ['In every lesson', 'Ask yourself, Myth or fact, sort-the-cases game', 'Self-check, not graded'],
    ['In every lesson, "Practise it"', 'Step-by-step drill, flip cards, lab tasks, role-play with observer rubric, worksheet', 'Done in class, peer-scored'],
    ['In every lesson', 'Crossword', 'Self-check, not graded'],
    ['Each module', 'Module practice quiz (true/false, multi-select, matching, fill the gap, talk-to-the-victim chains)', 'Unlimited attempts, does not count'],
    ['Each module', 'Module check quiz', 'Must pass to move on'],
    ['Module 10', 'Capstone scenario with observer rubric', 'Rehearsal for the assessed capstone'],
    ['Module 11', 'Final online MCQ', '20%'],
    ['Module 11', 'Practical stations and capstone, scored on an 11-criterion rubric', '30%, assessor'],
    ['Practicum', 'Two community sessions, three case logs, portfolio and report', '30%, assessor'],
    ['Practicum', 'Viva', '20%, assessor'],
];
$h = '<div class="hb"><p>Here is every kind of assessment in this course, where to find it and whether it counts.</p><table class="table"><thead><tr><th>Where</th><th>What</th><th>Counts?</th></tr></thead><tbody>';
foreach ($rows as $r) { $h .= '<tr><td>' . s($r[0]) . '</td><td>' . s($r[1]) . '</td><td>' . s($r[2]) . '</td></tr>'; }
$h .= '</tbody></table></div>';
$cm = testing_util::get_data_generator()->create_module('page', ['course' => 14, 'section' => 0, 'name' => 'How you are assessed',
    'content' => $h, 'contentformat' => FORMAT_HTML, 'printintro' => 0]);
$mi = get_fast_modinfo($course);
$after = $DB->get_field('course_modules', 'id', ['course' => 14, 'id' => $mi->get_section_info(0)->sequence ? explode(',', $mi->get_section_info(0)->sequence)[1] : 0]);
if ($after) { moveto_module(get_coursemodule_from_id(null, $cm->cmid), $mi->get_section_info(0), get_coursemodule_from_id(null, $after)); }
rebuild_course_cache(14, true);
echo "added\n";
