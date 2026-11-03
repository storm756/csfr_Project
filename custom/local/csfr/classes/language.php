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

/**
 * Keeps a learner's site language in step with the "Preferred language" they chose at sign-up (profile field
 * "language"). Only installed languages are used; the language menu still lets anyone switch at any time.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class language {
    /** Profile-field answer → Moodle language code. */
    const MAP = ['English' => 'en', 'Hindi' => 'hi', 'Marathi' => 'mr', 'Gujarati' => 'gu'];

    /**
     * Sets user.lang from the profile field after a user is created or updated.
     *
     * @param \core\event\base $event user_created or user_updated
     */
    public static function sync(\core\event\base $event): void {
        global $DB, $USER;
        $userid = $event->relateduserid ?: $event->objectid;
        $fieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'language']);
        if (!$userid || !$fieldid) {
            return;
        }
        $answer = $DB->get_field('user_info_data', 'data', ['userid' => $userid, 'fieldid' => $fieldid]);
        $lang = self::MAP[$answer] ?? null;
        $installed = get_string_manager()->get_list_of_translations();
        if (!$lang || !isset($installed[$lang]) || $DB->get_field('user', 'lang', ['id' => $userid]) === $lang) {
            return;
        }
        $DB->set_field('user', 'lang', $lang, ['id' => $userid]);
        if (!empty($USER->id) && (int)$USER->id === (int)$userid) {
            $USER->lang = $lang;
        }
    }
}
