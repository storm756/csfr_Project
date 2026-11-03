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

namespace theme_csfr;

use core\hook\output\before_standard_top_of_body_html_generation;

/**
 * Hook callbacks for the CSFR theme.
 *
 * @package    theme_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Puts the 1930 / cybercrime.gov.in help strip in the footer of learner-facing course pages only.
     * It sits at the end of the page content (not pinned), and never appears on admin, the dashboard or the
     * front page, where it is only clutter.
     *
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function add_help_strip(before_standard_top_of_body_html_generation $hook): void {
        global $PAGE;

        if ($PAGE->theme->name !== 'csfr') {
            return;
        }
        // Only on learner-facing course pages (the course page, lessons, handouts, quizzes). This hook fires before
        // $PAGE->course is populated, so key off the page layout, which is set in time: 'course' and 'incourse'
        // cover course content and exclude admin, the dashboard, the front page and user profiles.
        if (!in_array($PAGE->pagelayout, ['course', 'incourse'], true)) {
            return;
        }
        $strip = \html_writer::span(get_string('helpstrip', 'theme_csfr'))
            . \html_writer::tag('button', '&times;', ['type' => 'button', 'class' => 'csfr-helpstrip__x',
                'data-action' => 'csfr-hide-helpstrip', 'aria-label' => 'Hide the helpline bar']);
        $html = \html_writer::div($strip, 'csfr-helpstrip', ['role' => 'note', 'data-region' => 'csfr-helpstrip']);
        // Remember a learner's dismissal for the browser session; hide with no flash if already dismissed.
        $html .= <<<'JS'
<script>
(function () {
  var bar = document.currentScript.previousElementSibling;
  if (!bar) { return; }
  try { if (sessionStorage.getItem('csfrHelpstripHidden') === '1') { bar.style.display = 'none'; } } catch (e) {}
  bar.querySelector('[data-action="csfr-hide-helpstrip"]').addEventListener('click', function () {
    bar.style.display = 'none';
    try { sessionStorage.setItem('csfrHelpstripHidden', '1'); } catch (e) {}
  });
})();
</script>
JS;
        $hook->add_html($html);
    }
}
