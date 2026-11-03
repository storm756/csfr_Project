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
 * Programme admin: every learner's progress in a CSFR course, with batches, stages and a CSV.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_csfr\dashboard;
use local_csfr\ui;

require('../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir . '/gradelib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$batch = optional_param('batch', '', PARAM_TEXT);
$status = optional_param('status', '', PARAM_ALPHA);
$q = trim(optional_param('q', '', PARAM_TEXT));
$download = optional_param('download', '', PARAM_ALPHA);

require_login();
$courses = dashboard::courses();
if (!$courses) {
    throw new moodle_exception('nocourse', 'local_csfr');
}
$course = $courses[$courseid] ?? reset($courses);
$context = context_course::instance($course->id);
require_capability('local/csfr:viewdashboard', $context);

$params = array_filter(['courseid' => $course->id, 'batch' => $batch, 'status' => $status, 'q' => $q]);
$url = new moodle_url('/local/csfr/admin.php', $params);
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('admin', 'local_csfr'));
$PAGE->set_heading(get_string('admin', 'local_csfr'));
$PAGE->add_body_class('csfr-page');
$PAGE->set_primary_active_tab('csfradmin');

$dashboard = new dashboard($course, $context);
$all = $dashboard->get_rows();
$inbatch = array_filter($all, fn($r) => $batch === '' || $r->group === $batch);
$rows = array_filter($inbatch, fn($r) => ($status === '' || $r->status === $status)
    && ($q === '' || core_text::strpos(core_text::strtolower($r->name), core_text::strtolower($q)) !== false));
$short = fn(string $group) => explode(',', $group)[0];

if ($download === 'csv') {
    $csv = new csv_export_writer();
    $csv->set_filename('csfr-learners-' . userdate(time(), '%Y%m%d'));
    $csv->add_data(['Learner', 'Batch', 'Hours of 60', 'Classroom hours', 'Online hours', 'Practicum logs verified',
        'Lessons read', 'Pre-test', 'Post-test', 'Final %', 'Status', 'Certificate code']);
    foreach ($rows as $r) {
        $csv->add_data([$r->name, $r->group, ui::hours($r->totalhours), $r->classroomhours, $r->onlinehours,
            "{$r->verified} of {$r->logcount}", "{$r->modulesdone} of {$r->lessoncount}", $r->pretest ?? '', $r->posttest ?? '',
            $r->finalgrade ?? '', get_string('status_' . $r->status, 'local_csfr'), $r->certificate]);
    }
    $csv->download_file();
    die;
}

echo $OUTPUT->header();
echo html_writer::start_div('csfr-app');

// Head: what this is, and the two things you take away from it.
echo html_writer::start_div('csfr-app__head');
echo html_writer::tag('p', get_string('admin_intro', 'local_csfr', format_string($course->fullname)), ['class' => 'csfr-lede']);
echo html_writer::div(
    html_writer::link(new moodle_url($url, ['download' => 'csv']), get_string('downloadcsv', 'local_csfr'),
        ['class' => 'btn btn-secondary']) .
    html_writer::link(new moodle_url('/course/view.php', ['id' => $course->id]), get_string('course'),
        ['class' => 'btn btn-secondary']),
    'csfr-app__actions');
echo html_writer::end_div();

// Figures across the whole course.
$paired = array_map(fn($r) => [$r->pretest, $r->posttest],
    array_filter($all, fn($r) => $r->pretest !== null && $r->posttest !== null));
$gain = dashboard::gain($paired);
$certified = count(array_filter($all, fn($r) => $r->certificate !== ''));
$avghours = dashboard::mean(array_column($all, 'totalhours')) ?? 0;
echo html_writer::div(
    ui::figure((string)count($all), get_string('learners', 'local_csfr')) .
    ui::figure((string)$certified, get_string('certified', 'local_csfr')) .
    ui::figure($gain === null ? '0' : round($gain) . '%', get_string('avggain', 'local_csfr')) .
    ui::figure(ui::hours($avghours), get_string('avghours', 'local_csfr')),
    'csfr-figures');

// Where learners are: one bar per stage.
$counts = dashboard::stage_counts($inbatch);
$total = max(1, $counts['registered']);
$funnel = '';
foreach ($counts as $stage => $n) {
    $pct = round($n / $total * 100);
    $funnel .= html_writer::tag('li',
        html_writer::span(get_string('stage_' . $stage, 'local_csfr'), 'csfr-funnel__label') .
        html_writer::span(html_writer::span('', 'csfr-funnel__fill' . ($stage === 'certified' ? ' is-done' : ''),
            ['style' => "width: {$pct}%"]), 'csfr-funnel__track') .
        html_writer::span($n, 'csfr-funnel__n'));
}
echo html_writer::start_tag('section', ['class' => 'csfr-section']);
echo $OUTPUT->heading(get_string('funnel', 'local_csfr') . ($batch !== '' ? ': ' . s($short($batch)) : ''), 2);
echo html_writer::tag('ol', $funnel, ['class' => 'csfr-funnel']);
echo html_writer::tag('p', get_string('funnel_note', 'local_csfr'), ['class' => 'csfr-note']);
echo html_writer::end_tag('section');

