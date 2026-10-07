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
require_once($CFG->libdir . '/formslib.php');

require_login();
require_capability('local/coursecarousel:manage', context_system::instance());

$url = new moodle_url('/local/coursecarousel/importar_csv.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title(get_string('importcsv', 'local_coursecarousel'));
$PAGE->set_heading(get_string('importcsv', 'local_coursecarousel'));
$PAGE->set_pagelayout('admin');

$form = new \local_coursecarousel\form\import_csv_form();

if ($form->get_data()) {
    $categoriescontent = $form->get_file_content('categoriesfile');
    $metadatacontent = $form->get_file_content('metadatafile');
    $profilecontent = has_capability('moodle/site:config', context_system::instance())
        ? $form->get_file_content('profilefile') : false;

    if ($categoriescontent === false && $metadatacontent === false && $profilecontent === false) {
        throw new moodle_exception('selectatleastonecsv', 'local_coursecarousel');
    }

    require_sesskey();
    // Validate profile definitions before processing the other uploaded files.
    if ($profilecontent !== false) {
        require_capability('moodle/site:config', context_system::instance());
        \local_coursecarousel\local\profile_field_csv::read_content($profilecontent);
    }
    $transaction = $DB->start_delegated_transaction();
    $messages = [];

    if ($categoriescontent !== false) {
        $rows = \local_coursecarousel\SpreadsheetReader::readcsvcontent($categoriescontent);
        $headers = $rows ? array_keys(reset($rows)) : [];
        $expectedheaders = [];
        foreach (array_keys($headers) as $index) {
            $expectedheaders[] = 'categoria' . ($index + 1);
        }
        if (!$headers || array_values($headers) !== $expectedheaders) {
            throw new moodle_exception('invalidcategoriescsv', 'local_coursecarousel');
        }
        \local_coursecarousel\MetadataImporter::import_categories($rows);
        $messages[] = get_string('categoriesimported', 'local_coursecarousel');
    }

    if ($metadatacontent !== false) {
        $rows = \local_coursecarousel\SpreadsheetReader::readcsvcontent($metadatacontent);
        $requiredheaders = ['shortname', 'name', 'type', 'description', 'sortorder', 'options'];
        $headers = $rows ? array_keys(reset($rows)) : [];
        if (array_diff($requiredheaders, $headers)) {
            throw new moodle_exception('invalidmetadatacsv', 'local_coursecarousel');
        }
        \local_coursecarousel\FieldImporter::import($rows);
        $messages[] = get_string('metadataimported', 'local_coursecarousel');
    }

    if ($profilecontent !== false) {
        $counts = \local_coursecarousel\local\profile_fields::import_csv_content($profilecontent);
        $messages[] = get_string('profilefieldsimported', 'local_coursecarousel', (object)$counts);
    }

    $transaction->allow_commit();
    redirect($url, implode(' ', $messages), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->notification(get_string('importcsvinfo', 'local_coursecarousel'), 'info');
$form->display();
echo $OUTPUT->footer();
