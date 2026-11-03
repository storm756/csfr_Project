<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A module's handout: its pages of the HOD's participant handbook, shown as full-width pictures on the page, so
 * nothing needs opening or downloading. The PDF stays available as a link for printing.
 *
 * Pages are drawn once with Ghostscript into moodledata (csfr-pages) and reused.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_csfr\dashboard;

require('../../config.php');
require_once($CFG->libdir . '/filelib.php');

// 0 = whole handbook, help = help card, 1-11 = a module's handbook pages, ext4-ext6 = the extended notes for 4 to 6.
$n = required_param('n', PARAM_ALPHANUM);
$img = optional_param('img', 0, PARAM_INT);
$pdf = optional_param('pdf', 0, PARAM_BOOL);

// Pages per handout. Handbook pages 33-34 (the question bank and answer key) are never shown.
$pages = ['0' => array_merge(range(1, 32), [35]), 'help' => [35], '10' => [30, 31], '11' => [32]];
foreach (range(1, 9) as $m) {
    $pages[(string)$m] = [3 * $m, 3 * $m + 1, 3 * $m + 2];
}
foreach (range(4, 6) as $m) {
    $pages["ext$m"] = range(($m - 4) * 6 + 1, ($m - 4) * 6 + 6);
}
// The HOD's complete booklet (setup/booklet.php, files/booklet.pdf) is the source when present: each module's learner
// pages, never the assessor-only ones. Otherwise the teammates' module PDFs, then the old handbook.
$booklet = is_readable('/opt/csfr-setup/booklet.php') && is_readable('/opt/csfr-setup/files/booklet.pdf')
    ? (function () { return require('/opt/csfr-setup/booklet.php'); })() : null;
$usebook = $booklet && (ctype_digit($n) && isset($booklet['pages'][(int)$n]) || $n === 'help');
if ($usebook) {
    $pages[$n] = $n === 'help' ? [299, 300] : $booklet['pages'][(int)$n];
}
// The teammates' revised module PDFs (setup/files/modules/mN.pdf) replace the handbook pages when present: all eight
// pages for Modules 1-9, both for Module 10, and only the first page of Module 11 (pages 2-3 are the pre/post-test bank
// and its answer key, which learners must never see).
$modpages = [10 => [1, 2], 11 => [1]];
$modfile = null;
if (!$booklet && ctype_digit($n) && (int)$n >= 1 && (int)$n <= 11 && is_readable("/opt/csfr-setup/files/modules/m$n.pdf")) {
    $modfile = "/opt/csfr-setup/files/modules/m$n.pdf";
    $pages[$n] = $modpages[(int)$n] ?? range(1, 8);
}
if (!isset($pages[$n])) {
    throw new moodle_exception('invalidparameter');
}
$list = $pages[$n];
$extended = str_starts_with($n, 'ext');

$courses = dashboard::courses();
$course = reset($courses);
require_login($course ?: null, false);

$source = $usebook ? '/opt/csfr-setup/files/booklet.pdf' : $modfile ?? ('/opt/csfr-setup/files/' . ($extended ? 'modules-4-6-extended.pdf' : 'participant-handbook.pdf'));
$prefix = $usebook ? 'booklet' : ($modfile ? "module$n" : ($extended ? 'extended' : 'handbook'));
$dir = $CFG->dataroot . '/csfr-pages';
check_dir_exists($dir);

if ($img) {
    if (!in_array($img, $list, true)) {
        throw new moodle_exception('invalidparameter');
    }
    $file = sprintf('%s/%s-p%02d.jpg', $dir, $prefix, $img);
    if (!is_readable($file)) {
        $cmd = sprintf('gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=jpeg -dJPEGQ=82 -r130 -dFirstPage=%d -dLastPage=%d '
            . '-sOutputFile=%s %s 2>&1', $img, $img, escapeshellarg($file), escapeshellarg($source));
        exec($cmd, $output, $status);
        if ($status !== 0 || !is_readable($file)) {
            throw new moodle_exception('generalexceptionmessage', 'error', '', "Could not draw $prefix page $img");
        }
    }
    send_file($file, basename($file), 30 * DAYSECS, 0, false, false, 'image/jpeg');
}

if ($pdf) {
    $file = sprintf('%s/%s-%s.pdf', $dir, $prefix, $n);
    if (!is_readable($file)) {
        $cmd = sprintf('gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -sPageList=%s '
            . '-sOutputFile=%s %s 2>&1', escapeshellarg(implode(',', $list)), escapeshellarg($file), escapeshellarg($source));
        exec($cmd, $output, $status);
    }
    send_file($file, $n === '0' ? 'csfr-participant-handbook.pdf' : "csfr-handout-$n.pdf", 0, 0, false, true, 'application/pdf');
}

$title = match ($n) {
    '0' => get_string('handout_all', 'local_csfr'),
    'help' => get_string('handout_help', 'local_csfr'),
    default => $extended ? get_string('handout_extended', 'local_csfr', substr($n, 3))
        : get_string('handout_module', 'local_csfr', $n),
};
$PAGE->set_url(new moodle_url('/local/csfr/handout.php', ['n' => $n]));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->add_body_class('csfr-page');
if ($course) {
    $PAGE->navbar->add(get_string('handouts', 'local_csfr'));
}

echo $OUTPUT->header();
echo html_writer::start_div('csfr-handout');
foreach ($list as $i => $p) {
    echo html_writer::empty_tag('img', [
        'src' => (new moodle_url('/local/csfr/handout.php', ['n' => $n, 'img' => $p]))->out(false),
        'alt' => get_string('handout_page', 'local_csfr', ['n' => $i + 1, 'of' => count($list)]),
        'loading' => $i < 2 ? 'eager' : 'lazy', 'width' => 1075, 'height' => 1521,
    ]);
}
echo html_writer::tag('p', html_writer::link(new moodle_url('/local/csfr/handout.php', ['n' => $n, 'pdf' => 1]),
    get_string('handout_print', 'local_csfr')), ['class' => 'csfr-handout__print']);
echo html_writer::end_div();
echo $OUTPUT->footer();
