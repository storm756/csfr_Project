<?php
// Puts the showcase learner (demo.learner, Asha Desai) back to "consent not yet given", so the DPDP consent pages
// show again at her next login. Her course progress is kept. Run before a demo, or after rehearsing one:
// docker compose exec -u www-data web php /opt/csfr-setup/reset-showcase.php

define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');

$user = $DB->get_record('user', ['username' => 'demo.learner', 'deleted' => 0], '*', MUST_EXIST);
$removed = $DB->count_records('tool_policy_acceptances', ['userid' => $user->id]);
$DB->delete_records('tool_policy_acceptances', ['userid' => $user->id]);
$DB->set_field('user', 'policyagreed', 0, ['id' => $user->id]);
\core\session\manager::destroy_user_sessions($user->id);

cli_writeln("Asha Desai (demo.learner): removed $removed consent answer(s); the consent pages show at her next login.");
