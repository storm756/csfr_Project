<?php
// Custom assessment viewer: serves teammate's HTML/JS assessments as full-page content
// under the Moodle navbar, without the SCORM player chrome.
require('../../config.php');
require_login();

$mod = required_param('m', PARAM_ALPHANUMEXT);
// Whitelist valid module codes.
$valid = ['m01','m02','m03','m04','m05','m06','m07','m08','m09','m11'];
if (!in_array($mod, $valid)) {
    throw new moodle_exception('invalidparam');
}

$base = __DIR__ . '/assess/' . $mod;
if (!is_dir($base)) {
    throw new moodle_exception('invalidparam');
}

// Serve sub-resources (CSS, JS) directly.
$res = optional_param('f', '', PARAM_PATH);
if ($res !== '') {
    $path = realpath($base . '/' . $res);
    // Security: must be inside the assess directory.
    if ($path === false || strpos($path, realpath($base)) !== 0) {
        throw new moodle_exception('invalidparam');
    }
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $types = ['css' => 'text/css', 'js' => 'text/javascript', 'json' => 'application/json',
              'png' => 'image/png', 'jpg' => 'image/jpeg', 'svg' => 'image/svg+xml',
              'gif' => 'image/gif', 'woff2' => 'font/woff2', 'woff' => 'font/woff'];
    $ct = $types[$ext] ?? 'application/octet-stream';
    header('Content-Type: ' . $ct);
    header('Cache-Control: public, max-age=86400');
    readfile($path);
    exit;
}

// Module names for the page title.
$names = [
    'm01' => 'Module 1', 'm02' => 'Module 2', 'm03' => 'Module 3',
    'm04' => 'Module 4', 'm05' => 'Module 5', 'm06' => 'Module 6',
    'm07' => 'Module 7', 'm08' => 'Module 8', 'm09' => 'Module 9',
    'm11' => 'Final Assessment',
];

$PAGE->set_url(new moodle_url('/local/csfr/assess.php', ['m' => $mod]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('embedded');
$PAGE->set_title($names[$mod] . ' Assessment — CCFR');

// Read the assessment HTML.
$html = file_get_contents($base . '/index.html');

// Rewrite relative resource paths to go through this same script.
$selfurl = (new moodle_url('/local/csfr/assess.php', ['m' => $mod]))->out(false);
$html = preg_replace('/(href|src)="(?!https?:\/\/|\/\/)([^"]+)"/', '$1="' . $selfurl . '&f=$2"', $html);

// Remove the SCORM height reporter script (not needed outside iframe).
$html = preg_replace('/<script>!function\(\)\{var s=function.*?<\/script>/', '', $html);

echo $OUTPUT->header();
// Output the assessment content directly in the page.
echo '<div id="csfr-assess-wrap" style="max-width:900px;margin:0 auto;padding:1rem 1rem 3rem">';
// Extract just the body content.
if (preg_match('/<body[^>]*>(.*)<\/body>/s', $html, $m)) {
    echo $m[1];
} else {
    echo $html;
}
echo '</div>';

// Include the CSS and JS from the assessment.
$cssurl = $selfurl . '&f=css/ccfr.css';
echo '<link rel="stylesheet" href="' . htmlspecialchars($cssurl) . '">';

// Include JS files in order.
$jsfiles = ['js/scorm-api.js', 'modules.js', 'js/questions.js', 'js/engine.js'];
foreach ($jsfiles as $jsf) {
    if (file_exists($base . '/' . $jsf)) {
        $jsurl = $selfurl . '&f=' . $jsf;
        echo '<script src="' . htmlspecialchars($jsurl) . '"></script>';
    }
}

echo $OUTPUT->footer();
