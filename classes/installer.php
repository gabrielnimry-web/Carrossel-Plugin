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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/SpreadsheetReader.php');
require_once(__DIR__ . '/FieldImporter.php');
require_once(__DIR__ . '/FieldCreator.php');
require_once(__DIR__ . '/MetadataImporter.php');
require_once(__DIR__ . '/CategoryCreator.php');

/**
 * Creates missing course metadata and categories during installation or upgrade.
 */
class installer {

    /**
     * Reapplies the bundled course CSVs without overwriting existing records.
     */
    public static function install(): void {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $metadatacategoryid = self::create_metadata_category();
        set_config('metadatacategoryid', $metadatacategoryid, 'local_coursecarousel');
        self::create_course_fields();
        self::create_course_categories();
        $transaction->allow_commit();
    }

    /**
     * Creates the custom course fields declared by the plugin.
     */
    private static function create_course_fields(): void {
        $path = __DIR__ . '/../install/metadados.csv';
        FieldImporter::import(SpreadsheetReader::readcsv($path));
    }

    /**
     * Creates the category hierarchy declared by the plugin.
     */
    private static function create_course_categories(): void {
        $path = __DIR__ . '/../install/categorias.csv';
        MetadataImporter::import_categories(SpreadsheetReader::readcsv($path));
    }

    /**
     * Retorna a configuração dos metadados.
     *
     * @return array Configuração dos metadados.
     */
    private static function get_metadata() {

        return require(
            __DIR__.'/../install/metadata.php'
        );

    }

    /**
     * Cria a categoria de metadados.
     *
     * @return int Identificador da categoria.
     */
    private static function create_metadata_category() {

        global $DB;

        // The administrator may have renamed the managed metadata category.
        $configuredid = (int)get_config('local_coursecarousel', 'metadatacategoryid');
        if ($configuredid > 0 && $DB->record_exists('customfield_category', [
            'id' => $configuredid,
            'component' => 'core_course',
            'area' => 'course',
        ])) {
            return $configuredid;
        }

        $metadata = self::get_metadata();

        $component = 'core_course';
        $area      = 'course';
        $name      = $metadata['category']['name'];

        // Verifica se já existe.
        if ($category = $DB->get_record(
            'customfield_category',
            [
                'component' => $component,
                'area'      => $area,
                'name' => $name,
            ]
        )) {

            return $category->id;
        }

        $handler = \core_course\customfield\course_handler::create();
        return $handler->create_category($name);
    }
}
