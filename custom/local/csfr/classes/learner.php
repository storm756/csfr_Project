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

use moodle_url;

/**
 * Everything about one learner's progress in one CSFR course.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class learner {
    /** @var \stdClass The summary row from dashboard::get_rows(). */
    public \stdClass $row;

    /** @var \course_modinfo */
    private \course_modinfo $modinfo;

    /**
     * Constructor.
     *
     * @param dashboard $dashboard
     * @param \stdClass $course
     * @param int $userid
     */
    public function __construct(
        /** @var dashboard */
        private dashboard $dashboard,
        /** @var \stdClass */
        private \stdClass $course,
        /** @var int */
        public int $userid,
    ) {
        $rows = $dashboard->get_rows([$userid]);
        if (!isset($rows[$userid])) {
            throw new \moodle_exception('invaliduser');
        }
        $this->row = $rows[$userid];
        $this->modinfo = get_fast_modinfo($course);
    }

    /**
     * The course module's view URL and name.
     *
     * @param \stdClass|null $cm from dashboard::module()
     * @return array [url|null, name]
     */
    private function link(?\stdClass $cm): array {
        if (!$cm) {
            return [null, ''];
        }
        $info = $this->modinfo->get_cm($cm->cmid);
        return [$info->url, $info->name];
    }

    /**
     * The nine online lessons with their checks.
     *
     * @return \stdClass[] n, title, viewed (time or 0), check (grade or null), lessonurl, checkurl, state
     */
    public function modules(): array {
        global $DB;
        $out = [];
        for ($n = 1; $n <= 9; $n++) {
            $lesson = $this->dashboard->module("csfr_lesson$n");
            $check = $this->dashboard->module("csfr_check$n");
            if (!$lesson) {
                continue;
            }
            [$lessonurl, $name] = $this->link($lesson);
            [$checkurl] = $this->link($check);
            $viewed = (int)$DB->get_field('course_modules_completion', 'timemodified',
                ['coursemoduleid' => $lesson->cmid, 'userid' => $this->userid, 'completionstate' => COMPLETION_COMPLETE]);
            $grade = $check ? $DB->get_field('quiz_grades', 'grade', ['quiz' => $check->instance, 'userid' => $this->userid]) : false;
            $grade = $grade === false ? null : (float)$grade;
            $out[] = (object)[
                'n' => $n,
                'title' => preg_replace('/^Online lesson:\s*/', '', $name),
                'viewed' => $viewed,
                'check' => $grade,
                'lessonurl' => $lessonurl,
                'checkurl' => $checkurl,
                'state' => $viewed && $grade !== null ? 'done' : ($viewed ? 'read' : 'todo'),
            ];
        }
        return $out;
    }

    /**
     * Classroom sessions for the learner's batch, with their attendance.
     *
     * @return \stdClass[] date, label, state
     */
    public function attendance(): array {
        global $DB;
        $att = $this->dashboard->module('csfr_attendance');
        if (!$att) {
            return [];
        }
        $groups = array_keys(groups_get_all_groups($this->course->id, $this->userid));
        [$gsql, $gparams] = $DB->get_in_or_equal(array_merge([0], $groups), SQL_PARAMS_NAMED, 'g');
        $sessions = $DB->get_records_sql("SELECT s.id, s.sessdate, s.duration, s.description, s.lasttaken,
                                                 st.acronym, st.grade
                                            FROM {attendance_sessions} s
                                       LEFT JOIN {attendance_log} al ON al.sessionid = s.id AND al.studentid = :u
                                       LEFT JOIN {attendance_statuses} st ON st.id = al.statusid
                                           WHERE s.attendanceid = :att AND s.groupid $gsql
                                        ORDER BY s.sessdate",
            ['u' => $this->userid, 'att' => $att->instance] + $gparams);
        $out = [];
        foreach ($sessions as $s) {
            $state = match (true) {
                $s->acronym !== null && $s->grade > 0 => 'present',
                $s->acronym !== null => 'absent',
                $s->sessdate > time() => 'upcoming',
                default => 'notmarked',
            };
            $out[] = (object)['date' => $s->sessdate, 'label' => strip_tags($s->description), 'state' => $state];
        }
        return $out;
    }

    /**
     * The five practicum logs.
     *
     * @return \stdClass[] name, url, state, when
     */
    public function practicum(): array {
        global $DB;
        $out = [];
        for ($n = 1; $n <= 5; $n++) {
            $log = $this->dashboard->module("csfr_log$n");
            if (!$log) {
                continue;
            }
            [$url, $name] = $this->link($log);
            $sub = $DB->get_record('assign_submission', ['assignment' => $log->instance, 'userid' => $this->userid, 'latest' => 1]);
            $grade = $DB->get_records('assign_grades', ['assignment' => $log->instance, 'userid' => $this->userid],
                'attemptnumber DESC', 'id, grade, timemodified', 0, 1);
            $grade = $grade ? reset($grade) : null;
            $state = match (true) {
                $grade && $grade->grade >= 2 => 'verified',
                $grade && $grade->grade >= 1 => 'needsrework',
                $sub && $sub->status === 'submitted' => 'submitted',
                default => 'todo',
            };
            $out[] = (object)[
                'name' => preg_replace('/^Practicum:\s*/', '', $name),
                'url' => $url,
                'state' => $state,
                'when' => $grade->timemodified ?? ($sub->timemodified ?? 0),
            ];
        }
        return $out;
    }

    /**
     * The four graded parts of the final assessment and the course total.
     *
     * @return \stdClass[] key, name, weight, grade (0 to 100 or null), url
     */
    public function assessments(): array {
        global $DB;
        $out = [];
        $modweight = round(50 / 9, 1);
        $parts = [];
        for ($i = 1; $i <= 9; $i++) {
            $parts[] = ["csfr_assess$i", 'scorm', $modweight];
        }
        $parts[] = ['csfr_final', 'scorm', 50];
        foreach ($parts as [$idnumber, $mod, $weight]) {
            $cm = $this->dashboard->module($idnumber);
            if (!$cm) {
                continue;
            }
            [$url, $name] = $this->link($cm);
            $item = \grade_item::fetch(['courseid' => $this->course->id, 'itemtype' => 'mod', 'itemmodule' => $mod,
                'iteminstance' => $cm->instance, 'itemnumber' => 0]);
            $grade = null;
            if ($item && ($gg = $DB->get_record('grade_grades', ['itemid' => $item->id, 'userid' => $this->userid]))
                    && $gg->finalgrade !== null) {
                $grade = $gg->finalgrade / ($item->grademax ?: 100) * 100;
            }
            $out[] = (object)['key' => $idnumber, 'name' => preg_replace('/\s*\(\d+%.*\)$/', '', $name), 'weight' => $weight,
                'grade' => $grade, 'url' => $url];
        }
        return $out;
    }

    /**
     * The certificate, issued or not.
     *
     * @return \stdClass code (or ''), issued (time or 0), url
     */
    public function certificate(): \stdClass {
        global $DB;
        $cm = $this->dashboard->module('csfr_certificate');
        $issue = $cm ? $DB->get_record('customcert_issues', ['customcertid' => $cm->instance, 'userid' => $this->userid]) : null;
        [$url] = $this->link($cm);
        return (object)['code' => $issue->code ?? '', 'issued' => $issue->timecreated ?? 0, 'url' => $url,
            'verifyurl' => $issue ? new moodle_url('/mod/customcert/verify_certificate.php', ['code' => $issue->code, 'qrcode' => 1]) : null];
    }

    /**
     * The learner's answers to the site policies (the DPDP consent record).
     *
     * @return \stdClass[] name, optional, status (accepted, declined, none), when
     */
    public function consent(): array {
        global $DB;
        $versions = $DB->get_records_sql("SELECT v.id, v.name, v.optional
                                             FROM {tool_policy_versions} v
                                             JOIN {tool_policy} p ON p.currentversionid = v.id
                                         ORDER BY p.sortorder");
        $out = [];
        foreach ($versions as $v) {
            $a = $DB->get_record('tool_policy_acceptances', ['policyversionid' => $v->id, 'userid' => $this->userid]);
            $out[] = (object)['name' => $v->name, 'optional' => (bool)$v->optional,
                'status' => !$a ? 'none' : ($a->status ? 'accepted' : 'declined'), 'when' => $a->timemodified ?? 0];
        }
        return $out;
    }

    /**
     * Profile fields the programme collects (district, group, language).
     *
     * @return array shortname => value
     */
    public function profile(): array {
        global $DB;
        return $DB->get_records_sql_menu("SELECT f.shortname, d.data
                                            FROM {user_info_data} d
                                            JOIN {user_info_field} f ON f.id = d.fieldid
                                           WHERE d.userid = :u AND f.shortname IN ('district', 'beneficiarygroup', 'language')",
            ['u' => $this->userid]);
    }

    /**
     * The one thing to do next, in programme order.
     *
     * @return array [label, url|null]
     */
    public function next_step(): array {
        $row = $this->row;
        if ($row->pretest === null) {
            return [get_string('nextstep_pretest', 'local_csfr'), $this->link($this->dashboard->module('csfr_pretest'))[0]];
        }
        foreach ($this->modules() as $m) {
            if (!$m->viewed) {
                return [get_string('nextstep_lesson', 'local_csfr', $m->n), $m->lessonurl];
            }
            if ($m->check === null) {
                return [get_string('nextstep_check', 'local_csfr', $m->n), $m->checkurl];
            }
        }
        if ($row->certificate === '') {
            return [get_string('nextstep_cert', 'local_csfr'), $this->certificate()->url];
        }
        return [get_string('nextstep_cert', 'local_csfr'), $this->certificate()->url];
    }
}
