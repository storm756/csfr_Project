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
 * A module's handout: readable web text where the curated booklet text exists, with the source pages and PDF kept
 * available for reference and printing. Modules without curated text remain safely on the original page view.
 *
 * Pages are drawn once with Ghostscript into moodledata (csfr-pages) and reused.
 *
 * @package    local_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_csfr\dashboard;

require('../../config.php');
require_once($CFG->libdir . '/filelib.php');

/** Renders the curated handbook block structure used by the web handouts. */
function csfr_handout_blocks(array $blocks, array $translations = [], array $labels = []): string {
    $tr = static fn(string $text): string => $translations[$text] ?? $text;
    $inner = function (array $items) use (&$inner, $tr, $labels): string {
        $html = '';
        foreach ($items as $block) {
            switch ($block['type']) {
                case 'h':
                    $html .= html_writer::tag('h3', s($tr($block['text'])), ['class' => 'csfr-handout__subhead']);
                    break;
                case 'p':
                    $html .= html_writer::tag('p', s($tr($block['text'])), ['class' => 'csfr-handout__text']);
                    break;
                case 'ul':
                    $html .= html_writer::start_tag('ul', ['class' => 'csfr-handout__list']);
                    foreach ($block['items'] as $item) {
                        $html .= html_writer::tag('li', s($tr($item)));
                    }
                    $html .= html_writer::end_tag('ul');
                    break;
                case 'kv':
                case 'cols':
                    $rows = $block['rows'];
                    $head = $block['type'] === 'cols' ? $block['head'] : [];
                    $html .= html_writer::start_tag('div', ['class' => 'csfr-handout__tablewrap'])
                        . html_writer::start_tag('table', ['class' => 'csfr-handout__table']);
                    if ($head) {
                        $html .= html_writer::start_tag('thead') . html_writer::start_tag('tr');
                        foreach ($head as $cell) {
                            $html .= html_writer::tag('th', s($tr($cell)), ['scope' => 'col']);
                        }
                        $html .= html_writer::end_tag('tr') . html_writer::end_tag('thead');
                    }
                    $html .= html_writer::start_tag('tbody');
                    foreach ($rows as $row) {
                        $html .= html_writer::start_tag('tr');
                        foreach ($row as $index => $cell) {
                            $tag = !$head && $index === 0 ? 'th' : 'td';
                            $attrs = $tag === 'th' ? ['scope' => 'row'] : [];
                            $html .= html_writer::tag($tag, s($tr($cell)), $attrs);
                        }
                        $html .= html_writer::end_tag('tr');
                    }
                    $html .= html_writer::end_tag('tbody') . html_writer::end_tag('table') . html_writer::end_tag('div');
                    break;
                case 'ssr':
                    $html .= html_writer::start_tag('ol', ['class' => 'csfr-handout__ssr']);
                    foreach (['Stop' => $block['stop'], 'Save' => $block['save'], 'Report' => $block['report']] as $label => $text) {
                        if (trim($text) !== '') {
                            $html .= html_writer::tag('li', html_writer::tag('strong', $labels[$label] ?? $label) . html_writer::tag('p', s($tr($text))));
                        }
                    }
                    $html .= html_writer::end_tag('ol');
                    break;
                case 'case':
                case 'remember':
                    $label = $labels[$block['type'] === 'case' ? 'A real case' : 'Remember'] ?? ($block['type'] === 'case' ? 'A real case' : 'Remember');
                    $html .= html_writer::tag('aside', html_writer::tag('h4', $label) . $inner($block['blocks']),
                        ['class' => 'csfr-handout__note csfr-handout__note--' . $block['type']]);
                    break;
            }
        }
        return $html;
    };
    return $inner($blocks);
}

// 0 = whole handbook, help = help card, 1-11 = a module's handbook pages, ext4-ext6 = the extended notes for 4 to 6.
$n = required_param('n', PARAM_ALPHANUM);
$img = optional_param('img', 0, PARAM_INT);
$pdf = optional_param('pdf', 0, PARAM_BOOL);

