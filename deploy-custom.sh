#!/usr/bin/env bash
# Copies our own Moodle plugins (custom/) into the running container, then upgrades Moodle and clears caches.
# Usage (Git Bash, from D:\csfr-lms):  ./deploy-custom.sh
set -euo pipefail
export MSYS_NO_PATHCONV=1
cd "$(dirname "$0")"

docker compose exec -T web rm -rf /var/www/html/public/theme/csfr /var/www/html/public/local/csfr
docker cp custom/theme/csfr csfr-lms-web-1:/var/www/html/public/theme/csfr
docker cp custom/local/csfr csfr-lms-web-1:/var/www/html/public/local/csfr
docker compose exec -T web chown -R www-data:www-data /var/www/html/public/theme/csfr /var/www/html/public/local/csfr
docker compose exec -T -u www-data web php admin/cli/upgrade.php --non-interactive | tail -3
docker compose exec -T -u www-data web php admin/cli/purge_caches.php

# A SCSS error does not break Moodle: it quietly serves Boost's stock CSS instead. Compile here so an error stops the
# deploy with its message, and so the first page load does not wait for the compile.
check='
    define("CLI_SCRIPT", true);
    require("/var/www/html/config.php");
    $CFG->debug = E_ALL; $CFG->debugdisplay = 1; $CFG->debugdeveloper = true;
    ob_start();
    $css = theme_config::load("csfr")->get_css_content_debug("scss", null, null);
    $log = ob_get_clean();
    if (strpos((string) $css, "--csfr-paper") === false) {
        preg_match("/Error while compiling SCSS: [^+]*/", $log, $m);
        echo "Theme SCSS did not compile: " . ($m[0] ?? "no message"), PHP_EOL;
        exit(1);
    }'
if ! out=$(docker compose exec -T -u www-data web php -r "$check" 2>&1); then
    echo "$out" | grep "did not compile" >&2
    exit 1
fi
docker compose exec -T -u www-data web php admin/cli/build_theme_css.php --themes=csfr > /dev/null 2>&1
echo "Custom code deployed."
