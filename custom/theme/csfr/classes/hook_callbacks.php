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

namespace theme_csfr;

use core\hook\output\before_standard_top_of_body_html_generation;

/**
 * Hook callbacks for the CSFR theme.
 *
 * @package    theme_csfr
 * @copyright  2026 CyberPeace Foundation and SAKEC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Returns the small, learner-facing course-navigation dictionary. Moodle repeats activity names in the course
     * index, section page, breadcrumb and activity header, but this Moodle image has no core multilang filter.
     * Replacing visible labels leaves activity IDs, completion, grades and links untouched.
     *
     * @param string $lang Moodle language code.
     * @return array<string, mixed>
     */
    private static function course_labels(string $lang): array {
        $titles = [
            'hi' => ['साइबर स्वच्छता और डिजिटल सुरक्षा', 'ऑनलाइन धोखाधड़ी और ठगी', 'डीपफेक, AI खतरे और भ्रामक जानकारी', 'घटना की पहचान और प्राथमिकता निर्धारण', 'साक्ष्य संरक्षण', 'रिपोर्टिंग, अग्रेषण और कानूनी आधार', 'नागरिक मार्गदर्शन और पीड़ित सहायता', 'रैनसमवेयर, मैलवेयर और खाते पर अनधिकृत कब्ज़ा', 'IoT/OT और स्मार्ट-डिवाइस सुरक्षा'],
            'mr' => ['सायबर स्वच्छता आणि डिजिटल सुरक्षितता', 'ऑनलाइन फसवणूक आणि घोटाळे', 'डीपफेक, AI धोके आणि चुकीची माहिती', 'घटना ओळख आणि तातडीचे वर्गीकरण', 'पुरावे जतन करणे', 'तक्रार, पुढील कार्यवाही आणि कायद्याची मूलतत्त्वे', 'नागरिक मार्गदर्शन आणि पीडितांना मदत', 'रॅन्समवेअर, मालवेअर आणि खात्यावर अनधिकृत ताबा', 'IoT/OT आणि स्मार्ट उपकरणांची सुरक्षितता'],
            'gu' => ['સાયબર સ્વચ્છતા અને ડિજિટલ સુરક્ષા', 'ઓનલાઇન છેતરપિંડી અને કૌભાંડો', 'ડીપફેક, AI ખતરાં અને ભ્રામક માહિતી', 'ઘટનાની ઓળખ અને પ્રાથમિકતા નક્કી કરવી', 'પુરાવાનું સંરક્ષણ', 'રિપોર્ટિંગ, આગળ મોકલવું અને કાનૂની મૂળભૂત બાબતો', 'નાગરિક માર્ગદર્શન અને પીડિત સહાય', 'રેન્સમવેર, મેલવેર અને એકાઉન્ટ પર અનધિકૃત કબજો', 'IoT/OT અને સ્માર્ટ ઉપકરણોની સુરક્ષા'],
        ];
        $words = [
            'hi' => ['module' => 'मॉड्यूल', 'lesson' => 'पाठ', 'handout' => 'हैंडआउट', 'practice' => 'अभ्यास', 'check' => 'जाँच', 'assessment' => 'मूल्यांकन'],
            'mr' => ['module' => 'मॉड्यूल', 'lesson' => 'धडा', 'handout' => 'हँडआउट', 'practice' => 'सराव', 'check' => 'तपासणी', 'assessment' => 'मूल्यमापन'],
            'gu' => ['module' => 'મોડ્યુલ', 'lesson' => 'પાઠ', 'handout' => 'હેન્ડઆઉટ', 'practice' => 'અભ્યાસ', 'check' => 'તપાસ', 'assessment' => 'મૂલ્યાંકન'],
        ];
        $assessments = [
            'hi' => ['Phone Rescue' => 'फोन बचाव', 'Scam Busters Desk' => 'घोटाला पहचान डेस्क', 'The Verification Desk' => 'सत्यापन डेस्क', 'Triage Desk' => 'प्राथमिकता निर्धारण डेस्क', 'The Evidence Lab' => 'साक्ष्य प्रयोगशाला', 'The Routing Room' => 'अग्रेषण कक्ष', 'The Support Circuit' => 'सहायता चक्र', 'The Recovery Clinic' => 'पुनर्प्राप्ति केंद्र', 'Secure the Smart Home' => 'स्मार्ट घर को सुरक्षित करें'],
            'mr' => ['Phone Rescue' => 'फोन बचाव', 'Scam Busters Desk' => 'घोटाळा शोध डेस्क', 'The Verification Desk' => 'पडताळणी डेस्क', 'Triage Desk' => 'तातडीचे वर्गीकरण डेस्क', 'The Evidence Lab' => 'पुरावा प्रयोगशाळा', 'The Routing Room' => 'मार्गनियोजन कक्ष', 'The Support Circuit' => 'सहाय्य चक्र', 'The Recovery Clinic' => 'पुनर्प्राप्ती केंद्र', 'Secure the Smart Home' => 'स्मार्ट घर सुरक्षित करा'],
            'gu' => ['Phone Rescue' => 'ફોન બચાવ', 'Scam Busters Desk' => 'કૌભાંડ ઓળખ ડેસ્ક', 'The Verification Desk' => 'ચકાસણી ડેસ્ક', 'Triage Desk' => 'પ્રાથમિકતા ડેસ્ક', 'The Evidence Lab' => 'પુરાવા પ્રયોગશાળા', 'The Routing Room' => 'આગળ મોકલવાનો કક્ષ', 'The Support Circuit' => 'સહાય ચક્ર', 'The Recovery Clinic' => 'પુનઃપ્રાપ્તિ કેન્દ્ર', 'Secure the Smart Home' => 'સ્માર્ટ ઘર સુરક્ષિત કરો'],
        ];
        $fixed = [
            'hi' => ['Certificate in Cyber Incident First Response (Pilot 2026)' => 'साइबर घटना प्रथम प्रतिक्रिया प्रमाणपत्र (पायलट 2026)', 'Collapse all' => 'सभी समेटें', 'Start here' => 'यहाँ से शुरू करें', 'How this course works' => 'यह कोर्स कैसे काम करता है', 'How you are assessed' => 'आपका मूल्यांकन कैसे होगा', 'Participant handbook, all 11 modules' => 'प्रतिभागी हैंडबुक, सभी 11 मॉड्यूल', 'Help card: helplines and portals' => 'सहायता कार्ड: हेल्पलाइन और पोर्टल', 'Classroom attendance (15 contact hours)' => 'कक्षा उपस्थिति (15 संपर्क घंटे)', 'Day 1 pre-test (does not count)' => 'दिन 1 पूर्व-परीक्षण (अंकों में शामिल नहीं)'],
            'mr' => ['Certificate in Cyber Incident First Response (Pilot 2026)' => 'सायबर घटना प्रथम प्रतिसाद प्रमाणपत्र (पायलट 2026)', 'Collapse all' => 'सर्व आवरा', 'Start here' => 'येथून सुरू करा', 'How this course works' => 'हा अभ्यासक्रम कसा चालतो', 'How you are assessed' => 'तुमचे मूल्यांकन कसे होते', 'Participant handbook, all 11 modules' => 'सहभागी हँडबुक, सर्व 11 मॉड्यूल', 'Help card: helplines and portals' => 'मदत कार्ड: हेल्पलाइन आणि पोर्टल', 'Classroom attendance (15 contact hours)' => 'वर्ग उपस्थिती (15 संपर्क तास)', 'Day 1 pre-test (does not count)' => 'पहिल्या दिवसाची पूर्वचाचणी (गुणांमध्ये धरली जात नाही)'],
            'gu' => ['Certificate in Cyber Incident First Response (Pilot 2026)' => 'સાયબર ઘટના પ્રથમ પ્રતિભાવ પ્રમાણપત્ર (પાયલટ 2026)', 'Collapse all' => 'બધું સંકુચિત કરો', 'Start here' => 'અહીંથી શરૂ કરો', 'How this course works' => 'આ કોર્સ કેવી રીતે કાર્ય કરે છે', 'How you are assessed' => 'તમારું મૂલ્યાંકન કેવી રીતે થાય છે', 'Participant handbook, all 11 modules' => 'ભાગીદાર હેન્ડબુક, બધા 11 મોડ્યુલ', 'Help card: helplines and portals' => 'મદદ કાર્ડ: હેલ્પલાઇન અને પોર્ટલ', 'Classroom attendance (15 contact hours)' => 'વર્ગ હાજરી (15 સંપર્ક કલાક)', 'Day 1 pre-test (does not count)' => 'દિવસ 1 પૂર્વ-પરીક્ષણ (ગુણમાં ગણાતું નથી)'],
        ];
        return ['source' => ['Cyber hygiene and digital safety', 'Online fraud and scams', 'Deepfakes, AI threats and misinformation', 'Incident identification and triage', 'Evidence preservation', 'Reporting, escalation and legal basics', 'Citizen guidance and victim support', 'Ransomware, malware and account compromise', 'IoT/OT and smart-device safety'], 'titles' => $titles[$lang] ?? [], 'words' => $words[$lang] ?? [], 'assessments' => $assessments[$lang] ?? [], 'fixed' => $fixed[$lang] ?? []];
    }

    /**
     * Puts the 1930 / cybercrime.gov.in help strip in the footer of learner-facing course pages only.
     * It sits at the end of the page content (not pinned), and never appears on admin, the dashboard or the
     * front page, where it is only clutter.
     *
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function add_help_strip(before_standard_top_of_body_html_generation $hook): void {
        global $PAGE;

        if ($PAGE->theme->name !== 'csfr') {
            return;
        }
        // Only on learner-facing course pages (the course page, lessons, handouts, quizzes). This hook fires before
        // $PAGE->course is populated, so key off the page layout, which is set in time: 'course' and 'incourse'
        // cover course content and exclude admin, the dashboard, the front page and user profiles.
        if (!in_array($PAGE->pagelayout, ['course', 'incourse'], true)) {
            return;
        }
        $strip = \html_writer::span(get_string('helpstrip', 'theme_csfr'))
            . \html_writer::tag('button', '&times;', ['type' => 'button', 'class' => 'csfr-helpstrip__x',
                'data-action' => 'csfr-hide-helpstrip', 'aria-label' => 'Hide the helpline bar']);
        $html = \html_writer::div($strip, 'csfr-helpstrip', ['role' => 'note', 'data-region' => 'csfr-helpstrip']);
        // Remember a learner's dismissal for the browser session; hide with no flash if already dismissed.
        $html .= <<<'JS'
<script>
(function () {
  var bar = document.currentScript.previousElementSibling;
  if (!bar) { return; }
  try { if (sessionStorage.getItem('csfrHelpstripHidden') === '1') { bar.style.display = 'none'; } } catch (e) {}
  bar.querySelector('[data-action="csfr-hide-helpstrip"]').addEventListener('click', function () {
    bar.style.display = 'none';
    try { sessionStorage.setItem('csfrHelpstripHidden', '1'); } catch (e) {}
  });
})();
</script>
JS;
        $hook->add_html($html);

        // Show translated copies of course navigation labels in every Moodle region which repeats them. This runs
        // only for the three CSFR learner languages and deliberately does not touch question/lesson body text.
        $labels = self::course_labels(current_language());
        if (!$labels['titles']) {
            return;
        }
        $json = json_encode($labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $hook->add_html(<<<JS
<script>
(function () {
  var data = $json;
  function translated(text) {
    var t = text.replace(/^\s+|\s+$/g, '');
    if (data.fixed[t]) { return data.fixed[t]; }
    var m = t.match(/^Module\s+([1-9]):\s+(.+)$/);
    if (m) { return data.words.module + ' ' + m[1] + ': ' + data.titles[+m[1] - 1]; }
    m = t.match(/^Lesson:\s+(.+)$/);
    if (m) { var i = data.source.indexOf(m[1]); return i >= 0 ? data.words.lesson + ': ' + data.titles[i] : null; }
    m = t.match(/^Handout,\s+Module\s+([1-9])$/);
    if (m) { return data.words.handout + ', ' + data.words.module + ' ' + m[1]; }
    m = t.match(/^Module\s+([1-9])\s+(practice|check)$/);
    if (m) { return data.words.module + ' ' + m[1] + ' ' + data.words[m[2]]; }
    m = t.match(/^Module\s+([1-9])\s+assessment:\s+(.+)$/);
    if (m) { return data.words.module + ' ' + m[1] + ' ' + data.words.assessment + ': ' + (data.assessments[m[2]] || m[2]); }
    return null;
  }
  function apply(root) {
    var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {acceptNode: function (node) {
      return node.parentElement && !node.parentElement.closest('script, style, textarea, .que') ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
    }});
    var node, pending = [];
    while ((node = walker.nextNode())) { var value = translated(node.nodeValue); if (value) { pending.push([node, value]); } }
    pending.forEach(function (item) { item[0].nodeValue = item[1]; });
  }
  function run() {
    apply(document.querySelector('#course-index') || document.body);
    apply(document.querySelector('#region-main') || document.body);
    apply(document.querySelector('.breadcrumb') || document.body);
  }
  function watchDrawer() {
    run();
    var observer = new MutationObserver(run);
    observer.observe(document.body, {childList: true, subtree: true});
    window.setTimeout(function () { observer.disconnect(); }, 2500);
  }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', watchDrawer); } else { watchDrawer(); }
})();
</script>
JS);
    }
}
