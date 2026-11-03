<?php
// Plain wording for the first-login consent pages, written as Moodle's own local language customisation (en_local),
// the same files Site administration > Language > Language customisation writes. Core files stay untouched.
// Run: docker compose exec -u www-data web php /opt/csfr-setup/strings.php

define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');

$strings = [
    'tool_policy' => [
        'steppolicies' => 'Policy {$a->numpolicy} of {$a->totalpolicies}',
        'readpolicy' => 'Read this, then choose Next. You agree or decline on the last step.',
        'consentpagetitle' => 'Your consent',
        'agreepolicies' => 'Agree to each required policy to continue. Optional ones are your choice.',
        'mustagreetocontinue' => 'Answer each policy below to continue.',
        'refertofullpolicytext' => 'Read the full text: {$a}',
        'iagree' => 'I agree: {$a}',
        'idontagree' => 'I do not agree: {$a}',
        // Visitors who are not signed in see a notice, not a claim that browsing is consent.
        'guestconsentmessage' => 'How this site handles personal data:',
        'guestconsent:continue' => 'Got it',
    ],
];

$dir = $CFG->dataroot . '/lang/en_local';
check_dir_exists($dir);
foreach ($strings as $component => $list) {
    $php = "<?php\n// Written by setup/strings.php.\n\n";
    foreach ($list as $key => $value) {
        $php .= '$string[' . var_export($key, true) . '] = ' . var_export($value, true) . ";\n";
    }
    file_put_contents("$dir/$component.php", $php);
    cli_writeln("Wrote $dir/$component.php");
}
get_string_manager()->reset_caches();
