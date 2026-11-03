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
 * Gathers the programme evidence for one CSFR course.
 *
 * Activities are found by their ID number (csfr_pretest, csfr_posttest, csfr_lesson1..9, csfr_check1..9,
 * csfr_log1..5, csfr_practical, csfr_portfolio, csfr_viva, csfr_attendance, csfr_certificate), so trainers can
 * rebuild or rename them without code changes.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard {
    /** @var int A gap longer than this ends an online study session. */
    const SESSION_GAP = 30 * MINSECS;

    /** @var array The four parts of the 60 hours: key => target hours. */
    const PARTS = ['classroom' => 15, 'online' => 15, 'practicum' => 24, 'portfolio' => 6];

    /** @var string[] Stages a learner passes through, in order. */
    const STAGES = ['registered', 'pretest', 'modules', 'posttest', 'practicum', 'assessed', 'certified'];

    /**
     * Constructor.
     *
     * @param \stdClass $course
     * @param \context_course $context
     */
    public function __construct(
        /** @var \stdClass The course. */
        private \stdClass $course,
        /** @var \context_course The course context. */
        private \context_course $context,
    ) {
    }

    /**
     * Courses that are CSFR certificate courses (they hold a csfr_pretest activity), oldest first.
     *
     * @return \stdClass[]
     */
    public static function courses(): array {
        global $DB;
        return $DB->get_records_sql("SELECT c.* FROM {course} c
                                       JOIN {course_modules} cm ON cm.course = c.id
                                      WHERE cm.idnumber = 'csfr_pretest' AND cm.deletioninprogress = 0
                                   ORDER BY c.id");
    }

    /**
     * Course modules whose ID number starts with a prefix, keyed by ID number.
     *
     * @param string $prefix
     * @return \stdClass[] idnumber, cmid, instance
     */
    public function modules(string $prefix): array {
        global $DB;
        $like = $DB->sql_like('cm.idnumber', ':prefix');
        return $DB->get_records_sql("SELECT cm.idnumber, cm.id AS cmid, cm.instance
                                       FROM {course_modules} cm
                                      WHERE cm.course = :course AND cm.deletioninprogress = 0 AND $like",
            ['course' => $this->course->id, 'prefix' => $prefix . '%']);
    }

    /**
     * One activity by ID number, or null.
     *
     * @param string $idnumber
     * @return \stdClass|null
     */
    public function module(string $idnumber): ?\stdClass {
        return $this->modules($idnumber)[$idnumber] ?? null;
    }

    /**
     * Groups the current user may see: all of them, or only their own batches (for mentors).
     *
     * @return \stdClass[]
     */
    public function visible_groups(): array {
        global $USER;
        if (has_capability('moodle/site:accessallgroups', $this->context)) {
            return groups_get_all_groups($this->course->id);
        }
        return groups_get_all_groups($this->course->id, $USER->id);
    }

    /**
     * One row per learner the current user may see (or just the given learners).
     *
     * @param int[]|null $onlyuserids
     * @return \stdClass[]
     */
    public function get_rows(?array $onlyuserids = null): array {
        global $DB;

        $learners = [];
        $groupof = [];
        $fields = 'u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename';
        if (has_capability('moodle/site:accessallgroups', $this->context) || $onlyuserids) {
            $learners = get_enrolled_users($this->context, 'moodle/course:isincompletionreports', 0, $fields,
                'u.lastname, u.firstname');
        }
        foreach (groups_get_all_groups($this->course->id) as $group) {
            foreach (groups_get_members($group->id, 'u.id') as $member) {
                $groupof[$member->id] = $group->name;
            }
        }
        if (!has_capability('moodle/site:accessallgroups', $this->context) && !$onlyuserids) {
            foreach ($this->visible_groups() as $group) {
                foreach (get_enrolled_users($this->context, 'moodle/course:isincompletionreports', $group->id, $fields,
                        'u.lastname, u.firstname') as $user) {
                    $learners[$user->id] = $user;
                }
            }
        }
        if ($onlyuserids) {
            $learners = array_intersect_key($learners, array_flip($onlyuserids));
        }
        if (!$learners) {
            return [];
        }
        [$usql, $uparams] = $DB->get_in_or_equal(array_keys($learners), SQL_PARAMS_NAMED, 'u');

        $quizgrade = function(?\stdClass $cm) use ($DB, $usql, $uparams): array {
            if (!$cm) {
                return [];
            }
            return $DB->get_records_sql_menu("SELECT userid, grade FROM {quiz_grades} WHERE quiz = :quiz AND userid $usql",
                ['quiz' => $cm->instance] + $uparams);
        };
        $pre = $quizgrade($this->module('csfr_pretest'));
        $post = $quizgrade($this->module('csfr_posttest'));

        $classroom = [];
        if ($att = $this->module('csfr_attendance')) {
            $classroom = $DB->get_records_sql_menu("SELECT al.studentid, SUM(s.duration)
                                                      FROM {attendance_log} al
                                                      JOIN {attendance_sessions} s ON s.id = al.sessionid
                                                      JOIN {attendance_statuses} st ON st.id = al.statusid
                                                     WHERE s.attendanceid = :att AND st.grade > 0 AND al.studentid $usql
                                                  GROUP BY al.studentid", ['att' => $att->instance] + $uparams);
        }

        $lessons = array_column($this->modules('csfr_lesson'), 'cmid');
        $modulesdone = [];
        if ($lessons) {
            [$lsql, $lparams] = $DB->get_in_or_equal($lessons, SQL_PARAMS_NAMED, 'l');
            $modulesdone = $DB->get_records_sql_menu("SELECT userid, COUNT(1) FROM {course_modules_completion}
                                                       WHERE coursemoduleid $lsql AND completionstate > 0 AND userid $usql
                                                    GROUP BY userid", $lparams + $uparams);
        }

        $logs = array_column($this->modules('csfr_log'), 'instance');
        $verified = [];
        if ($logs) {
            // Scale "Needs rework, Verified": a grade of 2 means the mentor verified the log.
            [$asql, $aparams] = $DB->get_in_or_equal($logs, SQL_PARAMS_NAMED, 'a');
            $verified = $DB->get_records_sql_menu("SELECT userid, COUNT(DISTINCT assignment) FROM {assign_grades}
                                                    WHERE assignment $asql AND grade >= 2 AND userid $usql
                                                 GROUP BY userid", $aparams + $uparams);
        }

        $submitted = function(?\stdClass $cm) use ($DB, $usql, $uparams): array {
            if (!$cm) {
                return [];
            }
            return $DB->get_records_sql_menu("SELECT userid, 1 FROM {assign_submission}
                                               WHERE assignment = :a AND status = 'submitted' AND latest = 1 AND userid $usql",
                ['a' => $cm->instance] + $uparams);
        };
        $graded = function(?\stdClass $cm) use ($DB, $usql, $uparams): array {
            if (!$cm) {
                return [];
            }
            return $DB->get_records_sql_menu("SELECT userid, MAX(grade) FROM {assign_grades}
                                               WHERE assignment = :a AND grade >= 0 AND userid $usql GROUP BY userid",
                ['a' => $cm->instance] + $uparams);
        };
        $portfolio = $submitted($this->module('csfr_portfolio'));
        $viva = $graded($this->module('csfr_viva'));

        $final = [];
        $grademax = 100;
        if ($courseitem = \grade_item::fetch_course_item($this->course->id)) {
            $final = $DB->get_records_sql_menu("SELECT userid, finalgrade FROM {grade_grades}
                                                 WHERE itemid = :item AND finalgrade IS NOT NULL AND userid $usql",
                ['item' => $courseitem->id] + $uparams);
            $grademax = (float)$courseitem->grademax ?: 100;
        }

        $codes = [];
        if ($cert = $this->module('csfr_certificate')) {
            $codes = $DB->get_records_sql_menu("SELECT userid, code FROM {customcert_issues}
                                                 WHERE customcertid = :cert AND userid $usql",
                ['cert' => $cert->instance] + $uparams);
        }

        [$online, $lastactive] = $this->online_seconds(array_keys($learners));
        $lessoncount = count($lessons);
        $logcount = count($logs);

        $rows = [];
        foreach ($learners as $id => $user) {
            $hours = [
                'classroom' => min(self::PARTS['classroom'], ($classroom[$id] ?? 0) / HOURSECS),
                'online' => min(self::PARTS['online'], ($online[$id] ?? 0) / HOURSECS),
                'practicum' => $logcount ? ($verified[$id] ?? 0) / $logcount * self::PARTS['practicum'] : 0,
                'portfolio' => (isset($portfolio[$id]) ? 3 : 0) + (isset($viva[$id]) ? 3 : 0),
            ];
            $row = (object)[
                'userid' => $id,
                'name' => fullname($user),
                'group' => $groupof[$id] ?? get_string('nogroup', 'local_csfr'),
                'hours' => $hours,
                'totalhours' => array_sum($hours),
                'classroomhours' => round(($classroom[$id] ?? 0) / HOURSECS, 1),
                'onlinehours' => round(($online[$id] ?? 0) / HOURSECS, 1),
                'modulesdone' => (int)($modulesdone[$id] ?? 0),
                'lessoncount' => $lessoncount,
                'pretest' => isset($pre[$id]) ? round($pre[$id]) : null,
                'posttest' => isset($post[$id]) ? round($post[$id]) : null,
                'verified' => (int)($verified[$id] ?? 0),
                'logcount' => $logcount,
                'portfolio' => isset($portfolio[$id]),
                'viva' => isset($viva[$id]),
                'finalgrade' => isset($final[$id]) ? round($final[$id] / $grademax * 100) : null,
                'certificate' => $codes[$id] ?? '',
                'lastactive' => $lastactive[$id] ?? 0,
            ];
            $row->stage = $this->stage_of($row);
            $row->status = match (true) {
                $row->certificate !== '' => 'certified',
                $row->posttest !== null => 'assessment',
                $row->pretest !== null => 'inprogress',
                default => 'notstarted',
            };
            $rows[$id] = $row;
        }
        return $rows;
    }

    /**
     * The furthest stage a learner has reached, as an index into STAGES.
     *
     * @param \stdClass $row
     * @return int
     */
    private function stage_of(\stdClass $row): int {
        $reached = [
            true,
            $row->pretest !== null,
            $row->lessoncount && $row->modulesdone >= $row->lessoncount,
            $row->posttest !== null,
            $row->logcount && $row->verified >= $row->logcount,
            $row->portfolio && $row->viva,
            $row->certificate !== '',
        ];
        $stage = 0;
        foreach ($reached as $i => $ok) {
            if (!$ok) {
                break;
            }
            $stage = $i;
        }
        return $stage;
    }

    /**
     * How many learners have reached each stage.
     *
     * @param \stdClass[] $rows
     * @return int[] stage key => count
     */
    public static function stage_counts(array $rows): array {
        $counts = array_fill_keys(self::STAGES, 0);
        foreach ($rows as $row) {
            foreach (self::STAGES as $i => $key) {
                if ($row->stage >= $i) {
                    $counts[$key]++;
                }
            }
        }
        return $counts;
    }

    /**
     * Estimated online study time per user (the sum of gaps between logged actions, ignoring gaps over
     * 30 minutes) and their last action in the course.
     *
     * @param int[] $userids
     * @return array [seconds by user id, last action time by user id]
     */
    private function online_seconds(array $userids): array {
        global $DB;
        [$usql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $rs = $DB->get_recordset_sql("SELECT id, userid, timecreated FROM {logstore_standard_log}
                                       WHERE courseid = :course AND userid $usql
                                    ORDER BY userid, timecreated", ['course' => $this->course->id] + $params);
        $seconds = [];
        $last = [];
        foreach ($rs as $log) {
            $gap = isset($last[$log->userid]) ? $log->timecreated - $last[$log->userid] : 0;
            if ($gap > 0 && $gap <= self::SESSION_GAP) {
                $seconds[$log->userid] = ($seconds[$log->userid] ?? 0) + $gap;
            }
            $last[$log->userid] = $log->timecreated;
        }
        $rs->close();
        return [$seconds, $last];
    }

    /**
     * Per-batch summary, using the handbook's knowledge-gain formula on learners who took both tests.
     *
     * @param \stdClass[] $rows from get_rows()
     * @return \stdClass[]
     */
    public function get_batches(array $rows): array {
        $batches = [];
        foreach ($rows as $row) {
            $b = $batches[$row->group] ??= (object)['name' => $row->group, 'learners' => 0, 'pre' => [], 'post' => [],
                'paired' => [], 'certified' => 0, 'hours' => 0];
            $b->learners++;
            if ($row->pretest !== null) {
                $b->pre[] = $row->pretest;
            }
            if ($row->posttest !== null) {
                $b->post[] = $row->posttest;
            }
            if ($row->pretest !== null && $row->posttest !== null) {
                $b->paired[] = [$row->pretest, $row->posttest];
            }
            $b->certified += $row->certificate !== '' ? 1 : 0;
            $b->hours += $row->totalhours;
        }
        foreach ($batches as $b) {
            $b->premean = self::mean($b->pre);
            $b->postmean = self::mean($b->post);
            $b->gain = self::gain($b->paired);
            $b->paired = count($b->paired);
            $b->hours = round($b->hours / max(1, $b->learners), 1);
        }
        ksort($batches);
        return $batches;
    }

    /**
     * Knowledge gain across the given learners: (post mean - pre mean) / pre mean, learners with both tests only.
     *
     * @param array $paired list of [pre, post]
     * @return float|null percentage
     */
    public static function gain(array $paired): ?float {
        $pre = self::mean(array_column($paired, 0));
        $post = self::mean(array_column($paired, 1));
        return $pre ? ($post - $pre) / $pre * 100 : null;
    }

    /**
     * Mean of a list, or null when it is empty.
     *
     * @param array $values
     * @return float|null
     */
    public static function mean(array $values): ?float {
        return $values ? array_sum($values) / count($values) : null;
    }
}
