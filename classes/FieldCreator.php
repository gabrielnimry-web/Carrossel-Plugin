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

namespace local_coursecarousel;

/**
 * Creates course custom fields.
 */
class FieldCreator {

    /**
     * Cria um campo personalizado.
     *
     * @param array $linha Dados do campo personalizado.
     * @return void
     */
    public static function create(array $linha): void {

        global $DB;

        $shortname = trim($linha['shortname']);

        // Já existe?
        $existsql = "SELECT 1
                      FROM {customfield_field} f
                      JOIN {customfield_category} c ON c.id = f.categoryid
                     WHERE f.shortname = :shortname
                       AND c.component = :component
                       AND c.area = :area";
        if ($DB->record_exists_sql($existsql, [
            'shortname' => $shortname,
            'component' => 'core_course',
            'area' => 'course',
        ])) {

            return;
        }

        // Categoria dos campos.
        $categoryid = self::get_category();

        $record = new \stdClass();

        $record->shortname = $shortname;
        $record->name = trim($linha['name']);

        $record->type = trim($linha['type']);

        $record->description = trim($linha['description'] ?? '');
        $record->descriptionformat = FORMAT_HTML;

        $record->categoryid = $categoryid;

        $record->sortorder = (int)$linha['sortorder'];

        $record->configdata = [
            'required' => '0',
            'uniquevalues' => '0',
            'locked' => '0',
            'visibility' => '2',
        ];
        if ($record->type === 'select' && !empty($linha['options'])) {
            $record->configdata['options'] = str_replace('|', "\n", $linha['options']);
            $record->configdata['defaultvalue'] = '';
        }

        $field = \core_customfield\field_controller::create(0, (object)[
            'type' => $record->type,
            'categoryid' => $record->categoryid,
        ]);
        $formdata = \core_customfield\api::prepare_field_for_config_form($field);
        foreach (['shortname', 'name', 'type', 'description', 'descriptionformat', 'categoryid', 'sortorder'] as $property) {
            $formdata->{$property} = $record->{$property};
        }
        $formdata->configdata = array_merge((array)$formdata->configdata, $record->configdata);
        \core_customfield\api::save_field_configuration($field, $formdata);

    }

    /**
     * Obtém ou cria a categoria
     * Catálogo UFMG Digital.
     */
    private static function get_category(): int {

        global $DB;

        $configuredid = (int)get_config('local_coursecarousel', 'metadatacategoryid');
        if ($configuredid > 0 && $DB->record_exists('customfield_category', [
            'id' => $configuredid,
            'component' => 'core_course',
            'area' => 'course',
        ])) {
            return $configuredid;
        }

        $categoria = $DB->get_record(
            'customfield_category',
            [
                'name' => 'Metadados UFMG Digital',
                'component' => 'core_course',
                'area' => 'course',
            ]
        );

        if ($categoria) {
            set_config('metadatacategoryid', $categoria->id, 'local_coursecarousel');
            return $categoria->id;
        }

        $handler = \core_course\customfield\course_handler::create();
        $categoryid = $handler->create_category('Metadados UFMG Digital');
        set_config('metadatacategoryid', $categoryid, 'local_coursecarousel');
        return $categoryid;

    }

}
