<?php
// Package setup/scorm/src/m01 into a SCORM zip and add it to the course as the Module 1 assessment (graded /100,
// highest attempt). Safe to run again: an existing csfr_assess1 is left alone.
// Run: docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/scorm/import_m01.php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

\core\session\manager::set_user(get_admin());
$course = get_course(14);

if ($DB->record_exists('course_modules', ['idnumber' => 'csfr_assess1', 'course' => 14])) {
    cli_writeln('module 1 assessment already present; nothing to do');
    exit(0);
}

// Build the package zip (imsmanifest.xml at the root) in a temp file.
$src = '/opt/csfr-setup/scorm/src/m01';
$zippath = make_request_directory() . '/csfr_m01.zip';
$zip = new ZipArchive();
if ($zip->open($zippath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    cli_error('cannot create zip');
}
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if ($f->isFile()) {
        $local = str_replace('\\', '/', substr($f->getPathname(), strlen($src) + 1));
        $zip->addFile($f->getPathname(), $local);
    }
}
$zip->close();
cli_writeln('packaged ' . $zippath . ' (' . filesize($zippath) . ' bytes)');

// Put the zip into the admin user's draft file area and hand the generator that draft id (bypasses its
// dirroot-relative path handling).
$usercontext = context_user::instance($USER->id);
$draftid = file_get_unused_draft_itemid();
get_file_storage()->create_file_from_pathname([
    'component' => 'user', 'filearea' => 'draft', 'contextid' => $usercontext->id,
    'itemid' => $draftid, 'filepath' => '/', 'filename' => 'csfr_m01.zip',
], $zippath);

$gen = testing_util::get_data_generator();
$cm = $gen->create_module('scorm', [
    'course' => 14,
    'section' => 1,
    'name' => 'Module 1 assessment: Phone Rescue',
    'intro' => 'Ten quick challenges on cyber hygiene. You are competent at 50% or more with no safety-gate choice.',
    'introformat' => FORMAT_HTML,
    'scormtype' => 'local',
    'packagefile' => $draftid,
    'grademethod' => 1,            // GRADEHIGHEST: the highest attempt's raw score
    'maxgrade' => 100,
    'whatgrade' => 0,              // HIGHESTATTEMPT
    'idnumber' => 'csfr_assess1',
    'completion' => COMPLETION_TRACKING_AUTOMATIC,
    'completionstatusrequired' => 4,   // require lesson_status "passed"
]);
cli_writeln('created SCORM activity cmid=' . $cm->cmid);

// place it right after the Module 1 lesson/check, before the next section
rebuild_course_cache(14, true);
$gi = $DB->get_record('grade_items', ['courseid' => 14, 'itemmodule' => 'scorm', 'iteminstance' => $cm->instance]);
cli_writeln($gi ? ('grade item id=' . $gi->id . ' grademax=' . $gi->grademax) : 'WARNING: no grade item found');
cli_writeln('done');
