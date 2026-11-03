<?php
// The lesson page of a module (1-9), drawn in the handbook's look. Shared by build.php (a fresh course) and lessons.php
// (refresh the live course). The page follows the module PDF: pages 1-3 (what it is, how it works, ask yourself,
// stop-save-report, protect, rules, remember) from modules.php, then "Practise it", pages 4-8 from modules_ext.json.
//
// Everything interactive is plain HTML and CSS (checkboxes, radio buttons, <details> and :has), so it needs no script,
// survives Moodle's content handling and works the same on a phone. Each interactive piece is one section of the page:
//   myth or fact (tap a verdict), flip cards (situation on the front, the safe response on the back), a four-step stepper,
//   lab stations as tabs, role-plays with a self-scoring observer rubric, a channel quiz, and the worksheet.

defined('CLI_SCRIPT') || defined('MOODLE_INTERNAL') || die();

function csfr_list(array $items, bool $ordered = false): string {
    $tag = $ordered ? 'ol' : 'ul';
    return "<$tag>" . implode('', array_map(fn($i) => '<li>' . s($i) . '</li>', $items)) . "</$tag>";
}

/** A Font Awesome icon (Moodle ships the library), hidden from screen readers. */
function csfr_icon(string $name): string {
    return '<i class="fa fa-' . $name . '" aria-hidden="true"></i>';
}

/** A bulleted list with a tick on each item. */
function csfr_ticks(array $items): string {
    return '<ul class="hb-ticks">' . implode('', array_map(fn($i) => '<li>' . csfr_icon('check') . '<span>' . s($i)
        . '</span></li>', $items)) . '</ul>';
}

function csfr_pill(string $text, string $tone = '', string $icon = ''): string {
    return '<h3 class="hb-pill' . ($tone ? " hb-pill--$tone" : '') . '">' . ($icon ? csfr_icon($icon) . ' ' : '') . s($text) . '</h3>';
}

/** Titled cards in a grid. $tone colours the card heads. */
function csfr_cards(array $items, string $tone = 'terra', string $extra = ''): string {
    $html = '<div class="hb-grid' . $extra . '">';
    foreach ($items as [$title, $text]) {
        $html .= '<div class="hb-card hb-card--tight hb-card--' . $tone . '"><h4 class="hb-card__h">' . s($title) . '</h4><p>' . s($text) . '</p></div>';
    }
    return $html . '</div>';
}

/** One choice question with instant feedback (radio buttons + :has). $right is the index of the right option. */
function csfr_choice(string $id, string $q, array $opts, int $right, string $why, array $classes = []): string {
    $html = '<fieldset class="hb-case"><legend>' . s($q) . '</legend><div class="hb-case__opts">';
    foreach ($opts as $k => $o) {
        $oid = "$id-$k";
        $html .= '<input type="radio" name="' . $id . '" id="' . $oid . '" class="hb-case__in' . ($k === $right ? ' is-right' : '') . '">'
            . '<label for="' . $oid . '" class="hb-case__opt ' . ($classes[$k] ?? 'hb-case__opt--plain') . '">' . s($o) . '</label>';
    }
    return $html . '</div><p class="hb-case__fb hb-case__fb--right">' . csfr_icon('circle-check') . ' Right. ' . s($why) . '</p>'
        . '<p class="hb-case__fb hb-case__fb--wrong">' . csfr_icon('circle-info') . ' Not quite: ' . s($opts[$right]) . '. ' . s($why) . '</p></fieldset>';
}

/** Module 4's triage game: four cases, each sorted Red, Amber, Yellow or Green. */
function csfr_triage_game(): string {
    $cases = [
        ['A neighbour\'s 14-year-old daughter is being threatened on Instagram: "send more photos or we post these".',
            0, 'A child and a live threat: call 112 (or 1098 Childline) now and tell your mentor.'],
        ['A shopkeeper approved a ₹18,500 "refund" request on UPI 20 minutes ago.',
            1, 'Money in the last 24 hours: call 1930 and the bank right away, inside the golden hour.'],
        ['A teacher finds a fake Facebook profile using her photo. Nobody has asked for money yet.',
            2, 'No immediate danger or loss: report the profile and file on cybercrime.gov.in today.'],
        ['Your aunt got an SMS: "KYC expired, click this link". She did not click it.',
            3, 'Nothing lost: report it on Chakshu (sancharsaathi.gov.in) and share the hygiene tips.'],
    ];
    $html = '<section class="hb-game" aria-label="Try it: sort four cases">' . csfr_pill('Try it: sort four cases', '', 'gamepad')
        . '<p class="hb-text">Pick a colour for each case. You will see straight away if it is right.</p>';
    foreach ($cases as $i => [$text, $right, $why]) {
        $html .= csfr_choice("hb-case$i", ($i + 1) . '. ' . $text, ['Red', 'Amber', 'Yellow', 'Green'], $right, $why,
            ['hb-case__opt--red', 'hb-case__opt--amber', 'hb-case__opt--yellow', 'hb-case__opt--green']);
    }
    return $html . '</section>';
}

/** The blocks between "How it works" and "Ask yourself": tables, lists, warning-sign strips and small quizzes. */
function csfr_blocks(array $blocks): string {
    $html = '';
    foreach ($blocks as $bi => $b) {
        switch ($b[0]) {
            case 'cards':
                $html .= csfr_pill($b[1]) . csfr_cards($b[2], 'navy');
                break;
            case 'signs':
                $html .= '<section class="hb-signs"><h4>' . csfr_icon('triangle-exclamation') . ' ' . s($b[1]) . '</h4><ul>'
                    . implode('', array_map(fn($x) => '<li>' . s(trim($x)) . '</li>', explode('·', $b[2]))) . '</ul></section>';
                break;
            case 'table':
                $html .= csfr_pill($b[1]) . '<div class="hb-tablewrap"><table class="hb-table"><thead><tr><th>' . s($b[2][0]) . '</th><th>'
                    . s($b[2][1]) . '</th></tr></thead><tbody>';
                foreach ($b[3] as [$a, $c]) {
                    $html .= '<tr><th scope="row">' . s($a) . '</th><td>' . s($c) . '</td></tr>';
                }
                $html .= '</tbody></table></div>';
                break;
            case 'list':
                $html .= csfr_pill($b[1]) . '<div class="hb-card hb-list' . ($b[3] ? ' hb-list--num' : '') . '">' . csfr_list($b[2], $b[3]) . '</div>';
                break;
            case 'note':
                $html .= '<aside class="hb-note"><h4>' . csfr_icon('scale-balanced') . ' ' . s($b[1]) . '</h4><p>' . s($b[2]) . '</p></aside>';
                break;
            case 'urgency':
                $html .= csfr_pill($b[1]) . '<div class="hb-urgency">';
                foreach ($b[2] as [$key, $level, $kind, $when, $action, $within]) {
                    $html .= '<div class="hb-urgency__row hb-urgency__row--' . $key . '"><div class="hb-urgency__lvl"><b>' . s($level) . '</b><span>'
                        . s($kind) . '</span></div><p class="hb-urgency__when">' . s($when) . '</p><p class="hb-urgency__act">' . s($action)
                        . '</p><p class="hb-urgency__in">' . csfr_icon('clock') . ' ' . s($within) . '</p></div>';
                }
                $html .= '</div>';
                break;
            case 'route':
                $html .= '<section class="hb-game">' . csfr_pill($b[1], '', 'route') . '<p class="hb-text">Choose where each case goes first.</p>';
                foreach ($b[2] as $i => [$q, $opts, $right]) {
                    $html .= csfr_choice("hb-route$bi-$i", ($i + 1) . '. ' . $q, $opts, $right, 'Check the reporting map above.');
                }
                $html .= '</section>';
                break;
        }
    }
    return $html;
}

