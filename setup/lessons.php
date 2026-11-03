<?php
// Refresh the live course's lesson pages (Modules 1-9, with their films) and the Module 10 capstone page from
// modules.php / modules_ext.json, without a rebuild (learners, grades and logs stay as they are).
// Run: docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/lessons.php

define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once(__DIR__ . '/videolib.php');
require_once(__DIR__ . '/lessonlib.php');

$content = require(__DIR__ . '/content.php');
$touched = [];
$save = function (int $cmid, string $html) use ($DB, &$touched) {
    $cm = $DB->get_record('course_modules', ['id' => $cmid], '*', MUST_EXIST);
    $page = $DB->get_record('page', ['id' => $cm->instance], '*', MUST_EXIST);
    $page->content = $html;
    $page->revision++;
    $page->timemodified = time();
    $DB->update_record('page', $page);
    $touched[$cm->course] = true;
};

foreach ($content['modules'] as $n => $m) {
    $cm = $DB->get_record('course_modules', ['idnumber' => "csfr_lesson$n"], 'id', IGNORE_MULTIPLE);
    if (!$cm) {
        cli_writeln("module $n: no lesson page");
        continue;
    }
    // The same practice questions the build picks: the first three of the module's extra questions.
    $practice = array_slice(array_values(array_filter($content['extra'], fn($q) => $q['m'] === $n)), 0, 3);
    $save($cm->id, csfr_l10n(csfr_with_film(csfr_lesson($n, $m, $practice), $n)) . csfr_gate_script());
    $DB->set_field('page', 'name', "Lesson: {$m['title']}", ['id' => $DB->get_field('course_modules', 'instance', ['id' => $cm->id])]);
    cli_writeln("module $n: lesson refreshed");
}

// The capstone page has no idnumber; find it by name in section 10.
$cap = $DB->get_record_sql("SELECT cm.id FROM {course_modules} cm JOIN {modules} md ON md.id = cm.module AND md.name = 'page'
                              JOIN {page} p ON p.id = cm.instance WHERE p.name = ?", ['Capstone scenario and observer rubric'], IGNORE_MULTIPLE);
if ($cap) {
    $save($cap->id, csfr_capstone());
    cli_writeln('module 10: capstone refreshed');
}
foreach (array_keys($touched) as $courseid) {
    rebuild_course_cache($courseid, true);
}
