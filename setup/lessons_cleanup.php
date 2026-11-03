<?php
// One-off: remove the "Extended notes and practice exercises, Module 4-6" handouts (their content is now in the modules).
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/clilib.php');
$rows = $DB->get_records_sql("SELECT cm.id, u.name FROM {course_modules} cm JOIN {modules} m ON m.id = cm.module AND m.name = 'url'
                                JOIN {url} u ON u.id = cm.instance WHERE u.name LIKE 'Extended notes and practice exercises%'");
foreach ($rows as $r) {
    course_delete_module($r->id);
    cli_writeln("removed: $r->name");
}