/** Myth or fact: three statements, tap a verdict, see why. */
function csfr_myths(array $myths): string {
    $html = '<section class="hb-myths">' . csfr_pill('Myth or fact?', 'plum', 'circle-question') . '<p class="hb-text">Tap your verdict for each one.</p>';
    foreach ($myths as $i => [$text, $fact, $why]) {
        $html .= csfr_choice("hb-mf$i", '“' . $text . '”', ['Myth', 'Fact'], $fact ? 1 : 0, $why, ['hb-case__opt--myth', 'hb-case__opt--fact']);
    }
    return $html . '</section>';
}

/** Pages 4-8 of the module PDF: the method, the situation cards, the lab, the role-plays and the worksheet. */
function csfr_practice_part(int $n, array $x): string {
    $html = '<div class="hb-part"><span>' . csfr_icon('person-chalkboard') . ' Practise it</span></div>';

    // The method: four steps as a stepper (tap a number), then a "stop and check" prompt.
    $s = $x['steps'];
    $html .= '<section class="hb-stepper">' . csfr_pill($s['title'], 'teal', 'shoe-prints') . '<p class="hb-text">' . s($s['intro']) . '</p>';
    foreach ($s['steps'] as $i => $st) {
        $html .= '<input type="radio" name="hb-st" id="hb-st' . $i . '" class="hb-stepper__in"' . ($i === 0 ? ' checked' : '') . '>';
    }
    $html .= '<div class="hb-stepper__tabs" role="presentation">';
    foreach ($s['steps'] as $i => $st) {
        $html .= '<label for="hb-st' . $i . '"><b>' . ($i + 1) . '</b><span>' . s($st['h']) . '</span></label>';
    }
    $html .= '</div><div class="hb-stepper__panels">';
    foreach ($s['steps'] as $i => $st) {
        $html .= '<div class="hb-stepper__panel"><h4>Step ' . ($i + 1) . ' · ' . s($st['h']) . '</h4><p>' . s($st['p']) . '</p></div>';
    }
    $html .= '</div>';
    if ($s['check']) {
        $html .= '<div class="hb-think"><h4>' . csfr_icon('lightbulb') . ' Stop and check</h4><p>' . s($s['check']) . '</p>'
            . '<label class="hb-think__done"><input type="checkbox"> <span>I have thought it through</span></label></div>';
    }
    $html .= '</section>';

    // Situation cards: tap to flip from the situation to the safe response.
    $c = $x['cards'];
    $html .= '<section class="hb-flips-wrap">' . csfr_pill($c['title'], 'terra', 'repeat') . '<p class="hb-text">' . s($c['intro'])
        . ' <b>Tap a card</b> to turn it over.</p><div class="hb-flips">';
    foreach ($c['cards'] as $i => $cd) {
        $html .= '<label class="hb-flip"><input type="checkbox" class="hb-flip__in"><span class="hb-flip__card">'
            . '<span class="hb-flip__face hb-flip__front"><b>' . s($cd['h']) . '</b>' . ($cd['k1'] ? '<i>' . s($cd['k1']) . '</i>' : '')
            . '<span>' . s($cd['v1']) . '</span><em>' . csfr_icon('rotate') . ' ' . s($cd['k2'] ?: 'Turn over') . '</em></span>'
            . '<span class="hb-flip__face hb-flip__back"><b>' . s($cd['h']) . '</b><i>' . s($cd['k2']) . '</i><span>' . s($cd['v2']) . '</span></span>'
            . '</span></label>';
    }
    $html .= '</div>';
    if ($c['think']) {
        $html .= '<aside class="hb-note hb-note--teal"><h4>' . csfr_icon('brain') . ' ' . s($c['thinkk'] ?: 'How to think about it') . '</h4><p>'
            . s($c['think']) . '</p></aside>';
    }
    $html .= '</section>';

    // The guided lab: stations as tabs.
    $l = $x['lab'];
    $html .= '<section class="hb-tabs">' . csfr_pill($l['title'], 'navy', 'flask') . '<p class="hb-text">' . s($l['intro']) . '</p>';
    foreach ($l['stations'] as $i => $st) {
        $html .= '<input type="radio" name="hb-lab" id="hb-lab' . $i . '" class="hb-tabs__in"' . ($i === 0 ? ' checked' : '') . '>';
    }
    $html .= '<div class="hb-tabs__bar">';
    foreach ($l['stations'] as $i => $st) {
        $html .= '<label for="hb-lab' . $i . '">' . s($st['k']) . '</label>';
    }
    $html .= '</div><div class="hb-tabs__panels">';
    foreach ($l['stations'] as $st) {
        $html .= '<div class="hb-tabs__panel"><h4>' . s($st['h']) . '</h4><p>' . s($st['p']) . '</p></div>';
    }
    $html .= '</div><p class="hb-expect">' . csfr_icon('bullseye') . ' <b>Success looks like this:</b> ' . s($l['expect']) . '</p></section>';

    // Role-plays: three cases, then the observer rubric that adds itself up.
    $k = $x['cases'];
    $html .= '<section class="hb-roleplay">' . csfr_pill($k['title'], 'plum', 'people-group') . '<p class="hb-text">' . s($k['intro']) . '</p><div class="hb-grid">';
    foreach ($k['cases'] as $cs) {
        $html .= '<div class="hb-card hb-card--tight hb-card--case"><span class="hb-case__tag">' . s($cs['k']) . '</span><h4 class="hb-card__h">'
            . s($cs['h']) . '</h4><p>' . s($cs['p']) . '</p></div>';
    }
    $html .= '</div>' . str_replace('<details class="hb-rubric" open>', '<details class="hb-rubric">', csfr_rubric('hb-rb', $k['rubric'], 'Observer: score this role-play'));
    if ($k['discuss']) {
        $html .= '<p class="hb-discuss">' . csfr_icon('comments') . ' <b>Discuss:</b> ' . s($k['discuss']) . '</p>';
    }
    $html .= '</section>';

    // The worksheet: tick the questions you can answer, the script to say, the self-check.
    $w = $x['sheet'];
    $html .= '<section class="hb-sheet">' . csfr_pill($w['title'], 'mustard', 'clipboard-list') . '<p class="hb-text">' . s($w['intro'])
        . '</p><ul class="hb-ask">';
    foreach ($w['questions'] as $i => $q) {
        $id = "hb-ws$i";
        $html .= '<li><input type="checkbox" id="' . $id . '"><label for="' . $id . '"><span class="hb-ask__mark">' . csfr_icon('pen')
            . csfr_icon('check') . '</span>' . s($q) . '</label></li>';
    }
    $html .= '</ul>';
    if ($w['script']) {
        $html .= '<blockquote class="hb-script"><h4>' . csfr_icon('comment-dots') . ' ' . s($w['scripth']) . '</h4><p>' . s($w['script']) . '</p></blockquote>';
    }
    if ($w['selfcheck']) {
        $html .= '<p class="hb-selfcheck">' . csfr_icon('circle-check') . ' <b>Self-check:</b> ' . s($w['selfcheck']) . '</p>';
    }
    return $html . '</section>';
}

