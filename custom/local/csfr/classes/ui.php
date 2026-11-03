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
 * Small pieces of markup shared by the CSFR pages.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ui {
    /**
     * The 60-hour bar: four segments sized by their hours, each filled by what is done.
     *
     * @param array $hours part => hours done, keys as dashboard::PARTS
     * @param bool $compact a thin bar with no labels, for table rows
     * @return string
     */
    public static function hours_bar(array $hours, bool $compact = false): string {
        $total = array_sum($hours);
        $segments = '';
        $labels = '';
        foreach (dashboard::PARTS as $part => $target) {
            $done = min($target, $hours[$part] ?? 0);
            $pct = $target ? round($done / $target * 100) : 0;
            $label = get_string('part_' . $part, 'local_csfr');
            $segments .= html_writer::span(
                html_writer::span('', 'csfr-hours__fill', ['style' => "width: {$pct}%"]),
                'csfr-hours__seg csfr-hours__seg--' . $part . ($pct >= 100 ? ' is-done' : ''),
                ['style' => "flex-grow: {$target}", 'title' => self::hours($done) . ' / ' . $target . ' h ' . $label]
            );
            if (!$compact) {
                $labels .= html_writer::span(
                    html_writer::span($label, 'csfr-hours__name') .
                    html_writer::span(self::hours($done) . ' / ' . $target . ' h', 'csfr-hours__num'),
                    'csfr-hours__label csfr-hours__label--' . $part, ['style' => "flex-grow: {$target}"]
                );
            }
        }
        $aria = get_string('hoursof', 'local_csfr', self::hours($total));
        $bar = html_writer::span($segments, 'csfr-hours__track', ['role' => 'img', 'aria-label' => $aria]);
        if ($compact) {
            return html_writer::span($bar . html_writer::span(self::hours($total), 'csfr-hours__total'),
                'csfr-hours csfr-hours--compact');
        }
        return html_writer::div($bar . html_writer::div($labels, 'csfr-hours__labels'), 'csfr-hours');
    }

    /**
     * Hours as a short number: 12, 12.5.
     *
     * @param float $h
     * @return string
     */
    public static function hours(float $h): string {
        return rtrim(rtrim(number_format($h, 1, '.', ''), '0'), '.');
    }

    /**
     * A state pill. Kinds: done (teal, means done or open), on (ink), todo (outline), plain.
     *
     * @param string $text
     * @param string $kind
     * @return string
     */
    public static function pill(string $text, string $kind = 'plain'): string {
        return html_writer::span(s($text), 'csfr-pill csfr-pill--' . $kind);
    }

    /**
     * The status pill for a learner row.
     *
     * @param string $status certified, assessment, inprogress, notstarted
     * @return string
     */
    public static function status(string $status): string {
        $kind = ['certified' => 'done', 'assessment' => 'on', 'inprogress' => 'plain', 'notstarted' => 'todo'][$status];
        return self::pill(get_string('status_' . $status, 'local_csfr'), $kind);
    }

    /**
     * A figure: a large number and what it counts.
     *
     * @param string $value
     * @param string $label
     * @return string
     */
    public static function figure(string $value, string $label): string {
        return html_writer::div(html_writer::tag('strong', $value) . html_writer::span($label), 'csfr-figure');
    }

    /**
     * A score out of 100, or a quiet "not yet".
     *
     * @param float|null $value
     * @param string $suffix
     * @return string
     */
    public static function score(?float $value, string $suffix = ''): string {
        return $value === null ? html_writer::span(get_string('notyet', 'local_csfr'), 'csfr-muted') : round($value) . $suffix;
    }
}
