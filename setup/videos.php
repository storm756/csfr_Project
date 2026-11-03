<?php
// Put (or refresh) the module films at the top of each module's lesson page on the live course, without a rebuild.
// Run: docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/videos.php

define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once(__DIR__ . '/videolib.php');

foreach (range(1, 9) as $n) {
    $cm = $DB->get_record('course_modules', ['idnumber' => "csfr_lesson$n"], '*', IGNORE_MULTIPLE);
    if (!$cm) {
        cli_writeln("module $n: no lesson page");
        continue;
    }
    $page = $DB->get_record('page', ['id' => $cm->instance], '*', MUST_EXIST);
    $page->content = csfr_with_film($page->content, $n);
    $page->revision++;
    $page->timemodified = time();
    $DB->update_record('page', $page);
    rebuild_course_cache($cm->course, true);
    cli_writeln("module $n: " . (str_contains($page->content, 'csfr-film') ? 'film in place' : 'no film file'));
}
