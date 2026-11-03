<?php
// One-off: the programme's new name (the HOD's booklet, Section 5.1A) on the live course and its certificate.
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
$check = $DB->get_record('course_modules', ['idnumber' => 'csfr_check1'], 'course', MUST_EXIST);
$c = get_course($check->course);
$c->fullname = 'Certificate in Cyber Incident First Response (Pilot 2026)';
update_course($c);
$cc = $DB->get_record('customcert', ['course' => $c->id], '*', MUST_EXIST);
$DB->set_field('customcert', 'name', 'Certificate in Cyber Incident First Response', ['id' => $cc->id]);
$els = $DB->get_records_sql('SELECT e.* FROM {customcert_elements} e JOIN {customcert_pages} p ON p.id = e.pageid WHERE p.templateid = ?', [$cc->templateid]);
foreach ($els as $e) {
    $d = json_decode($e->data, true);
    if (!is_array($d) || !isset($d['text'])) {
        continue;
    }
    if (str_contains($d['text'], 'Community Cyber First Response')) {
        $d['text'] = 'has completed the Certificate in Cyber Incident First Response (Cyber Incident First Responder)';
    } else if (str_starts_with($d['text'], '60 notional')) {
        $d['text'] = '60 notional learning hours in two micro-credentials: MC-1 Knowledge and Skills (30 h) and MC-2 Supervised Community Practice (30 h)';
    } else {
        continue;
    }
    $DB->set_field('customcert_elements', 'data', json_encode($d), ['id' => $e->id]);
}
rebuild_course_cache($c->id, true);
cli_writeln('course: ' . get_course($c->id)->fullname);
cli_writeln('certificate: ' . $DB->get_field('customcert', 'name', ['id' => $cc->id]));
