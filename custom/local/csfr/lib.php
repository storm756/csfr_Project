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
 * CSFR programme dashboard navigation.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds the dashboard to the course "More" menu for trainers, mentors, assessors and managers.
 *
 * @param navigation_node $navigation
 * @param stdClass $course
 * @param context $context
 */
function local_csfr_extend_navigation_course($navigation, $course, $context) {
    if (!has_capability('local/csfr:viewdashboard', $context)) {
        return;
    }
    $navigation->add(get_string('dashboard', 'local_csfr'), new moodle_url('/local/csfr/index.php', ['id' => $course->id]),
        navigation_node::TYPE_SETTING, null, 'csfrdashboard', new pix_icon('i/report', ''));
}