/**
 * A crossword for module $n: the words go to the page as data and a small script lays them out (crossing letters,
 * numbered clues), draws one input per cell and checks answers. Without script the clues still show as a list.
 */
function csfr_crossword(int $n, array $words): string {
    $data = htmlspecialchars(json_encode(array_map(fn($w) => ['a' => strtoupper($w[0]), 'c' => $w[1]], $words)), ENT_QUOTES);
    $html = '<section class="hb-xword" data-words="' . $data . '">' . csfr_pill('Crossword', 'navy', 'table-cells')
        . '<p class="hb-text">Tap a square and type. Tap the same square again to switch between across and down.</p>'
        . '<div class="hb-xword__body"><div class="hb-xword__grid" role="grid" aria-label="Crossword grid"></div><div class="hb-xword__clues"><ol>';
    foreach ($words as [$a, $c]) {
        $html .= '<li>' . s($c) . ' (' . strlen($a) . ')</li>';
    }
    $html .= '</ol></div></div><div class="hb-xword__bar"><button type="button" class="btn btn-primary" data-x="check">Check</button>'
        . '<button type="button" class="btn btn-outline-secondary" data-x="reveal">Show answers</button><span class="hb-xword__msg" aria-live="polite"></span></div></section>';
    return $html . <<<'JS'
<script>
(function () {
  var box = document.currentScript.previousElementSibling;
  if (!box || !box.classList.contains('hb-xword')) { return; }
  var all = JSON.parse(box.getAttribute('data-words'));
  var key = function (x, y) { return x + ',' + y; };
  var cells, placed, loose;
  function build(words) {
    cells = {}; placed = []; loose = 0;
    function fits(w, x, y, dir) {
      var dx = dir ? 0 : 1, dy = dir ? 1 : 0, cross = 0;
      if (cells[key(x - dx, y - dy)] || cells[key(x + dx * w.length, y + dy * w.length)]) { return -1; }
      for (var i = 0; i < w.length; i++) {
        var cx = x + dx * i, cy = y + dy * i, c = cells[key(cx, cy)];
        if (c) { if (c.ch !== w[i] || c.dirs.indexOf(dir) >= 0) { return -1; } cross++; continue; }
        if (cells[key(cx + dy, cy + dx)] || cells[key(cx - dy, cy - dx)]) { return -1; }
      }
      return cross;
    }
    function put(wd, x, y, dir) {
      for (var i = 0; i < wd.a.length; i++) {
        var k = key(x + (dir ? 0 : i), y + (dir ? i : 0));
        cells[k] = cells[k] || { ch: wd.a[i], dirs: [] }; cells[k].dirs.push(dir);
      }
      placed.push({ w: wd, x: x, y: y, dir: dir });
    }
    put(words[0], 0, 0, 0);
    var pending = words.slice(1), progress = true;
    while (pending.length && progress) {
      progress = false;
      pending = pending.filter(function (wd) {
        var best = null;
        Object.keys(cells).forEach(function (k) {
          var p = k.split(',').map(Number), c = cells[k];
          for (var i = 0; i < wd.a.length; i++) {
            if (wd.a[i] !== c.ch) { continue; }
            [0, 1].forEach(function (dir) {
              var x = p[0] - (dir ? 0 : i), y = p[1] - (dir ? i : 0), sc = fits(wd.a, x, y, dir);
              if (sc > 0 && (!best || sc > best.s)) { best = { x: x, y: y, dir: dir, s: sc }; }
            });
          }
        });
        if (best) { put(wd, best.x, best.y, best.dir); progress = true; return false; }
        return true;
      });
    }
    pending.forEach(function (wd) {
      var ks = Object.keys(cells).map(function (k) { return k.split(',').map(Number); });
      put(wd, Math.min.apply(null, ks.map(function (p) { return p[0]; })), Math.max.apply(null, ks.map(function (p) { return p[1]; })) + 2, 0);
      loose++;
    });
    var xs = Object.keys(cells).map(function (k) { return +k.split(',')[0]; }), ys = Object.keys(cells).map(function (k) { return +k.split(',')[1]; });
    var w = Math.max.apply(null, xs) - Math.min.apply(null, xs) + 1, h = Math.max.apply(null, ys) - Math.min.apply(null, ys) + 1;
    return loose * 1000 + Math.max(w, h) * 10 + w * h / 10 + (w > 16 ? 500 : 0);
  }
  // Try many orders (seeded, so the layout is the same on every visit) and keep the most interlocked, compact one.
  var seed = all.length * 7919, rnd = function () { seed = (seed * 16807) % 2147483647; return seed / 2147483647; };
  var bestOrder = null, bestScore = Infinity;
  for (var t = 0; t < 200; t++) {
    var order = all.slice().sort(function (a, b) { return b.a.length - a.a.length; });
    if (t) { for (var i = order.length - 1; i > 0; i--) { var j = Math.floor(rnd() * (i + 1)), tmp = order[i]; order[i] = order[j]; order[j] = tmp; } }
    var sc = build(order);
    if (sc < bestScore) { bestScore = sc; bestOrder = order; }
  }
  build(bestOrder);
  box.dataset.loose = loose;
  var xs = Object.keys(cells).map(function (k) { return +k.split(',')[0]; }), ys = Object.keys(cells).map(function (k) { return +k.split(',')[1]; });
  var x0 = Math.min.apply(null, xs), y0 = Math.min.apply(null, ys), W = Math.max.apply(null, xs) - x0 + 1, H = Math.max.apply(null, ys) - y0 + 1;
  placed.sort(function (a, b) { return (a.y - b.y) || (a.x - b.x); });
  var num = {}, n = 0, clues = { a: [], d: [] };
  placed.forEach(function (p) { var k = key(p.x, p.y); if (!num[k]) { num[k] = ++n; } p.n = num[k]; (p.dir ? clues.d : clues.a).push(p); });
  var grid = box.querySelector('.hb-xword__grid'), inputs = {};
  grid.style.gridTemplateColumns = 'repeat(' + W + ', var(--xc))';
  for (var y = y0; y < y0 + H; y++) { for (var x = x0; x < x0 + W; x++) {
    var k = key(x, y), d = document.createElement('div');
    d.className = 'hb-xword__cell' + (cells[k] ? '' : ' is-empty');
    if (cells[k]) {
      if (num[k]) { var s = document.createElement('span'); s.textContent = num[k]; d.appendChild(s); }
      var inp = document.createElement('input'); inp.maxLength = 1; inp.setAttribute('aria-label', 'Square ' + (num[k] || '')); inp.dataset.k = k;
      inp.autocomplete = 'off'; inp.setAttribute('autocapitalize', 'characters'); d.appendChild(inp); inputs[k] = inp;
    }
    grid.appendChild(d);
  } }
  var list = box.querySelector('.hb-xword__clues'), html = '';
  [['Across', clues.a], ['Down', clues.d]].forEach(function (g) {
    if (!g[1].length) { return; }
    html += '<h4>' + g[0] + '</h4><ol>' + g[1].map(function (p) { return '<li value="' + p.n + '">' + p.w.c.replace(/</g, '&lt;') + ' (' + p.w.a.length + ')</li>'; }).join('') + '</ol>';
  });
  list.innerHTML = html;
  var dir = 0;
  function move(k, step) { var p = k.split(',').map(Number), nk = key(p[0] + (dir ? 0 : step), p[1] + (dir ? step : 0)); if (inputs[nk]) { inputs[nk].focus(); } }
  Object.keys(inputs).forEach(function (k) {
    var inp = inputs[k];
    inp.addEventListener('mousedown', function () { if (document.activeElement === inp) { dir = 1 - dir; } });
    inp.addEventListener('input', function () { inp.value = inp.value.replace(/[^a-z]/gi, '').toUpperCase(); inp.parentNode.classList.remove('is-right', 'is-wrong'); if (inp.value) { move(k, 1); } });
    inp.addEventListener('keydown', function (e) {
      var m = { ArrowRight: [0, 1], ArrowLeft: [0, -1], ArrowDown: [1, 1], ArrowUp: [1, -1] }[e.key];
      if (m) { dir = m[0]; move(k, m[1]); e.preventDefault(); }
      if (e.key === 'Backspace' && !inp.value) { move(k, -1); }
    });
  });
  var msg = box.querySelector('.hb-xword__msg');
  box.querySelector('[data-x="check"]').addEventListener('click', function () {
    var right = 0, all = 0;
    Object.keys(inputs).forEach(function (k) { var ok = inputs[k].value === cells[k].ch; all++; if (ok) { right++; }
      inputs[k].parentNode.classList.toggle('is-right', ok && !!inputs[k].value); inputs[k].parentNode.classList.toggle('is-wrong', !ok && !!inputs[k].value); });
    msg.textContent = right === all ? 'All correct. Well done!' : right + ' of ' + all + ' squares right.';
  });
  box.querySelector('[data-x="reveal"]').addEventListener('click', function () {
    Object.keys(inputs).forEach(function (k) { inputs[k].value = cells[k].ch; inputs[k].parentNode.classList.remove('is-wrong'); });
    msg.textContent = 'Answers shown.';
  });
})();
</script>
JS;
}

