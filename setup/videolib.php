<?php
// The module films: one short explainer per teaching module (1-9), shown at the top of the module's lesson page.
// Files live in files/videos/ (mN.mp4 web copy, mN.jpg poster, mN.json voice-over timeline used for the transcript) and are
// streamed by local/csfr/video.php to signed-in users. Shared by build.php (fresh course) and videos.php (live course update).

defined('MOODLE_INTERNAL') || die();

/** Title, duration in seconds and style of each film. */
function csfr_films(): array {
    return [
        1 => ['Lock your digital doors', 90, 'Five doors a thief tries, and the habit that locks each one.'],
        2 => ['The case against online fraud', 92, 'Three scams on trial: the QR code, the power-cut SMS and the task job.'],
        3 => ['The call that wasn’t him', 84, 'A voice-clone call, the evidence, and the call-back that settles it.'],
        4 => ['The first ten minutes', 102, 'One citizen, the six questions and the four urgency colours, in real time.'],
        5 => ['Screenshot first', 96, 'What to save, and how, before anything is blocked or deleted.'],
        6 => ['Right line, right stop', 112, 'Every reporting channel as a line on a map, and the Zero FIR.'],
        7 => ['A calm voice first', 103, 'The CALM approach, and how to adapt it for each person.'],
        8 => ['Disconnect first', 92, 'A lab hit by ransomware, a phone hit by a fake app, and what to do.'],
        9 => ['Change the default', 96, 'One street of connected devices, and how to make each one safe.'],
    ];
}

/** The film block for module $n, or '' when the film is not there. Marked so videos.php can replace it. */
function csfr_film_block(int $n): string {
    global $CFG;
    $films = csfr_films();
    $dir = __DIR__ . '/files/videos';
    if (empty($films[$n]) || !is_readable("$dir/m$n.mp4")) {
        return '';
    }
    [$title, $secs, $about] = $films[$n];
    $len = sprintf('%d:%02d', intdiv($secs, 60), $secs % 60);
    $src = $CFG->wwwroot . '/local/csfr/video.php?n=' . $n;
    $html = '<!--csfr-film--><figure class="csfr-film">'
        . '<video class="csfr-film__v" controls preload="metadata" playsinline width="1920" height="1080"'
        . ' poster="' . $src . '&amp;f=poster" aria-label="' . s("Module $n film: $title, $len") . '">'
        . '<source src="' . $src . '" type="video/mp4">'
        . '<p>Your browser cannot play this video. <a href="' . $src . '">Open the film</a>.</p></video>'
        . '<figcaption class="csfr-film__cap"><span class="csfr-film__k">Module ' . $n . ' film · ' . $len . '</span>'
        . '<b class="csfr-film__t">' . s($title) . '</b><span class="csfr-film__d">' . s($about)
        . ' Narrated in English, with English and Hindi subtitles.</span></figcaption>';
    $tl = @json_decode((string)@file_get_contents("$dir/m$n.json"), true);
    if (!empty($tl['lines'])) {
        $html .= '<details class="csfr-film__tr"><summary>Read the transcript</summary><ol>';
        foreach ($tl['lines'] as $l) {
            $html .= '<li><span class="csfr-film__ts">' . sprintf('%d:%02d', intdiv((int)$l['t0'], 60), (int)$l['t0'] % 60) . '</span>'
                . '<p>' . s($l['zh']) . '</p><p lang="hi">' . s($l['en']) . '</p></li>';
        }
        $html .= '</ol></details>';
    }
    return $html . '</figure><!--/csfr-film-->';
}

/** Put the film block into lesson HTML just after the tagline (replacing an earlier block). */
function csfr_with_film(string $html, int $n): string {
    $html = preg_replace('~<!--csfr-film-->.*?<!--/csfr-film-->~s', '', $html);
    $block = csfr_film_block($n);
    if ($block === '') {
        return $html;
    }
    $at = strpos($html, '</p>', (int)strpos($html, 'hb-tagline'));
    return $at === false ? $block . $html : substr($html, 0, $at + 4) . $block . substr($html, $at + 4);
}
