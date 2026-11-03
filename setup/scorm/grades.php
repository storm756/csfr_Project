<?php
// Re-point the course grade to the HOD's scheme: 50% the nine module assessments (equal), 50% the final assessment,
// everything else 0%. The nine module SCORMs move into a "Module assessments (50%)" category; the final SCORM and
// the three old buckets sit at the top level with explicit weights.
// Run: docker exec -u www-data csfr-lms-web-1 php /opt/csfr-setup/scorm/grades.php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/grade/grade_category.php');
require_once($CFG->libdir . '/grade/grade_item.php');

$courseid = 14;
$top = grade_category::fetch(['courseid' => $courseid, 'depth' => 1]);

// 1. A category for the nine module assessments, aggregated as a natural sum so each module is an equal 1/9 share
//    and a module not yet done counts as 0.
$cat = grade_category::fetch(['courseid' => $courseid, 'fullname' => 'Module assessments (50%)']);
if (!$cat) {
    $cat = new grade_category(['courseid' => $courseid, 'fullname' => 'Module assessments (50%)',
        'parent' => $top->id, 'aggregation' => GRADE_AGGREGATE_SUM], false);
    $cat->insert();
    cli_writeln('created category "Module assessments (50%)" id=' . $cat->id);
} else {
    cli_writeln('category already exists id=' . $cat->id);
}

// 2. Move the nine module-assessment grade items into it.
foreach (['csfr_assess1','csfr_assess2','csfr_assess3','csfr_assess4','csfr_assess5','csfr_assess6','csfr_assess7','csfr_assess8','csfr_assess9'] as $idn) {
    $cm = $DB->get_record('course_modules', ['idnumber' => $idn, 'course' => $courseid]);
    $s = $DB->get_record('scorm', ['id' => $cm->instance]);
    $gi = grade_item::fetch(['courseid' => $courseid, 'itemmodule' => 'scorm', 'iteminstance' => $s->id]);
    if ((int)$gi->categoryid !== (int)$cat->id) { $gi->set_parent($cat->id); }
}
cli_writeln('moved 9 module assessments into the category');
grade_regrade_final_grades($courseid);

// 3. Top-level weights: module-assessments category 50%, final 50%, the three old buckets 0%.
$cattotal = grade_item::fetch(['itemtype' => 'category', 'iteminstance' => $cat->id]);
$cattotal->weightoverride = 1; $cattotal->aggregationcoef2 = 0.5; $cattotal->update();

$finalcm = $DB->get_record('course_modules', ['idnumber' => 'csfr_final', 'course' => $courseid]);
$finalscorm = $DB->get_record('scorm', ['id' => $finalcm->instance]);
$final = grade_item::fetch(['courseid' => $courseid, 'itemmodule' => 'scorm', 'iteminstance' => $finalscorm->id]);
$final->weightoverride = 1; $final->aggregationcoef2 = 0.5; $final->update();

// the existing top-level category totals (old "counts", formative, practice) -> weight 0
foreach ($DB->get_records('grade_items', ['courseid' => $courseid, 'itemtype' => 'category']) as $g) {
    if ((int)$g->iteminstance === (int)$cat->id) { continue; }           // our new category keeps its 0.5
    $gi = grade_item::fetch(['id' => $g->id]);
    if ((int)$gi->categoryid === (int)$top->id) {                        // only the ones feeding the course total
        $gi->weightoverride = 1; $gi->aggregationcoef2 = 0; $gi->update();
    }
}
// and the final-bucket items / any stray graded items left at the top level -> 0
foreach ($DB->get_records('grade_items', ['courseid' => $courseid, 'categoryid' => $top->id]) as $g) {
    if ($g->itemtype === 'course' || $g->itemtype === 'category') { continue; }
    if ((int)$g->id === (int)$final->id) { continue; }
    $gi = grade_item::fetch(['id' => $g->id]);
    $gi->weightoverride = 1; $gi->aggregationcoef2 = 0; $gi->update();
}

grade_regrade_final_grades($courseid);
cli_writeln('weights set: module assessments 50%, final 50%, everything else 0%');
cli_writeln('done');
