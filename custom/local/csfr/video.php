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
 * A module film (1-9) or its poster, streamed to signed-in users. send_file() answers byte-range requests, so the
 * player can seek and start before the whole file has arrived.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_csfr\dashboard;

require('../../config.php');
require_once($CFG->libdir . '/filelib.php');

$n = required_param('n', PARAM_INT);
$f = optional_param('f', 'mp4', PARAM_ALPHA);
if ($n < 1 || $n > 9 || !in_array($f, ['mp4', 'poster'], true)) {
    throw new moodle_exception('invalidparameter');
}
$courses = dashboard::courses();
$course = reset($courses);
require_login($course ?: null, false);

$path = '/opt/csfr-setup/files/videos/m' . $n . ($f === 'poster' ? '.jpg' : '.mp4');
if (!is_readable($path)) {
    send_file_not_found();
}
// A week of browser caching; the file names change only when a film is re-cut (bump ?v= in the page then).
send_file($path, "csfr-module-$n" . ($f === 'poster' ? '.jpg' : '.mp4'), WEEKSECS, 0, false, false,
    $f === 'poster' ? 'image/jpeg' : 'video/mp4');
