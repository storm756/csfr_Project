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

namespace local_csfr;

use core\hook\navigation\primary_extend;
use core_user\hook\extend_default_homepage;

/**
 * Hook callbacks: the CSFR destinations in the top bar, and My progress as a home page option.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Puts My progress (learners) or Programme admin (staff), then the course, first in the top bar.
     *
     * @param primary_extend $hook
     */
    public static function extend_primary(primary_extend $hook): void {
        global $USER;

        if (!isloggedin() || isguestuser() || during_initial_install()) {
            return;
        }
        $staff = $learner = false;
        $mycourse = null;
        foreach (dashboard::courses() as $course) {
            $context = \context_course::instance($course->id);
            if (has_capability('local/csfr:viewdashboard', $context)) {
                $staff = true;
                $mycourse = $mycourse ?? $course;
            } else if (is_enrolled($context, $USER, 'moodle/course:isincompletionreports')) {
                $learner = true;
                $mycourse = $mycourse ?? $course;
            }
        }
        // Each CSFR page names its own tab with $PAGE->set_primary_active_tab(), because Moodle picks the
        // active item after this hook runs.
        $primary = $hook->get_primaryview();
        // The site's home page is My progress (staff go on to Programme admin), so Home would repeat the item beside it.
        if ($mycourse && ($home = $primary->get('home'))) {
            $home->remove();
        }
        $keys = $primary->children->get_key_list();
        $before = $keys ? reset($keys) : null;
        $items = [];
        if ($learner) {
            $items['csfrhome'] = [get_string('myprogress', 'local_csfr'), new \moodle_url('/local/csfr/home.php')];
        }
        if ($staff) {
            $items['csfradmin'] = [get_string('admin', 'local_csfr'), new \moodle_url('/local/csfr/admin.php')];
        }
        if ($mycourse) {
            $items['csfrcourse'] = [get_string('course'), new \moodle_url('/course/view.php', ['id' => $mycourse->id])];
        }
        foreach ($items as $key => [$text, $url]) {
            $primary->add_node(\navigation_node::create($text, $url, \navigation_node::TYPE_ROOTNODE, null, $key), $before);
        }
    }

    /**
     * Offers My progress as a default home page.
     *
     * @param extend_default_homepage $hook
     */
    public static function extend_homepage(extend_default_homepage $hook): void {
        $hook->add_option(new \core\url('/local/csfr/home.php'), new \core\lang_string('myprogress', 'local_csfr'));
    }
}
