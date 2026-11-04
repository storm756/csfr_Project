<?php
// Re-packages each SCORM source directory and replaces the stored package file in Moodle,
// then triggers a re-parse so the updated index.html (with height reporter) takes effect.
// Run: docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/scorm/update_packages.php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/mod/scorm/lib.php');
require_once($CFG->dirroot . '/mod/scorm/locallib.php');

$plan = [
    'm01' => 'csfr_assess1', 'm02' => 'csfr_assess2', 'm03' => 'csfr_assess3',
    'm04' => 'csfr_assess4', 'm05' => 'csfr_assess5', 'm06' => 'csfr_assess6',
    'm07' => 'csfr_assess7', 'm08' => 'csfr_assess8', 'm09' => 'csfr_assess9',
    'm11' => 'csfr_final',
];

$fs = get_file_storage();
foreach ($plan as $code => $idnumber) {
    $cm = $DB->get_record('course_modules', ['course' => 14, 'idnumber' => $idnumber], '*', IGNORE_MISSING);
    if (!$cm) { cli_writeln("$code: not found, skipped"); continue; }
    $scorm = $DB->get_record('scorm', ['id' => $cm->instance]);
    $context = context_module::instance($cm->id);

    $src = "/opt/csfr-setup/scorm/src/$code";
    if (!is_dir($src)) { cli_writeln("$code: source dir missing, skipped"); continue; }

    // Build zip.
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

    // Delete existing package file and upload the new one.
    $fs->delete_area_files($context->id, 'mod_scorm', 'package');
    $fs->create_file_from_pathname([
        'component' => 'mod_scorm', 'filearea' => 'package', 'contextid' => $context->id,
        'itemid' => 0, 'filepath' => '/', 'filename' => "csfr_$code.zip",
    ], $zippath);

    // Delete extracted content so Moodle re-extracts on next access.
    $fs->delete_area_files($context->id, 'mod_scorm', 'content');

    // Re-parse the package.
    scorm_parse($scorm, true);
    cli_writeln("$code ($idnumber): package updated and re-parsed");
}
rebuild_course_cache(14, true);
cli_writeln('done');
