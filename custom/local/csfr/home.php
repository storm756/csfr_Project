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
 * My progress: the home page after login. Learners see their certificate's progress; staff go to Programme admin.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_csfr\dashboard;
use local_csfr\learner;
use local_csfr\sections;
use local_csfr\ui;

require('../../config.php');
require_once($CFG->libdir . '/gradelib.php');

require_login();

$mine = null;
foreach (dashboard::courses() as $course) {
    $context = context_course::instance($course->id);
    if (has_capability('local/csfr:viewdashboard', $context)) {
        redirect(new moodle_url('/local/csfr/admin.php', ['courseid' => $course->id]));
    }
    if (!$mine && is_enrolled($context, $USER, 'moodle/course:isincompletionreports')) {
        $mine = [$course, $context];
    }
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/csfr/home.php'));
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('myprogress', 'local_csfr'));
$PAGE->set_heading(get_string('yourcertificate', 'local_csfr'));
$PAGE->add_body_class('csfr-page');
$PAGE->set_primary_active_tab('csfrhome');

echo $OUTPUT->header();
echo html_writer::start_div('csfr-app csfr-home');

if (!$mine) {
    echo html_writer::tag('p', get_string('nocourse', 'local_csfr'), ['class' => 'csfr-empty']);
    echo html_writer::end_div();
    echo $OUTPUT->footer();
    die;
}

[$course, $context] = $mine;
$learner = new learner(new dashboard($course, $context), $course, $USER->id);
$row = $learner->row;
[$nextlabel, $nexturl] = $learner->next_step();

// The signature: how far along the 60 hours, and the one thing to do next.
echo html_writer::div(
    html_writer::div(
        html_writer::tag('p', s(format_string($course->fullname)) . html_writer::empty_tag('br') . s($row->group),
            ['class' => 'csfr-lede']) .
        html_writer::tag('p', get_string('hoursof', 'local_csfr', html_writer::tag('strong', ui::hours($row->totalhours))),
            ['class' => 'csfr-bigfig']) .
        ui::hours_bar($row->hours), 'csfr-home__progress') .
    html_writer::div(
        html_writer::tag('h2', get_string('nextstep', 'local_csfr')) .
        html_writer::tag('p', s($nextlabel), ['class' => 'csfr-next__what']) .
        ($nexturl ? html_writer::link($nexturl, get_string('continue'), ['class' => 'btn btn-primary btn-lg']) : ''),
        'csfr-next'),
    'csfr-home__head');

echo html_writer::div(
    html_writer::div(sections::modules($learner, true) . sections::assessments($learner), 'csfr-cols__main') .
    html_writer::div(sections::practicum($learner, true) . sections::attendance($learner) .
        sections::certificate($learner, true), 'csfr-cols__side'),
    'csfr-cols');

// Your data under the DPDP Act: Moodle's own export and deletion requests, and the consent page.
$datalinks = [
    ['download', 'datacopy', new moodle_url('/admin/tool/dataprivacy/createdatarequest.php', ['type' => 1])],
    ['trash-can', 'datadelete', new moodle_url('/admin/tool/dataprivacy/createdatarequest.php', ['type' => 2])],
    ['file-signature', 'dataconsents', new moodle_url('/admin/tool/policy/user.php')],
];
$links = '';
foreach ($datalinks as [$icon, $string, $url]) {
    $links .= html_writer::link($url, html_writer::tag('i', '', ['class' => "fa fa-$icon", 'aria-hidden' => 'true']) .
        html_writer::span(get_string($string, 'local_csfr')), ['class' => 'csfr-datalink']);
}
echo html_writer::tag('section', html_writer::tag('h2', get_string('yourdata', 'local_csfr')) .
    html_writer::tag('p', get_string('datahelp', 'local_csfr'), ['class' => 'csfr-lede']) .
    html_writer::div($links, 'csfr-datalinks'), ['class' => 'csfr-section csfr-data']);
echo html_writer::tag('p', get_string('dataestimate', 'local_csfr'), ['class' => 'csfr-note']);
echo html_writer::end_div();
echo $OUTPUT->footer();