/**
 * The booklet's topics for module $n as tabs: each tab shows that topic's pages of the HOD's booklet, as drawn there
 * (figures, tables, real cases, trainer notes), loaded only when opened.
 */
function csfr_topics(int $n): string {
    global $CFG;
    static $book = null;
    $book ??= is_readable(__DIR__ . '/booklet.php') && is_readable(__DIR__ . '/files/booklet.pdf') ? require(__DIR__ . '/booklet.php') : [];
    $topics = $book['topics'][$n] ?? [];
    if (!$topics) {
        return '';
    }
    $html = '<section class="hb-topics">' . csfr_pill('Learn it: ' . count($topics) . ' topics from the booklet', 'navy', 'book-open')
        . '<p class="hb-text">Each topic is a short read from the programme booklet. Pick a topic; it opens below.</p>';
    foreach ($topics as $i => $t) {
        $html .= '<input type="radio" name="hb-tp" id="hb-tp' . $i . '" class="hb-tabs__in"' . ($i === 0 ? ' checked' : '') . '>';
    }
    $html .= '<div class="hb-topics__bar">';
    foreach ($topics as $i => [$title]) {
        $html .= '<label for="hb-tp' . $i . '"><b>' . ($i + 1) . '</b><span>' . s($title) . '</span></label>';
    }
    static $text = null;
    $text ??= is_readable(__DIR__ . '/booklet_text.json') ? json_decode(file_get_contents(__DIR__ . '/booklet_text.json'), true) : [];
    $html .= '</div><div class="hb-topics__panels">';
    foreach ($topics as $i => [$title, $from, $to]) {
        $html .= '<div class="hb-topics__panel"><h4>Topic ' . ($i + 1) . ' · ' . s($title) . '</h4>';
        $blocks = $text[$n][$i]['blocks'] ?? null;
        if ($blocks) {
            // sharp, selectable web text, with the original booklet page one tap away as a backstop
            $html .= '<div class="hb-bk">' . csfr_booklet_blocks($blocks) . '</div>'
                . '<details class="hb-bk__orig"><summary>Show the original booklet page</summary><div class="hb-bk__pages">';
        } else {
            $html .= '<div class="hb-bk__pages">';
        }
        foreach (range($from, $to) as $k => $p) {
            $src = $CFG->wwwroot . '/local/csfr/handout.php?n=' . $n . '&amp;img=' . $p;
            $html .= '<img src="' . $src . '" alt="' . s("Topic " . ($i + 1) . ": $title, page " . ($k + 1)) . '" loading="lazy" width="1075" height="1521">';
        }
        $html .= $blocks ? '</div></details>' : '</div>';
        $html .= '</div>';
    }
    return $html . '</div></section>';
}

