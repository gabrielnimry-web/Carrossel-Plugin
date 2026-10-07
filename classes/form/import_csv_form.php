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

namespace local_coursecarousel\form;

/**
 * CSV import form.
 */
class import_csv_form extends \moodleform {

    /**
     * Define the form fields.
     *
     * @return void
     */
    protected function definition(): void {
        global $CFG;

        $mform = $this->_form;
        $fileoptions = [
            'accepted_types' => ['.csv'],
            'maxbytes' => get_max_upload_file_size($CFG->maxbytes),
        ];

        $mform->addElement('filepicker', 'categoriesfile',
            get_string('categoriescsv', 'local_coursecarousel'), null, $fileoptions);
        $mform->addHelpButton('categoriesfile', 'categoriescsv', 'local_coursecarousel');

        $mform->addElement('filepicker', 'metadatafile',
            get_string('metadatacsv', 'local_coursecarousel'), null, $fileoptions);
        $mform->addHelpButton('metadatafile', 'metadatacsv', 'local_coursecarousel');

        if (has_capability('moodle/site:config', \context_system::instance())) {
            $mform->addElement('filepicker', 'profilefile',
                get_string('profilecsv', 'local_coursecarousel'), null, $fileoptions);
            $mform->addHelpButton('profilefile', 'profilecsv', 'local_coursecarousel');
        }

        $this->add_action_buttons(false, get_string('importcsv', 'local_coursecarousel'));
    }

}
