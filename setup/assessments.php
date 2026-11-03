<?php
// Add the graded module practice (Modules 1-9), the assessor rubric on the practical stations and capstone, and the
// assessors' station case pack to the live course, without a rebuild. Safe to run again: existing pieces are skipped.
// Run: docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/assessments.php

define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/lessonlib.php');
require_once(__DIR__ . '/practicelib.php');

\core\session\manager::set_user(get_admin());
$gen = testing_util::get_data_generator();
$items = require(__DIR__ . '/practice.php');

$check = $DB->get_record('course_modules', ['idnumber' => 'csfr_check1'], '*', IGNORE_MULTIPLE);
$course = get_course($check->course);

foreach ($items as $n => $list) {
    if ($DB->record_exists('course_modules', ['idnumber' => "csfr_practice$n", 'course' => $course->id])) {
        $cmid = $DB->get_field('course_modules', 'id', ['idnumber' => "csfr_practice$n", 'course' => $course->id]);
        $before = $DB->get_field('course_modules', 'id', ['idnumber' => "csfr_check$n", 'course' => $course->id]);
        if ($before) {
            moveto_module(get_coursemodule_from_id(null, $cmid, 0, false, MUST_EXIST), get_fast_modinfo($course)->get_section_info($n),
                get_coursemodule_from_id(null, $before, 0, false, MUST_EXIST));
        }
        cli_writeln("module $n: practice already there");
        continue;
    }
    $before = $DB->get_field('course_modules', 'id', ['idnumber' => "csfr_check$n", 'course' => $course->id]);
    csfr_practice_quiz($gen, $course, $n, $list, $before ?: null);
    cli_writeln("module $n: practice added (" . count($list) . ' items)');
}

$assign = $DB->get_record_sql("SELECT cm.id FROM {course_modules} cm JOIN {modules} m ON m.id = cm.module AND m.name = 'assign'
                                 JOIN {assign} a ON a.id = cm.instance WHERE cm.course = ? AND a.name LIKE 'Practical stations and capstone%'", [$course->id]);
if ($assign) {
    csfr_practical_rubric($assign->id);
    cli_writeln('rubric: attached to the practical stations and capstone');
}

if (!$DB->record_exists('page', ['course' => $course->id, 'name' => 'Station case pack (assessors only)'])) {
    $gen->create_module('page', ['course' => $course->id, 'section' => 11, 'name' => 'Station case pack (assessors only)',
        'content' => csfr_station_pack(), 'contentformat' => FORMAT_HTML, 'visible' => 0, 'printintro' => 0]);
    cli_writeln('station case pack: added, hidden from learners');
}
grade_regrade_final_grades($course->id);
rebuild_course_cache($course->id, true);