/** The observer rubric as a self-scoring block (radio buttons, a CSS counter adds the total). */
function csfr_rubric(string $prefix, array $criteria, string $summary): string {
    $html = '<details class="hb-rubric" open><summary>' . csfr_icon('clipboard-check') . ' ' . s($summary) . '</summary>'
        . '<p class="hb-text">0 = not shown · 1 = partly · 2 = clearly shown. The total adds itself up; nothing is saved.</p><div class="hb-rubric__rows">';
    foreach ($criteria as $ri => $crit) {
        $html .= '<div class="hb-rubric__row"><span>' . s($crit) . '</span><div class="hb-rubric__opts">';
        foreach ([0, 1, 2] as $v) {
            $id = "$prefix$ri-$v";
            $html .= '<input type="radio" name="' . $prefix . $ri . '" id="' . $id . '" value="' . $v . '" class="hb-rubric__in hb-rubric__in--' . $v . '">'
                . '<label for="' . $id . '">' . $v . '</label>';
        }
        $html .= '</div></div>';
    }
    return $html . '</div><p class="hb-rubric__total" data-max="' . (2 * count($criteria)) . '">Total</p></details>';
}

/** Module 10's capstone page (the module PDF, two pages): the case, the injects one at a time, the model response, the rubric. */
function csfr_capstone(): string {
    $html = '<div class="csfr-hb csfr-hb--m10"><p class="hb-tagline">One case, every skill: from the first call to the follow-up.</p>'
        . '<ul class="hb-chips"><li><b>Module 10</b><span>of 11</span></li><li><b>1.0 h</b><span>Practical in class</span></li>'
        . '<li><b>Teams of 4</b><span>Responder · Evidence lead · Reporting lead · Observer</span></li><li><b>Trainer</b><span>plays the victim</span></li></ul>'
        . '<ol class="hb-flow"><li>Stabilise</li><li>Secure</li><li>Save</li><li>Report</li><li>Support</li></ol>'
        . '<ol class="hb-timeline"><li><b>0–5 min</b><span>Briefing</span></li><li><b>5–35 min</b><span>Live simulation</span></li>'
        . '<li><b>35–45 min</b><span>Forms and next steps</span></li><li><b>45–60 min</b><span>Debrief</span></li></ol>'
        . csfr_pill('Scenario A · "The investment group"', 'terra', 'user')
        . '<blockquote class="hb-script hb-script--case"><h4>Mr. Prakash Kulkarni, 58, retired bank clerk, Kalyan</h4><p>"My son\'s friend added me to a '
        . 'WhatsApp group where a famous market expert gives daily tips. I installed their app and invested ₹3.5 lakh over three weeks. The app shows '
        . '₹11 lakh profit. Today they said I must pay ₹60,000 tax before withdrawal. I paid ₹25,000 half an hour ago. Now my WhatsApp is also asking '
        . 'for a code."</p></blockquote>'
        . csfr_pill('Injects: open each one when the trainer calls it', 'red', 'bell') . '<div class="hb-injects">';
    $injects = [['Minute 8', 'The group admin is calling me right now. He says if I don\'t pay the rest in one hour, my account will be frozen.'],
        ['Minute 14', 'Someone called saying he is from the Cyber Cell and can recover all my money for ₹15,000.'],
        ['Minute 20', 'My wife says my sister just got a WhatsApp from my number asking for ₹10,000.'],
        ['Minute 26', 'I feel so ashamed. My son will be angry. Maybe I should just keep quiet.']];
    foreach ($injects as [$t, $q]) {
        $html .= '<details class="hb-inject"><summary><b>' . s($t) . '</b> ' . csfr_icon('bell') . ' New development</summary><p>"' . s($q) . '"</p></details>';
    }
    $html .= '</div>';
    $model = [['Calm and connect', 'Reassure, no blame, private space: "fraudsters are professionals".'],
        ['Triage', 'Financial fraud + account compromise · AMBER: ₹25,000 in the last hour, live pressure, takeover in progress.'],
        ['Stop', 'No more payments; don\'t take the admin\'s call; don\'t share the WhatsApp code; note the app name/link, then uninstall.'],
        ['Secure', 'Re-register WhatsApp + two-step PIN; change email and banking passwords; warn the sister and contacts.'],
        ['Save evidence', 'UTRs for all 3 weeks, app name/link, group name, admin numbers, chat export, "profit" and "tax" screenshots.'],
        ['Report', '1930 now for all payments → bank dispute → cybercrime.gov.in → numbers on Chakshu.'],
        ['Recovery inject', 'A second fraud: police never charge to recover money. Report that number too.'],
        ['Shame inject', 'Address shame; involve the son with consent; offer Tele-MANAS 14416 if distress persists.'],
        ['Close', 'Acknowledgement no., evidence list, follow-up date, "no further payments to anyone".']];
    $html .= '<details class="hb-model"><summary>' . csfr_icon('key') . ' Model response · open after the debrief</summary><dl>';
    foreach ($model as [$k, $v]) {
        $html .= '<dt>' . s($k) . '</dt><dd>' . s($v) . '</dd>';
    }
    $html .= '</dl></details>' . csfr_pill('Alternate scenarios', 'navy') . csfr_cards([
        ['B · "The video call"', 'Ayesha, 20: recorded video call, morphed images, ₹15,000 demanded by tonight. CALM with a female responder · don\'t pay · don\'t ask to see images · save the profile URL and chat · portal (Women category) · platform · StopNCII · Tele-MANAS / 112 if needed.'],
        ['C · "The clinic"', 'A Panvel clinic PC shows a ransom note after a "GST Notice" email. AMBER · isolate all systems · don\'t pay · photograph the note · find the backup · CERT-In + police · check whether patient data was taken (DPDP breach duty).'],
    ], 'navy')
        . csfr_rubric('hb-cap', ['Calm, empathetic, no blame', 'Correct category and urgency', 'Stopped further loss; secured accounts',
            'Evidence preserved (UTRs, URLs, numbers)', 'Correct channels (1930, portal, bank, others)', 'Spotted the recovery scam',
            'Consent and privacy respected', 'Clear written next steps'], 'Observer rubric · score 0 / 1 / 2')
        . '<p class="hb-help">' . csfr_icon('phone') . ' Scammed? Call <strong>1930</strong> or file a complaint at <strong>cybercrime.gov.in</strong>.</p></div>';
    return $html;
}

