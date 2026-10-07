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

if (get_config('local_coursecarousel', 'requireloginforvideos')) {
    require_login();
}

$source = required_param('source', PARAM_ALPHA);
$id = required_param('id', PARAM_RAW_TRIMMED);
$title = optional_param('title', get_string('video', 'local_coursecarousel'), PARAM_TEXT);
$area = optional_param('area', 'app', PARAM_ALPHA);

if (!preg_match('/^[A-Za-z0-9_-]+$/D', $id)) {
    throw new moodle_exception('invalidparameter');
}

if ($source === 'youtube' && preg_match('/^[A-Za-z0-9_-]{11}$/D', $id)) {
    $embedurl = \local_coursecarousel\local\video\endpoints::youtube_embed($id, true) . '?rel=0';
} else if ($source === 'eduplay' && in_array($area, ['app', 'portal'], true)) {
    $embedurl = \local_coursecarousel\local\video\endpoints::eduplay_embed($area, $id);
} else {
    throw new moodle_exception('invalidparameter');
}

$urlparams = ['source' => $source, 'id' => $id, 'title' => $title];
if ($source === 'eduplay') {
    $urlparams['area'] = $area;
}

$PAGE->set_url(new moodle_url('/local/coursecarousel/video.php', $urlparams));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($title));
$PAGE->set_heading(format_string($title));

// Return to the site home even when My courses is the default landing page.
$backurl = new moodle_url('/', ['redirect' => 0]);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_coursecarousel/video_page', [
    'title' => format_string($title),
    'embedurl' => $embedurl,
    'backurl' => $backurl->out(false),
]);
echo $OUTPUT->footer();
