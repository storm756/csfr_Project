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
 * Programme admin: one learner's full record.
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

$userid = required_param('id', PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

require_login();
$courses = dashboard::courses();
$course = $courses[$courseid] ?? reset($courses);
$context = context_course::instance($course->id);
require_capability('local/csfr:viewdashboard', $context);

$dashboard = new dashboard($course, $context);
// Mentors without access to all batches see only learners in their own batches.
if (!has_capability('moodle/site:accessallgroups', $context)) {
    $mine = array_keys($dashboard->visible_groups());
    $theirs = array_keys(groups_get_all_groups($course->id, $userid));
    if (!array_intersect($mine, $theirs)) {
        throw new required_capability_exception($context, 'moodle/site:accessallgroups', 'nopermissions', '');
    }
}
$learner = new learner($dashboard, $course, $userid);
$row = $learner->row;

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/csfr/learner.php', ['id' => $userid, 'courseid' => $course->id]));
$PAGE->set_pagelayout('report');
$PAGE->set_title($row->name);
$PAGE->set_heading($row->name);
$PAGE->add_body_class('csfr-page');
$PAGE->set_primary_active_tab('csfradmin');
$PAGE->navbar->add(get_string('admin', 'local_csfr'), new moodle_url('/local/csfr/admin.php', ['courseid' => $course->id]));
$PAGE->navbar->add($row->name);

echo $OUTPUT->header();
echo html_writer::start_div('csfr-app');

// Who, where they are, and how far along.
$profile = $learner->profile();
$facts = [
    get_string('batch', 'local_csfr') => $row->group,
    get_string('district', 'local_csfr') => $profile['district'] ?? '',
    get_string('group', 'local_csfr') => $profile['beneficiarygroup'] ?? '',
    get_string('language') => $profile['language'] ?? '',
    get_string('lastactive', 'local_csfr') => $row->lastactive ? userdate($row->lastactive, '%d %b %Y') : '',
];
$dl = '';
foreach ($facts as $k => $v) {
    $dl .= html_writer::tag('dt', s($k)) . html_writer::tag('dd', s($v ?: get_string('notyet', 'local_csfr')));
}
echo html_writer::div(
    html_writer::div(ui::status($row->status) .
        html_writer::tag('p', get_string('hoursof', 'local_csfr', html_writer::tag('strong', ui::hours($row->totalhours))), ['class' => 'csfr-bigfig']) .
        ui::hours_bar($row->hours), 'csfr-learner__progress') .
    html_writer::tag('dl', $dl, ['class' => 'csfr-facts']),
    'csfr-learner__head');

echo html_writer::div(
    html_writer::div(sections::modules($learner, false) . sections::assessments($learner), 'csfr-cols__main') .
    html_writer::div(sections::attendance($learner) . sections::practicum($learner, false) . sections::certificate($learner, false) .
        sections::consent($learner), 'csfr-cols__side'),
    'csfr-cols');

echo html_writer::tag('p', get_string('dataestimate', 'local_csfr'), ['class' => 'csfr-note']);
echo html_writer::end_div();
echo $OUTPUT->footer();
