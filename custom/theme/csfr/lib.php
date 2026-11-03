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
 * CSFR theme functions.
 *
 * @package    theme_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Boost's default preset wrapped in the CSFR brand variables and overrides.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_csfr_get_main_scss_content($theme) {
    global $CFG;

    $pre = file_get_contents(__DIR__ . '/scss/pre.scss');
    $boost = file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    $post = file_get_contents(__DIR__ . '/scss/post.scss');

    return $pre . "\n" . $boost . "\n" . $post;
}

/**
 * Variables that must be set before Boost's own.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_csfr_get_pre_scss($theme) {
    return '';
}