// Batches.
$table = new html_table();
$table->attributes['class'] = 'csfr-table';
$table->head = [get_string('batch', 'local_csfr'), ucfirst(get_string('learners', 'local_csfr')),
    get_string('premean', 'local_csfr'), get_string('postmean', 'local_csfr'), get_string('gain', 'local_csfr'),
    get_string('hours', 'local_csfr'), ucfirst(get_string('certified', 'local_csfr'))];
foreach ($dashboard->get_batches($all) as $b) {
    $g = $b->gain === null ? html_writer::span(get_string('notyet', 'local_csfr'), 'csfr-muted')
        : ui::pill(round($b->gain) . '%', $b->gain >= 25 ? 'done' : 'todo') . html_writer::span(" n={$b->paired}", 'csfr-muted');
    $table->data[] = [html_writer::link(new moodle_url($url, ['batch' => $b->name]), s($b->name)), $b->learners,
        ui::score($b->premean), ui::score($b->postmean), $g, ui::hours($b->hours), $b->certified];
}
echo html_writer::start_tag('section', ['class' => 'csfr-section']);
echo $OUTPUT->heading(get_string('batches', 'local_csfr'), 2);
echo html_writer::table($table);
echo html_writer::tag('p', get_string('gainnote', 'local_csfr'), ['class' => 'csfr-note']);
echo html_writer::end_tag('section');

// Learners, with filters in the URL.
echo html_writer::start_tag('section', ['class' => 'csfr-section', 'id' => 'learners']);
echo $OUTPUT->heading(get_string('learnerstable', 'local_csfr'), 2);
$batchopts = ['' => get_string('allbatches', 'local_csfr')];
foreach ($dashboard->get_batches($all) as $b) {
    $batchopts[$b->name] = $short($b->name);
}
$statusopts = ['' => get_string('allstatuses', 'local_csfr')];
foreach (['notstarted', 'inprogress', 'assessment', 'certified'] as $s) {
    $statusopts[$s] = get_string('status_' . $s, 'local_csfr');
}
echo html_writer::start_tag('form', ['class' => 'csfr-filters', 'method' => 'get', 'action' => new moodle_url('/local/csfr/admin.php')]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid', 'value' => $course->id]);
echo html_writer::label(get_string('batch', 'local_csfr'), 'f-batch', true, ['class' => 'visually-hidden']);
echo html_writer::select($batchopts, 'batch', $batch, false, ['id' => 'f-batch', 'class' => 'form-select']);
echo html_writer::label(get_string('status', 'local_csfr'), 'f-status', true, ['class' => 'visually-hidden']);
echo html_writer::select($statusopts, 'status', $status, false, ['id' => 'f-status', 'class' => 'form-select']);
echo html_writer::label(get_string('search', 'local_csfr'), 'f-q', true, ['class' => 'visually-hidden']);
echo html_writer::empty_tag('input', ['type' => 'search', 'name' => 'q', 'id' => 'f-q', 'value' => $q, 'class' => 'form-control',
    'placeholder' => get_string('search', 'local_csfr')]);
echo html_writer::tag('button', get_string('filter', 'local_csfr'), ['type' => 'submit', 'class' => 'btn btn-primary']);
echo html_writer::end_tag('form');

if (!$all) {
    echo html_writer::tag('p', get_string('empty_learners', 'local_csfr'), ['class' => 'csfr-empty']);
} else if (!$rows) {
    echo html_writer::tag('p', get_string('empty_filter', 'local_csfr'), ['class' => 'csfr-empty']);
} else {
    $table = new html_table();
    $table->attributes['class'] = 'csfr-table csfr-table--learners';
    $table->head = [get_string('learner', 'local_csfr'), get_string('batch', 'local_csfr'), get_string('hours', 'local_csfr'),
        get_string('modules', 'local_csfr'), get_string('pretest', 'local_csfr'), get_string('posttest', 'local_csfr'),
        get_string('practicum', 'local_csfr'), get_string('finalgrade', 'local_csfr'), get_string('status', 'local_csfr')];
    foreach ($rows as $r) {
        $name = html_writer::link(new moodle_url('/local/csfr/learner.php', ['id' => $r->userid, 'courseid' => $course->id]),
            s($r->name), ['class' => 'csfr-name']);
        $table->data[] = [$name, s($short($r->group)), ui::hours_bar($r->hours, true), "{$r->modulesdone} / {$r->lessoncount}",
            ui::score($r->pretest), ui::score($r->posttest), "{$r->verified} / {$r->logcount}", ui::score($r->finalgrade, '%'),
            ui::status($r->status)];
    }
    echo html_writer::div(html_writer::table($table), 'csfr-scroll');
}
echo html_writer::tag('p', get_string('dataestimate', 'local_csfr'), ['class' => 'csfr-note']);
echo html_writer::end_tag('section');

echo html_writer::end_div();
echo $OUTPUT->footer();
