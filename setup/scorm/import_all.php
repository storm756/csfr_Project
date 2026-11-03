<?php
// Package setup/scorm/src/m02..m09 and m11 and add each to the course as a graded SCORM activity (/100, highest
// attempt, completion = "passed"). Module assessments go in their module section; the final goes in section 11.
// Safe to run again: an activity whose idnumber already exists is skipped (so m01, imported earlier, is left alone).
// Run: docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/scorm/import_all.php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

\core\session\manager::set_user(get_admin());
$course = get_course(14);
$gen = testing_util::get_data_generator();
$usercontext = context_user::instance($USER->id);

// code => [idnumber, section, name, intro]
$plan = [
    'm02' => ['csfr_assess2', 2, 'Module 2 assessment: Scam Busters Desk', 'Spot the scam pattern: hook, hurry, ask, cash-out.'],
    'm03' => ['csfr_assess3', 3, 'Module 3 assessment: The Verification Desk', 'Verify before you trust, pay or forward.'],
    'm04' => ['csfr_assess4', 4, 'Module 4 assessment: Triage Desk', 'Grade urgency by danger, active loss and spread.'],
    'm05' => ['csfr_assess5', 5, 'Module 5 assessment: The Evidence Lab', 'Keep the original untouched; register and prove integrity.'],
    'm06' => ['csfr_assess6', 6, 'Module 6 assessment: The Routing Room', 'Right door, right details, right time; promise no outcome.'],
    'm07' => ['csfr_assess7', 7, 'Module 7 assessment: The Support Circuit', 'Never blame. Look, listen, link. Safety first.'],
    'm08' => ['csfr_assess8', 8, 'Module 8 assessment: The Recovery Clinic', 'Disconnect, do not wipe; never pay, always escalate.'],
    'm09' => ['csfr_assess9', 9, 'Module 9 assessment: Secure the Smart Home', 'Change defaults, router first; for OT, escalate.'],
    'm11' => ['csfr_final', 11, 'Final assessment: The Final Shift', 'Eighteen challenges, two per module. Prevent, recognise, respond, report.'],
];

foreach ($plan as $code => [$idnumber, $section, $name, $intro]) {
    if ($DB->record_exists('course_modules', ['idnumber' => $idnumber, 'course' => 14])) {
        cli_writeln("$code: $idnumber already present, skipped");
        continue;
    }
    $src = "/opt/csfr-setup/scorm/src/$code";
    $zippath = make_request_directory() . "/csfr_$code.zip";
    $zip = new ZipArchive();
    $zip->open($zippath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isFile()) {
            $zip->addFile($f->getPathname(), str_replace('\\', '/', substr($f->getPathname(), strlen($src) + 1)));
        }
    }
    $zip->close();

    $draftid = file_get_unused_draft_itemid();
    get_file_storage()->create_file_from_pathname([
        'component' => 'user', 'filearea' => 'draft', 'contextid' => $usercontext->id,
        'itemid' => $draftid, 'filepath' => '/', 'filename' => "csfr_$code.zip",
    ], $zippath);

    $cm = $gen->create_module('scorm', [
        'course' => 14, 'section' => $section, 'name' => $name,
        'intro' => $intro, 'introformat' => FORMAT_HTML,
        'scormtype' => 'local', 'packagefile' => $draftid,
        'grademethod' => 1, 'maxgrade' => 100, 'whatgrade' => 0,
        'idnumber' => $idnumber,
        'hidetoc' => SCORM_TOC_HIDDEN,
        'width' => 100,
        'height' => 850,
        'nav' => 0,
        'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionstatusrequired' => 4,
    ]);
    cli_writeln("$code: created '$name' cmid={$cm->cmid} in section $section");
}
rebuild_course_cache(14, true);
cli_writeln('done');