// Pages per handout. Handbook pages 33-34 (the question bank and answer key) are never shown.
$pages = ['0' => array_merge(range(1, 32), [35]), 'help' => [35], '10' => [30, 31], '11' => [32]];
foreach (range(1, 9) as $m) {
    $pages[(string)$m] = [3 * $m, 3 * $m + 1, 3 * $m + 2];
}
foreach (range(4, 6) as $m) {
    $pages["ext$m"] = range(($m - 4) * 6 + 1, ($m - 4) * 6 + 6);
}
// The HOD's complete booklet (setup/booklet.php, files/booklet.pdf) is the source when present: each module's learner
// pages, never the assessor-only ones. Otherwise the teammates' module PDFs, then the old handbook.
$booklet = is_readable('/opt/csfr-setup/booklet.php') && is_readable('/opt/csfr-setup/files/booklet.pdf')
    ? (function () { return require('/opt/csfr-setup/booklet.php'); })() : null;
$usebook = $booklet && (ctype_digit($n) && isset($booklet['pages'][(int)$n]) || $n === 'help');
if ($usebook) {
    $pages[$n] = $n === 'help' ? [299, 300] : $booklet['pages'][(int)$n];
}
// The teammates' revised module PDFs (setup/files/modules/mN.pdf) replace the handbook pages when present: all eight
// pages for Modules 1-9, both for Module 10, and only the first page of Module 11 (pages 2-3 are the pre/post-test bank
// and its answer key, which learners must never see).
$modpages = [10 => [1, 2], 11 => [1]];
$modfile = null;
if (!$booklet && ctype_digit($n) && (int)$n >= 1 && (int)$n <= 11 && is_readable("/opt/csfr-setup/files/modules/m$n.pdf")) {
    $modfile = "/opt/csfr-setup/files/modules/m$n.pdf";
    $pages[$n] = $modpages[(int)$n] ?? range(1, 8);
}
if (!isset($pages[$n])) {
    throw new moodle_exception('invalidparameter');
}
$list = $pages[$n];
$extended = str_starts_with($n, 'ext');

$courses = dashboard::courses();
$course = reset($courses);
require_login($course ?: null, false);

// These are answer keys, not participant material. Exclude them from every learner response (including a directly
// requested image or generated PDF), while allowing course staff to retain the reference they need for facilitation.
$traineronly = ['1' => [43], '2' => [70]];
$context = $course ? context_course::instance($course->id) : null;
$canviewtrainer = $context && has_capability('moodle/course:manageactivities', $context);
if (!$canviewtrainer && isset($traineronly[$n])) {
    $list = array_values(array_diff($list, $traineronly[$n]));
}

$source = $usebook ? '/opt/csfr-setup/files/booklet.pdf' : $modfile ?? ('/opt/csfr-setup/files/' . ($extended ? 'modules-4-6-extended.pdf' : 'participant-handbook.pdf'));
$prefix = $usebook ? 'booklet' : ($modfile ? "module$n" : ($extended ? 'extended' : 'handbook'));
$dir = $CFG->dataroot . '/csfr-pages';
check_dir_exists($dir);

if ($img) {
    if (!in_array($img, $list, true)) {
        throw new moodle_exception('invalidparameter');
    }
    $file = sprintf('%s/%s-p%02d.jpg', $dir, $prefix, $img);
    if (!is_readable($file)) {
        $cmd = sprintf('gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=jpeg -dJPEGQ=82 -r130 -dFirstPage=%d -dLastPage=%d '
            . '-sOutputFile=%s %s 2>&1', $img, $img, escapeshellarg($file), escapeshellarg($source));
        exec($cmd, $output, $status);
        if ($status !== 0 || !is_readable($file)) {
            throw new moodle_exception('generalexceptionmessage', 'error', '', "Could not draw $prefix page $img");
        }
    }
    send_file($file, basename($file), 30 * DAYSECS, 0, false, false, 'image/jpeg');
}

