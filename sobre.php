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

require('../../config.php');
global $PAGE, $OUTPUT;
$context = context_system::instance();
$title = get_config('local_coursecarousel', 'abouttitle');
$content = get_config('local_coursecarousel', 'aboutcontent');
if (trim((string) $title) === '') {
    $title = get_string('about', 'local_coursecarousel');
}
if (trim((string) $content) === '') {
    $content = get_string('aboutcontent_default', 'local_coursecarousel');
}
$PAGE->set_url(new moodle_url('/local/coursecarousel/sobre.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($title));
$PAGE->set_heading(format_string($title));
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_coursecarousel/content_page', [
    'title' => format_string($title),
    'content' => format_text($content, FORMAT_HTML, ['context' => $context]),
]);
echo $OUTPUT->footer();