/**
 * A module's online lesson, drawn as the handbook's pages: the same sections, colours and order the class uses, with
 * things to tick, reveal and try. $practice is a few of the module-check questions (never the pre/post test bank).
 */
function csfr_lesson(int $n, array $m, array $practice = []): string {
    static $ext = null;
    $ext ??= (array)json_decode((string)@file_get_contents(__DIR__ . '/modules_ext.json'), true);
    $html = '<div class="csfr-hb csfr-hb--m' . $n . '">';
    $html .= '<p class="hb-tagline">' . s($m['tagline']) . '</p>';
    $html .= '<ul class="hb-chips"><li><b>Module ' . $n . '</b><span>of 11</span></li>'
        . '<li><b>' . s($m['theory']) . ' h</b><span>Theory in class</span></li>'
        . '<li><b>' . s($m['practical']) . ' h</b><span>Practical in class</span></li>'
        . '<li><b>About 90 min</b><span>Online, at home</span></li></ul>';
    $html .= '<section class="hb-card hb-card--outcomes"><h3 class="hb-card__h">By the end of this module you can</h3>'
        . csfr_ticks($m['outcomes']) . '</section>';

    $html .= csfr_topics($n);
    $html .= csfr_pill('What is it?') . '<p class="hb-text hb-text--lead">' . s($m['what']) . '</p>';
    if (!empty($m['meet'])) {
        $html .= csfr_pill($m['meet'][0], 'navy') . csfr_cards($m['meet'][1], 'navy', count($m['meet'][1]) === 3 ? ' hb-grid--3' : '');
    }
    if (!empty($m['how'])) {
        $html .= csfr_pill($m['howh'] ?? 'How it works', 'terra') . csfr_cards($m['how'], 'terra');
    }
    if (!empty($m['story'])) {
        $html .= '<aside class="hb-note hb-note--teal"><h4>' . csfr_icon('star') . ' A real case</h4><p>' . s($m['story']) . '</p></aside>';
    }
    $html .= csfr_blocks($m['blocks'] ?? []);
    if (!empty($m['game'])) {
        $html .= csfr_triage_game();
    }
    if (!empty($m['myths'])) {
        $html .= csfr_myths($m['myths']);
    }

    $html .= csfr_pill('Ask yourself', 'mustard') . '<p class="hb-text">Tick each one once you have thought it through.</p><ul class="hb-ask">';
    foreach (array_values($m['ask']) as $i => $q) {
        $id = "hb-ask$i";
        $html .= '<li><input type="checkbox" id="' . $id . '"><label for="' . $id . '"><span class="hb-ask__mark">'
            . csfr_icon('question') . csfr_icon('check') . '</span>' . s($q) . '</label></li>';
    }
    $html .= '</ul>';
    if (!empty($m['callout'])) {
        $html .= '<aside class="hb-callout">' . csfr_icon('circle-exclamation') . '<p>' . s($m['callout']) . '</p></aside>';
    }

    $html .= csfr_pill($m['stepsh'] ?? 'If it has happened', 'red', 'life-ring') . '<ol class="hb-steps">';
    foreach ([$m['stopl'] ?? 'Stop' => $m['stop'], $m['savel'] ?? 'Save evidence' => $m['save'], $m['reportl'] ?? 'Report' => $m['report']] as $step => $items) {
        $html .= '<li><h4>' . s($step) . '</h4>' . csfr_list($items) . '</li>';
    }
    $html .= '</ol>';

    $html .= csfr_pill('Protect yourself before it happens', 'teal', 'shield-halved') . '<div class="hb-grid">';
    foreach ($m['protect'] as $item) {
        [$t, $p] = is_array($item) ? $item : ['', $item];
        $html .= '<div class="hb-card hb-card--tight hb-card--safe">' . csfr_icon('shield-halved') . '<div>' . ($t ? '<h4 class="hb-card__h">' . s($t) . '</h4>' : '')
            . '<p>' . s($p) . '</p></div></div>';
    }
    $html .= '</div>';
    if (!empty($m['rules'])) {
        $html .= '<section class="hb-card hb-card--rules"><h3 class="hb-card__h">Follow these rules</h3>' . csfr_ticks($m['rules']) . '</section>';
    }
    $html .= csfr_pill('Remember', 'teal') . '<ul class="hb-rules">' . implode('', array_map(fn($r) => '<li>' . s($r) . '</li>', $m['remember'])) . '</ul>';

    if (!empty($ext[(string)$n])) {
        $html .= csfr_practice_part($n, $ext[(string)$n]);
    }
    static $xwords = null;
    $xwords ??= require(__DIR__ . '/crosswords.php');
    if (!empty($xwords[$n])) {
        $html .= csfr_crossword($n, $xwords[$n]);
    }

    if ($practice) {
        $html .= csfr_pill('Check yourself', 'mustard') . '<p class="hb-text">Think of your answer, then tap to see it. Nothing is recorded.</p><div class="hb-reveals">';
        foreach ($practice as $q) {
            $html .= '<details class="hb-reveal"><summary><span>' . s($q['q']) . '</span>' . csfr_icon('chevron-down') . '</summary><ul class="hb-reveal__opts">';
            foreach ($q['o'] as $j => $o) {
                $html .= '<li' . ($j === $q['a'] ? ' class="is-right"' : '') . '>' . ($j === $q['a'] ? csfr_icon('circle-check') : '') . s($o) . '</li>';
            }
            $html .= '</ul></details>';
        }
        $html .= '</div>';
    }
    $html .= '<p class="hb-help">' . csfr_icon('phone') . ' Scammed? Call <strong>1930</strong> or file a complaint at '
        . '<strong>cybercrime.gov.in</strong>. Danger to life: <strong>112</strong>.</p></div>';
    return $html;
}

/**
 * One copy of a lesson per language, each wrapped in <div lang="xx" class="csfr-l10n">; the theme shows the copy that
 * matches the learner's interface language (English otherwise). Lesson framing comes from i18n/ui.json; text nodes
 * that are not in it stay in English until the content itself is translated. ids get a language suffix so the
 * CSS-only controls in each copy stay independent.
 */
