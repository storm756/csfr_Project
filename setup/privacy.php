<?php
// DPDP Act 2023 set-up for the CSFR demo: consent policies, privacy officer, data requests, retention.
//
// Run inside the web container after build.php:
//   php /opt/csfr-setup/privacy.php
// The policy texts are drafts for CyberPeace Foundation's legal review, not legal advice.

define('CLI_SCRIPT', true);

require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');

\core\session\manager::set_user(get_admin());

// ---------------------------------------------------------------- Consent policies shown at sign-up and first login.
set_config('sitepolicyhandler', 'tool_policy');

$policies = [
    [
        'name' => 'Privacy notice and consent (DPDP Act, 2023)',
        'optional' => 0,
        'summary' => 'What personal data the programme collects, why, how long it is kept, and your rights.',
        'content' => '<p><strong>Draft for legal review.</strong></p>'
            . '<h4>Who processes your data</h4><p>CyberPeace Foundation is the Data Fiduciary for this programme. '
            . 'Shah &amp; Anchor Kutchhi Engineering College (SAKEC), Mumbai, runs the training and this website on its behalf.</p>'
            . '<h4>What we collect</h4><ul><li>Name, email address, district, the group you belong to, preferred language and '
            . 'confirmation that you are 16 or older.</li><li>Your learning records: lessons opened, quiz answers, attendance, '
            . 'assessment results and the anonymised practicum records you upload.</li><li>Your certificate and its verification code.</li></ul>'
            . '<h4>Why</h4><ul><li>To deliver the training, assess you and issue a certificate that others can verify.</li>'
            . '<li>To report <em>aggregate</em> numbers (people trained, knowledge gain) to the programme\'s CSR funder. '
            . 'No individual is named in these reports.</li></ul>'
            . '<h4>How long</h4><p>Learning records are kept for 3 years after the programme ends, then deleted. The certificate '
            . 'verification record is kept so employers and others can check your certificate, until you ask us to remove it.</p>'
            . '<h4>Your rights</h4><p>You can ask for a copy of your data, ask us to correct or erase it, withdraw consent, and '
            . 'complain to our grievance officer using "Contact the privacy officer" at the bottom of every page. If we do not '
            . 'resolve your complaint, you can approach the Data Protection Board of India.</p>'
            . '<h4>Participants under 18</h4><p>If you are under 18, a parent or guardian must give consent for you.</p>'
            . '<h4>What we never do</h4><p>We never sell your data, never show advertising and never send your data to tracking '
            . 'or analytics companies.</p>',
    ],
    [
        'name' => 'First Responder code of conduct',
        'optional' => 0,
        // Learners answer this one when they sign in; it is not part of the notice visitors see.
        'audience' => \tool_policy\policy_version::AUDIENCE_LOGGEDIN,
        'summary' => 'The rules every First Responder agrees to before helping citizens.',
        'content' => '<ul><li>I will never ask anyone for their OTP, PIN, CVV or password.</li>'
            . '<li>I will not take custody of anyone\'s phone, private images or money.</li>'
            . '<li>I will not contact, bait or investigate a suspect, and I will not promise that money will be recovered.</li>'
            . '<li>I will collect only the details a case needs, with consent, and remove names and numbers before uploading.</li>'
            . '<li>I will never download, keep or forward sexual material involving children; I will report it.</li>'
            . '<li>I will escalate every Red, child-related or large-loss case to my mentor.</li></ul>',
    ],
    [
        'name' => 'Research use of anonymised data (optional)',
        'optional' => 1,
        // An optional consent can only be given by answering it, never by browsing, so visitors are not shown it.
        'audience' => \tool_policy\policy_version::AUDIENCE_LOGGEDIN,
        'summary' => 'Allow your anonymised quiz and practicum results to be used in the programme\'s published research.',
        'content' => '<p>The programme will publish an open-access research paper on how well the training works. With your '
            . 'permission, your <strong>anonymised</strong> results (pre-test and post-test scores, practicum completion) may be '
            . 'included. Your name, email and district are never published. Saying no does not affect your certificate, and you '
            . 'can change your answer at any time from your profile.</p>',
    ],
];

