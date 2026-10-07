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
 * Imports category metadata.
 */
class MetadataImporter {

    /**
     * Import course categories from tabular data.
     *
     * @param array $dados Category rows.
     * @return void
     */
    public static function import_categories(array $dados) {

        foreach ($dados as $linha) {

            $parent = 0;

            foreach ($linha as $campo => $valor) {

                if (stripos($campo, 'categoria') !== 0) {
                    continue;
                }

                $valor = trim($valor);

                if ($valor === '') {
                    continue;
                }

                $parent = CategoryCreator::get_or_create(
                    $valor,
                    $parent
                );

            }

        }

    }
}