function csfr_l10n(string $html): string {
    $ui = json_decode(file_get_contents(__DIR__ . '/i18n/ui.json'), true);
    unset($ui['_note']);
    // teammate-translated text (English -> translated), keyed by language, from the returned worksheets:
    // booklet_i18n = the booklet topics; module_i18n = the module-authored lesson text (taglines, cards, stories...)
    $book = is_readable(__DIR__ . '/i18n/booklet_i18n.json')
        ? json_decode(file_get_contents(__DIR__ . '/i18n/booklet_i18n.json'), true) : [];
    $modi = is_readable(__DIR__ . '/i18n/module_i18n.json')
        ? json_decode(file_get_contents(__DIR__ . '/i18n/module_i18n.json'), true) : [];
    foreach (['hi', 'mr', 'gu'] as $lg) {
        $book[$lg] = array_merge($book[$lg] ?? [], $modi[$lg] ?? []);
    }
    $out = '<div lang="en" class="csfr-l10n">' . $html . '</div>';
    foreach (['hi', 'mr', 'gu'] as $k => $lang) {
        $map = [];
        foreach ($ui as $en => $tr) {
            $map[htmlspecialchars($en, ENT_QUOTES)] = $tr[$k];
            $map[$en] = $tr[$k];
        }
        foreach ($book[$lang] ?? [] as $en => $tr) {                 // booklet sentences, lists, tables, boxes
            $map[htmlspecialchars($en, ENT_QUOTES)] = htmlspecialchars($tr, ENT_QUOTES);
            $map[$en] = htmlspecialchars($tr, ENT_QUOTES);
        }
        $copy = preg_replace_callback('/>([^<>]+)</u', function ($m) use ($map) {
            $t = trim($m[1]);
            return isset($map[$t]) ? '>' . str_replace($t, $map[$t], $m[1]) . '<' : $m[0];
        }, $html);
        $copy = preg_replace('/\b(id|for|name)="(hb-[^"]+)"/', '$1="$2-' . $lang . '"', $copy);
        $out .= '<div lang="' . $lang . '" class="csfr-l10n">' . $copy . '</div>';
    }
    return $out;
}

/**
 * Render one booklet topic's parsed blocks (from booklet_text.json) as sharp, selectable web text.
 * Reuses the lesson design: hb-steps for Stop/Save/Report, hb-note for case and remember boxes, tables for the rest.
 */
function csfr_booklet_blocks(array $blocks): string {
    $inner = function (array $bs) use (&$inner): string {
        $h = '';
        foreach ($bs as $b) {
            switch ($b['type']) {
                case 'h': $h .= '<h5 class="hb-bk__h">' . s($b['text']) . '</h5>'; break;
                case 'p': $h .= '<p class="hb-text">' . s($b['text']) . '</p>'; break;
                case 'ul': $h .= csfr_list($b['items']); break;
                case 'kv':
                    $h .= '<table class="hb-bk__tbl"><tbody>';
                    foreach ($b['rows'] as [$k, $v]) { $h .= '<tr><th scope="row">' . s($k) . '</th><td>' . s($v) . '</td></tr>'; }
                    $h .= '</tbody></table>';
                    break;
                case 'cols':
                    $h .= '<table class="hb-bk__tbl"><thead><tr>';
                    foreach ($b['head'] as $c) { $h .= '<th>' . s($c) . '</th>'; }
                    $h .= '</tr></thead><tbody>';
                    foreach ($b['rows'] as $r) { $h .= '<tr>'; foreach ($r as $c) { $h .= '<td>' . s($c) . '</td>'; } $h .= '</tr>'; }
                    $h .= '</tbody></table>';
                    break;
                case 'ssr':
                    $h .= '<ol class="hb-steps hb-steps--bk">';
                    foreach (['Stop' => $b['stop'], 'Save' => $b['save'], 'Report' => $b['report']] as $k => $v) {
                        if (trim($v) !== '') { $h .= '<li><h4>' . $k . '</h4><p>' . s($v) . '</p></li>'; }
                    }
                    $h .= '</ol>';
                    break;
                case 'case':
                    $h .= '<aside class="hb-note hb-note--teal"><h4>' . csfr_icon('star') . ' A real case</h4>' . $inner($b['blocks']) . '</aside>';
                    break;
                case 'remember':
                    $h .= '<aside class="hb-note"><h4>' . csfr_icon('lightbulb') . ' Remember</h4>' . $inner($b['blocks']) . '</aside>';
                    break;
            }
        }
        return $h;
    };
    return $inner($blocks);
}

/**
 * Cisco-style forced engagement for a lesson page. Renders a fixed bottom bar that tracks the learner's progress
 * through the lesson's interactive pieces (every flip card flipped, every tab/stepper/lab panel opened, every
 * self-check ticked, a choice made in every myth/game group) plus scrolling to the end. Only when all of that is
 * done does the "Finish lesson" button enable; it marks the activity complete via the completion web service, which
 * unlocks the module's assessment. Scoped to the visible language variant; a finished lesson is remembered per device.
 */
