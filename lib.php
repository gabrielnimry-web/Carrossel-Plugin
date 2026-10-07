<?php
// This file is part of Moodle - http://moodle.org/.
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Local Course Carousel plugin.
 *
 * @package    local_coursecarousel
 * @copyright  2026 Universidade Federal de Minas Gerais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/renderer.php');

/**
 * Return a boolean plugin setting while preserving the enabled legacy default.
 *
 * @param string $name Setting name.
 * @return bool Whether the setting is enabled.
 */
function local_coursecarousel_config_enabled(string $name): bool {
    $value = get_config('local_coursecarousel', $name);
    return $value === false ? true : !empty($value);
}

/**
 * Load plugin styles before HTTP headers are sent.
 *
 * @return void
 */
function local_coursecarousel_before_http_headers() {

    global $PAGE;

    // Login identity presentation is owned by the active theme.

    $pluginpages = strpos((string)$PAGE->pagetype, 'local-coursecarousel-') === 0;
    $homepageenabled = local_coursecarousel_config_enabled('enablehomepage');
    $dashboardenabled = local_coursecarousel_config_enabled('enabledashboardcarousel');
    if ($pluginpages || ($homepageenabled && $PAGE->pagetype === 'site-index') ||
            ($dashboardenabled && $PAGE->pagetype === 'my-index')) {
        if (empty($PAGE->theme->coursecarouselpresentation)) {
            $PAGE->requires->css('/local/coursecarousel/styles.css');
        }
        $PAGE->requires->js_call_amd('local_coursecarousel/course_info', 'init');
    }

}

/**
 * Add plugin content and scripts before the page footer.
 *
 * @return string Additional page HTML.
 */
function local_coursecarousel_before_footer() {

    global $PAGE;

    $output = '';
    // Home page.
    if (local_coursecarousel_config_enabled('enablehomepage') && $PAGE->pagetype === 'site-index') {

        $output .= local_coursecarousel_render();
    }

    if (local_coursecarousel_config_enabled('enabledashboardcarousel') && $PAGE->pagetype === 'my-index') {
        $PAGE->requires->js_call_amd(
            'local_coursecarousel/dashboard',
            'init'
        );
    }
    // VLibras accessibility widget.
    if (local_coursecarousel_config_enabled('enablevlibras')) {
        $output .= '
    <div vw class="enabled">
        <div vw-access-button class="active"></div>

        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
        ';
        $PAGE->requires->js_call_amd('local_coursecarousel/vlibras', 'init', [
            'https://vlibras.gov.br/app/vlibras-plugin.js',
            'https://vlibras.gov.br/app',
        ]);
    }

    return $output;
}
