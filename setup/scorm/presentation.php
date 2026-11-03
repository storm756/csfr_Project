<?php
// Applies the native-looking player configuration to every CSFR SCORM assessment without changing its package,
// attempts, scores, completion record or gradebook item. Safe to run repeatedly on the live demo.
// Run: docker compose exec -u www-data web php /opt/csfr-setup/scorm/presentation.php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/mod/scorm/lib.php');

$courseid = 14;
$ids = ['csfr_assess1', 'csfr_assess2', 'csfr_assess3', 'csfr_assess4', 'csfr_assess5',
    'csfr_assess6', 'csfr_assess7', 'csfr_assess8', 'csfr_assess9', 'csfr_final'];

foreach ($ids as $idnumber) {
    $cm = $DB->get_record('course_modules', ['course' => $courseid, 'idnumber' => $idnumber], 'instance', IGNORE_MISSING);
    if (!$cm) {
        cli_writeln("$idnumber: not present, skipped");
        continue;
    }
    $DB->update_record('scorm', (object)[
        'id' => $cm->instance,
        'hidetoc' => SCORM_TOC_HIDDEN,
        'width' => 100,
        'height' => 850,
        'nav' => 0,
    ]);
    cli_writeln("$idnumber: full-width player configured");
}
rebuild_course_cache($courseid, true);
purge_all_caches();
cli_writeln('SCORM presentation configuration complete.');