function csfr_gate_script(): string {
    return <<<'HTML'
<script>
(function () {
  var hb = document.querySelector('.csfr-hb');
  var cmid = new URLSearchParams(location.search).get('id');
  if (!hb || !cmid) { return; }
  var DONE = 'csfrLessonDone-' + cmid, pre = false;
  try { pre = localStorage.getItem(DONE) === '1'; } catch (e) {}
  if (pre) { return; }                              // already finished on this device: no gate

  // gate-bar wording follows the interface language (Moodle sets <html lang>); English otherwise. Drafts.
  var L = (document.documentElement.lang || 'en').slice(0, 2);
  var T = {
    en: { finish: 'Finish lesson & continue', toFinish: 'Finish the lesson to continue', left: function (n) { return n + ' interaction' + (n > 1 ? 's' : '') + ' left'; }, read: 'read to the end', allDone: '<b>All done.</b> Mark the lesson complete to unlock the assessment.', saving: 'Saving…', complete: '<b>Lesson complete.</b> The assessment is unlocked.', done: 'Done ✓', fail: 'Could not save — please try again.' },
    hi: { finish: 'पाठ पूरा करें और आगे बढ़ें', toFinish: 'जारी रखने के लिए पाठ पूरा करें', left: function (n) { return n + ' काम बाकी'; }, read: 'अंत तक पढ़ें', allDone: '<b>सब हो गया।</b> मूल्यांकन खोलने के लिए पाठ को पूरा चिह्नित करें।', saving: 'सहेजा जा रहा है…', complete: '<b>पाठ पूरा हुआ।</b> मूल्यांकन खुल गया है।', done: 'हो गया ✓', fail: 'सहेज नहीं सका — कृपया फिर कोशिश करें।' },
    mr: { finish: 'धडा पूर्ण करा आणि पुढे जा', toFinish: 'पुढे जाण्यासाठी धडा पूर्ण करा', left: function (n) { return n + ' बाकी'; }, read: 'शेवटपर्यंत वाचा', allDone: '<b>सर्व झाले.</b> मूल्यांकन उघडण्यासाठी धडा पूर्ण म्हणून चिन्हांकित करा.', saving: 'जतन करत आहे…', complete: '<b>धडा पूर्ण झाला.</b> मूल्यांकन उघडले आहे.', done: 'झाले ✓', fail: 'जतन करता आले नाही — कृपया पुन्हा प्रयत्न करा.' },
    gu: { finish: 'પાઠ પૂર્ણ કરો અને આગળ વધો', toFinish: 'આગળ વધવા માટે પાઠ પૂર્ણ કરો', left: function (n) { return n + ' બાકી'; }, read: 'અંત સુધી વાંચો', allDone: '<b>બધું થઈ ગયું.</b> મૂલ્યાંકન ખોલવા પાઠને પૂર્ણ તરીકે ચિહ્નિત કરો.', saving: 'સાચવી રહ્યું છે…', complete: '<b>પાઠ પૂર્ણ થયો.</b> મૂલ્યાંકન ખૂલી ગયું છે.', done: 'થઈ ગયું ✓', fail: 'સાચવી શકાયું નહીં — કૃપા કરીને ફરી પ્રયાસ કરો.' }
  };
  var S = T[L] || T.en;

  function vis(el) { return !!(el && (el.offsetWidth || el.offsetHeight || el.getClientRects().length)); }
  var items = [], byId = {};
  function need(id) { if (!byId[id]) { byId[id] = { ok: false }; items.push(byId[id]); } return byId[id]; }
  function satisfy(id) { if (byId[id] && !byId[id].ok) { byId[id].ok = true; render(); } }

  Array.prototype.forEach.call(document.querySelectorAll('.hb-flip__in'), function (el, i) {
    if (!vis(el)) { return; } var id = 'flip' + i; need(id);
    el.addEventListener('change', function () { if (el.checked) { satisfy(id); } });
  });
  var rg = {};
  Array.prototype.forEach.call(document.querySelectorAll('input[type=radio]'), function (el) {
    if (!vis(el) || !/^hb-(tp|st|lab)/.test(el.name || '')) { return; } (rg[el.name] = rg[el.name] || []).push(el);
  });
  Object.keys(rg).forEach(function (n) {
    rg[n].forEach(function (el, i) { var id = n + '#' + i; need(id); if (el.checked) { satisfy(id); }
      el.addEventListener('change', function () { if (el.checked) { satisfy(id); } }); });
  });
  Array.prototype.forEach.call(document.querySelectorAll('.hb-ask input[type=checkbox]'), function (el, i) {
    if (!vis(el)) { return; } var id = 'ask' + i; need(id);
    el.addEventListener('change', function () { if (el.checked) { satisfy(id); } });
  });
  var cg = {};
  Array.prototype.forEach.call(document.querySelectorAll('.hb-case__in'), function (el) {
    if (!vis(el)) { return; } (cg[el.name] = cg[el.name] || []).push(el);
  });
  Object.keys(cg).forEach(function (n) { var id = 'choice:' + n; need(id);
    cg[n].forEach(function (el) { el.addEventListener('change', function () { if (el.checked) { satisfy(id); } }); }); });

  // "read to the end" via a sentinel at the foot of the visible lesson column — works whichever element scrolls
  var scrolled = false;
  var vcol = Array.prototype.filter.call(document.querySelectorAll('.csfr-hb'), function (e) { return e.offsetParent !== null; })[0] || hb;
  var sentinel = document.createElement('div'); sentinel.className = 'csfr-gate__end'; sentinel.setAttribute('aria-hidden', 'true'); sentinel.style.height = '1px';
  vcol.appendChild(sentinel);
  function markScrolled() { if (!scrolled) { scrolled = true; render(); } }
  function checkSentinel() { if (scrolled) { return; } if (sentinel.getBoundingClientRect().top <= window.innerHeight + 8) { markScrolled(); } }
  // capture-phase catches scrolling on whichever element Moodle's layout actually scrolls, plus IntersectionObserver
  document.addEventListener('scroll', checkSentinel, true);
  window.addEventListener('resize', checkSentinel);
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) { markScrolled(); } }); },
      { rootMargin: '0px 0px 240px 0px' }).observe(sentinel);
  } else { markScrolled(); }

  var bar = document.createElement('div'); bar.className = 'csfr-gate'; bar.setAttribute('role', 'status');
  bar.innerHTML = '<div class="csfr-gate__in"><div class="csfr-gate__txt"></div>' +
    '<div class="csfr-gate__track"><i></i></div><button type="button" class="csfr-gate__btn" disabled>Finish lesson &amp; continue</button></div>';
  document.body.appendChild(bar);
  document.body.classList.add('csfr-has-gate');
  var txt = bar.querySelector('.csfr-gate__txt'), fill = bar.querySelector('.csfr-gate__track i'), btn = bar.querySelector('.csfr-gate__btn');
  btn.textContent = S.finish;

  function render() {
    if (!fill) { return; }                        // progress bar not built yet (an initial satisfy fired during setup)
    var done = items.filter(function (x) { return x.ok; }).length, total = items.length;
    fill.style.width = Math.round((done + (scrolled ? 1 : 0)) / (total + 1) * 100) + '%';
    if (done >= total && scrolled) {
      txt.innerHTML = S.allDone; btn.disabled = false;
    } else {
      var bits = [];
      if (done < total) { bits.push(S.left(total - done)); }
      if (!scrolled) { bits.push(S.read); }
      txt.innerHTML = S.toFinish + ' · ' + bits.join(' · '); btn.disabled = true;
    }
  }

  btn.addEventListener('click', function () {
    btn.disabled = true; btn.textContent = S.saving;
    try { localStorage.setItem(DONE, '1'); } catch (e) {}
    require(['core/ajax'], function (ajax) {
      ajax.call([{ methodname: 'core_completion_update_activity_completion_status_manually',
        args: { cmid: Number(cmid), completed: true } }])[0]
        .then(function () { txt.innerHTML = S.complete;
          btn.textContent = S.done; setTimeout(function () { location.reload(); }, 900); return 1; })
        .fail(function () { btn.disabled = false; btn.textContent = S.finish;
          txt.textContent = S.fail; });
    });
  });

  render(); checkScroll();
})();
</script>
HTML;
}