foreach ($policies as $p) {
    $existing = $DB->get_record_sql('SELECT v.* FROM {tool_policy_versions} v WHERE v.name = ? ORDER BY v.id DESC',
        [$p['name']], IGNORE_MULTIPLE);
    if ($existing) {
        cli_writeln("Policy exists: {$p['name']}");
        continue;
    }
    $form = (object)[
        'name' => $p['name'], 'type' => \tool_policy\policy_version::TYPE_PRIVACY,
        'audience' => $p['audience'] ?? \tool_policy\policy_version::AUDIENCE_ALL,
        'optional' => $p['optional'], 'agreementstyle' => \tool_policy\policy_version::AGREEMENTSTYLE_CONSENTPAGE,
        'revision' => 'Nov 2026 draft',
        'summary_editor' => ['text' => $p['summary'], 'format' => FORMAT_HTML, 'itemid' => 0],
        'content_editor' => ['text' => $p['content'], 'format' => FORMAT_HTML, 'itemid' => 0],
    ];
    $version = \tool_policy\api::form_policydoc_add($form);
    \tool_policy\api::make_current($version->get('id'));
    cli_writeln("Policy created: {$p['name']}");
}

// ---------------------------------------------------------------- Privacy officer and data requests.
$managerroleid = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
set_config('contactdataprotectionofficer', 1, 'tool_dataprivacy');
set_config('dporoles', $managerroleid, 'tool_dataprivacy');
set_config('automaticdataexportapproval', 0, 'tool_dataprivacy');
set_config('automaticdatadeletionapproval', 0, 'tool_dataprivacy');
set_config('showdataretentionsummary', 1, 'tool_dataprivacy');
if ($lead = $DB->get_record('user', ['username' => 'demo.lead', 'deleted' => 0])) {
    role_assign($managerroleid, $lead->id, context_system::instance()->id);
    cli_writeln('demo.lead is the privacy / grievance officer.');
}

// ---------------------------------------------------------------- Retention: purposes and categories.
$purposes = [
    'Programme delivery and certification' => 'P3Y',
    'Account and contact details' => 'P3Y',
];
$made = [];
foreach ($purposes as $name => $period) {
    if (!$purpose = \tool_dataprivacy\purpose::get_record(['name' => $name])) {
        $purpose = \tool_dataprivacy\api::create_purpose((object)[
            'name' => $name,
            'description' => 'Consent under section 6 of the DPDP Act, 2023. Deleted ' . substr($period, 1, 1)
                . ' years after the programme ends.',
            'lawfulbases' => 'gdpr_art_6_1_a',
            'retentionperiod' => $period,
            'protected' => 0,
        ]);
    }
    $made[$name] = $purpose;
}
if (!$category = \tool_dataprivacy\category::get_record(['name' => 'Learner records'])) {
    $category = \tool_dataprivacy\api::create_category((object)['name' => 'Learner records',
        'description' => 'Learning activity, attendance, assessment and anonymised practicum uploads.']);
}
\tool_dataprivacy\api::set_contextlevel((object)['contextlevel' => CONTEXT_SYSTEM,
    'purposeid' => $made['Programme delivery and certification']->get('id'), 'categoryid' => $category->get('id')]);
\tool_dataprivacy\api::set_contextlevel((object)['contextlevel' => CONTEXT_USER,
    'purposeid' => $made['Account and contact details']->get('id'), 'categoryid' => $category->get('id')]);

// ---------------------------------------------------------------- Smaller privacy defaults.
set_config('hiddenuserfields', 'description,city,country,timezone,firstaccess,lastaccess,lastip,mycourses,groups,suspended');
set_config('showuseridentity', 'email');
set_config('verifyallcertificates', 1, 'customcert');

// ---------------------------------------------------------------- Dummy consent records.
// Every demo account except the showcase learner has already answered, so demonstrations go straight in;
// demo.learner meets the consent screen at first login. About seven in ten agree to the optional research use.
$versions = $DB->get_records_sql('SELECT v.id, v.optional FROM {tool_policy_versions} v
                                    JOIN {tool_policy} p ON p.currentversionid = v.id');
mt_srand(7);
$answered = 0;
$users = $DB->get_records_select('user', "(username LIKE 'demo.%' AND username <> 'demo.learner' OR username = 'admin') AND deleted = 0");
foreach ($users as $u) {
    $accept = $decline = [];
    foreach ($versions as $v) {
        if ($v->optional && $u->username !== 'admin' && mt_rand(1, 10) > 7) {
            $decline[] = $v->id;
        } else {
            $accept[] = $v->id;
        }
    }
    \core\session\manager::set_user($u);
    \tool_policy\api::accept_policies($accept);
    if ($decline) {
        \tool_policy\api::decline_policies($decline);
    }
    $answered++;
}
\core\session\manager::set_user(get_admin());
cli_writeln("Consent recorded for $answered demo accounts (demo.learner left to answer at first login).");

purge_all_caches();
cli_writeln('Privacy set-up done.');
