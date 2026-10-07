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

require_once(
    $CFG->dirroot .
    '/local/coursecarousel/lib_local.php'
);

require_login();

require_capability(
    'local/coursecarousel:manage',
    context_system::instance()
);

$PAGE->set_context(
    context_system::instance()
);

$PAGE->set_url(
    new moodle_url(
        '/local/coursecarousel/atualizar_menu.php'
    )
);

$PAGE->set_title(get_string('updatemenu', 'local_coursecarousel'));

$PAGE->set_heading(get_string('updatemenu', 'local_coursecarousel'));

echo $OUTPUT->header();

$confirm = optional_param('confirm', 0, PARAM_BOOL);
if ($confirm) {
    require_sesskey();
    $menu = local_coursecarousel_update_custom_menu();
    echo $OUTPUT->notification(
        get_string('menuupdated', 'local_coursecarousel'),
        'notifysuccess'
    );
    echo html_writer::tag('h3', get_string('generatedcontent', 'local_coursecarousel'));
    echo html_writer::tag('pre', s($menu));
} else {
    echo $OUTPUT->notification(get_string('updatemenuwarning', 'local_coursecarousel'), 'warning');
    $url = new moodle_url('/local/coursecarousel/atualizar_menu.php', [
        'confirm' => 1,
        'sesskey' => sesskey(),
    ]);
    echo $OUTPUT->single_button($url, get_string('updatemenu', 'local_coursecarousel'), 'post');
}

echo $OUTPUT->footer();