if ($pdf) {
    $file = sprintf('%s/%s-%s.pdf', $dir, $prefix, $n);
    if (!is_readable($file)) {
        $cmd = sprintf('gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -sPageList=%s '
            . '-sOutputFile=%s %s 2>&1', escapeshellarg(implode(',', $list)), escapeshellarg($file), escapeshellarg($source));
        exec($cmd, $output, $status);
    }
    send_file($file, $n === '0' ? 'csfr-participant-handbook.pdf' : "csfr-handout-$n.pdf", 0, 0, false, true, 'application/pdf');
}

$title = match ($n) {
    '0' => get_string('handout_all', 'local_csfr'),
    'help' => get_string('handout_help', 'local_csfr'),
    default => $extended ? get_string('handout_extended', 'local_csfr', substr($n, 3))
        : get_string('handout_module', 'local_csfr', $n),
};
$PAGE->set_url(new moodle_url('/local/csfr/handout.php', ['n' => $n]));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->add_body_class('csfr-page');
if ($course) {
    $PAGE->navbar->add(get_string('handouts', 'local_csfr'));
}

// The full booklet has clean, reviewed web-text data for Modules 1–9. Do not guess at Modules 10–11: their
// original pages remain the authoritative and safe presentation until equivalent structured text is supplied.
$webtopics = [];
$webmeta = null;
$handouttranslations = [];
$handoutlabels = [];
if ($usebook && ctype_digit($n) && (int)$n >= 1 && (int)$n <= 9) {
    $textfile = '/opt/csfr-setup/booklet_text.json';
    $alltext = is_readable($textfile) ? json_decode(file_get_contents($textfile), true) : null;
    $webtopics = is_array($alltext) ? ($alltext[(string)(int)$n] ?? []) : [];
    // The browser language choice is Moodle's language preference. Reuse the reviewed booklet translations for the
    // same structured web text, instead of making a live third-party request whenever a learner opens a handout.
    $lang = current_language();
    $i18nfile = '/opt/csfr-setup/i18n/booklet_i18n.json';
    $handouti18n = is_readable($i18nfile) ? json_decode(file_get_contents($i18nfile), true) : [];
    $handouttranslations = in_array($lang, ['hi', 'mr', 'gu'], true) ? ($handouti18n[$lang] ?? []) : [];
    $handoutlabels = [
        'hi' => ['Stop' => 'रुकें', 'Save' => 'सहेजें', 'Report' => 'रिपोर्ट करें', 'A real case' => 'एक वास्तविक मामला', 'Remember' => 'याद रखें', 'Readable web handout' => 'वेब हैंडआउट', 'Select, search and read this handout on any screen. The original page layout remains below.' => 'इस हैंडआउट को किसी भी स्क्रीन पर चुनें, खोजें और पढ़ें। मूल पृष्ठ लेआउट नीचे उपलब्ध है।', 'View the original handbook pages' => 'मूल हैंडबुक पृष्ठ देखें'],
        'mr' => ['Stop' => 'थांबा', 'Save' => 'जतन करा', 'Report' => 'तक्रार करा', 'A real case' => 'एक वास्तविक प्रकरण', 'Remember' => 'लक्षात ठेवा', 'Readable web handout' => 'वेब हँडआउट', 'Select, search and read this handout on any screen. The original page layout remains below.' => 'हा हँडआउट कोणत्याही स्क्रीनवर निवडा, शोधा आणि वाचा. मूळ पानांची रचना खाली उपलब्ध आहे.', 'View the original handbook pages' => 'मूळ हँडबुकची पाने पहा'],
        'gu' => ['Stop' => 'રોકો', 'Save' => 'સાચવો', 'Report' => 'ફરિયાદ કરો', 'A real case' => 'એક વાસ્તવિક કિસ્સો', 'Remember' => 'યાદ રાખો', 'Readable web handout' => 'વેબ હેન્ડઆઉટ', 'Select, search and read this handout on any screen. The original page layout remains below.' => 'આ હેન્ડઆઉટને કોઈપણ સ્ક્રીન પર પસંદ કરો, શોધો અને વાંચો. મૂળ પાનાંની રચના નીચે ઉપલબ્ધ છે.', 'View the original handbook pages' => 'મૂળ હેન્ડબુક પાનાં જુઓ'],
    ][$lang] ?? [];
    $modulesfile = '/opt/csfr-setup/modules.php';
    $modules = is_readable($modulesfile) ? require($modulesfile) : [];
    $module = $modules[(int)$n] ?? null;
    if ($module && $webtopics) {
        // The HOD booklet specifies 3 notional hours for Modules 1–8 and 2 for Module 9.
        $webmeta = ['title' => $module['title'], 'tagline' => $module['tagline'], 'hours' => (int)$n === 9 ? 2 : 3,
            'outcomes' => $module['outcomes'], 'topics' => array_column($webtopics, 'title')];
    }
}

