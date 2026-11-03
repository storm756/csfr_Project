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

use html_writer;

/**
 * The sections of a learner's record, shared by My progress and the staff view of a learner.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sections {
    /**
     * A section with a heading.
     *
     * @param string $title
     * @param string $body
     * @param string $class
     * @return string
     */
    public static function section(string $title, string $body, string $class = ''): string {
        return html_writer::tag('section', html_writer::tag('h2', s($title)) . $body, ['class' => trim('csfr-section ' . $class)]);
    }

    /**
     * The nine online lessons as a ruled list.
     *
     * @param learner $learner
     * @param bool $links link titles to the lessons (the learner's own view)
     * @return string
     */
    public static function modules(learner $learner, bool $links): string {
        $items = '';
        foreach ($learner->modules() as $m) {
            $title = $links && $m->lessonurl ? html_writer::link($m->lessonurl, s($m->title)) : s($m->title);
            $kind = ['done' => 'done', 'read' => 'plain', 'todo' => 'todo'][$m->state];
            $score = $m->check === null ? '' : html_writer::span(get_string('checkscore', 'local_csfr') . ' ' . round($m->check) . '%',
                'csfr-muted');
            if ($links) {
                // The learner's own view: one tile per module, its number on the handbook's mustard badge.
                $items .= html_writer::tag('li',
                    html_writer::span($m->n, 'csfr-tile__n') .
                    html_writer::span($m->lessonurl ? html_writer::link($m->lessonurl, s(preg_replace('/^Lesson: /', '', $m->title)))
                        : s($m->title), 'csfr-tile__title') .
                    html_writer::span(ui::pill(get_string('state_' . $m->state, 'local_csfr'), $kind) . ' ' . $score, 'csfr-tile__meta'),
                    ['class' => 'csfr-tile csfr-tile--' . $m->state]);
                continue;
            }
            $items .= html_writer::tag('li',
                html_writer::span($m->n, 'csfr-rows__n') .
                html_writer::span($title, 'csfr-rows__title') .
                html_writer::span($score, 'csfr-rows__meta') .
                ui::pill(get_string('state_' . $m->state, 'local_csfr'), $kind));
        }
        return self::section(get_string('onlinelessons', 'local_csfr'),
            html_writer::tag('ol', $items, ['class' => $links ? 'csfr-tiles' : 'csfr-rows']));
    }

    /**
     * Classroom days with attendance.
     *
     * @param learner $learner
     * @return string
     */
    public static function attendance(learner $learner): string {
        $items = '';
        foreach ($learner->attendance() as $s) {
            $kind = ['present' => 'done', 'absent' => 'todo', 'upcoming' => 'plain', 'notmarked' => 'todo'][$s->state];
            $items .= html_writer::tag('li',
                html_writer::span(userdate($s->date, '%d %b'), 'csfr-rows__n csfr-rows__date') .
                html_writer::span(s($s->label), 'csfr-rows__title') .
                ui::pill(get_string('state_' . $s->state, 'local_csfr'), $kind));
        }
        return self::section(get_string('attendance', 'local_csfr'), html_writer::tag('ul', $items, ['class' => 'csfr-rows']));
    }

    /**
     * The five practicum logs.
     *
     * @param learner $learner
     * @param bool $links
     * @return string
     */
    public static function practicum(learner $learner, bool $links): string {
        $items = '';
        foreach ($learner->practicum() as $log) {
            $kind = ['verified' => 'done', 'submitted' => 'plain', 'needsrework' => 'todo', 'todo' => 'todo'][$log->state];
            $title = $links && $log->url ? html_writer::link($log->url, s($log->name)) : s($log->name);
            $when = $log->when ? html_writer::span(userdate($log->when, '%d %b'), 'csfr-muted') : '';
            $items .= html_writer::tag('li',
                html_writer::span($title, 'csfr-rows__title') .
                html_writer::span($when, 'csfr-rows__meta') .
                ui::pill(get_string('state_' . $log->state, 'local_csfr'), $kind));
        }
        return self::section(get_string('practicum', 'local_csfr'), html_writer::tag('ul', $items, ['class' => 'csfr-rows']));
    }

    /**
     * The final assessment parts and the overall result.
     *
     * @param learner $learner
     * @return string
     */
    public static function assessments(learner $learner): string {
        $table = new \html_table();
        $table->attributes['class'] = 'csfr-table';
        $table->head = [get_string('assessment', 'local_csfr'), get_string('weight', 'local_csfr'),
            get_string('result', 'local_csfr')];
        foreach ($learner->assessments() as $a) {
            $table->data[] = [s($a->name), $a->weight . '%', ui::score($a->grade, '%')];
        }
        $total = $learner->row->finalgrade;
        $table->data[] = [html_writer::tag('strong', get_string('overall', 'local_csfr')), '100%',
            $total === null ? ui::score(null) : html_writer::tag('strong', $total . '%')];
        return self::section(get_string('assessments', 'local_csfr'), html_writer::table($table));
    }

    /**
     * The certificate: locked, or its code with download and verify links.
     *
     * @param learner $learner
     * @param bool $own the learner's own view (download) rather than staff (verify)
     * @return string
     */
    public static function certificate(learner $learner, bool $own): string {
        $c = $learner->certificate();
        if ($c->code === '') {
            $body = html_writer::div(ui::pill(get_string('state_todo', 'local_csfr'), 'todo') .
                html_writer::tag('p', get_string('certificate_locked', 'local_csfr')), 'csfr-cert');
        } else {
            $actions = $own && $c->url
                ? html_writer::link($c->url, get_string('downloadcertificate', 'local_csfr'), ['class' => 'btn btn-primary'])
                : html_writer::link($c->verifyurl, get_string('verify', 'local_csfr'), ['class' => 'btn btn-secondary']);
            $body = html_writer::div(ui::pill(get_string('status_certified', 'local_csfr'), 'done') .
                html_writer::tag('p', get_string('certificate_ready', 'local_csfr',
                    html_writer::tag('strong', s($c->code))) . ' ' . get_string('issuedon', 'local_csfr', userdate($c->issued, '%d %B %Y'))) .
                $actions, 'csfr-cert');
        }
        return self::section($own ? get_string('yourcertificate', 'local_csfr') : get_string('certificate', 'local_csfr'), $body);
    }

    /**
     * The consent record: each policy and the learner's answer.
     *
     * @param learner $learner
     * @return string
     */
    public static function consent(learner $learner): string {
        $items = '';
        foreach ($learner->consent() as $p) {
            $text = match ($p->status) {
                'accepted' => get_string('consent_accepted', 'local_csfr', userdate($p->when, '%d %b %Y')),
                'declined' => get_string('consent_declined', 'local_csfr', userdate($p->when, '%d %b %Y')),
                default => get_string('consent_none', 'local_csfr'),
            };
            $kind = ['accepted' => 'done', 'declined' => 'todo', 'none' => 'todo'][$p->status];
            // Say "optional" unless the policy's own name already does.
            $optional = $p->optional && stripos($p->name, 'optional') === false;
            $items .= html_writer::tag('li',
                html_writer::span(s($p->name) . ($optional ? ' ' . html_writer::span(get_string('optional'), 'csfr-muted') : ''),
                    'csfr-rows__title') .
                ui::pill($text, $kind));
        }
        return self::section(get_string('consent', 'local_csfr'), html_writer::tag('ul', $items, ['class' => 'csfr-rows']));
    }
}