echo $OUTPUT->header();
echo html_writer::start_div('csfr-handout' . ($webtopics ? ' csfr-handout--web' : ''));
if ($webtopics) {
    echo html_writer::tag('p', $handoutlabels['Readable web handout'] ?? 'Readable web handout', ['class' => 'csfr-handout__eyebrow']);
    echo html_writer::tag('p', $handoutlabels['Select, search and read this handout on any screen. The original page layout remains below.'] ?? 'Select, search and read this handout on any screen. The original page layout remains below.',
        ['class' => 'csfr-handout__intro']);
    if ($webmeta) {
        echo html_writer::start_tag('section', ['class' => 'csfr-handout__overview', 'aria-label' => 'Module overview']);
        echo html_writer::tag('p', 'Module ' . (int)$n . ' · ' . $webmeta['hours'] . ' notional hours',
            ['class' => 'csfr-handout__overview-kicker']);
        echo html_writer::tag('h2', s($webmeta['title']));
        echo html_writer::tag('p', s($webmeta['tagline']), ['class' => 'csfr-handout__overview-tagline']);
        echo html_writer::tag('h3', 'By the end, you can');
        echo html_writer::start_tag('ul', ['class' => 'csfr-handout__outcomes']);
        foreach ($webmeta['outcomes'] as $outcome) {
            echo html_writer::tag('li', s($outcome));
        }
        echo html_writer::end_tag('ul');
        echo html_writer::start_tag('details', ['class' => 'csfr-handout__topics']);
        echo html_writer::tag('summary', 'Topics in this handout');
        echo html_writer::start_tag('ol');
        foreach ($webmeta['topics'] as $topic) {
            echo html_writer::tag('li', s($topic));
        }
        echo html_writer::end_tag('ol') . html_writer::end_tag('details') . html_writer::end_tag('section');
    }
    echo html_writer::start_tag('article', ['class' => 'csfr-handout__paper']);
    foreach ($webtopics as $topic) {
        echo html_writer::start_tag('section', ['class' => 'csfr-handout__topic']);
        echo html_writer::tag('h2', s($handouttranslations[$topic['title']] ?? $topic['title']));
        echo csfr_handout_blocks($topic['blocks'], $handouttranslations, $handoutlabels);
        echo html_writer::end_tag('section');
    }
    echo html_writer::end_tag('article');
    echo html_writer::start_tag('details', ['class' => 'csfr-handout__original']);
    echo html_writer::tag('summary', $handoutlabels['View the original handbook pages'] ?? 'View the original handbook pages');
}
foreach ($list as $i => $p) {
    echo html_writer::empty_tag('img', [
        'src' => (new moodle_url('/local/csfr/handout.php', ['n' => $n, 'img' => $p]))->out(false),
        'alt' => get_string('handout_page', 'local_csfr', ['n' => $i + 1, 'of' => count($list)]),
        'loading' => $webtopics || $i >= 2 ? 'lazy' : 'eager', 'width' => 1075, 'height' => 1521,
    ]);
}
if ($webtopics) {
    echo html_writer::end_tag('details');
}
echo html_writer::tag('p', html_writer::link(new moodle_url('/local/csfr/handout.php', ['n' => $n, 'pdf' => 1]),
    get_string('handout_print', 'local_csfr')), ['class' => 'csfr-handout__print']);
echo html_writer::end_div();
echo $OUTPUT->footer();
