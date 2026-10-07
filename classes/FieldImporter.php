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
 * Imports course custom fields.
 */
class FieldImporter {

    /**
     * Importa todos os campos personalizados
     *
     * @param array $dados
     */
    public static function import(array $dados): void {

        foreach ($dados as $linha) {

            // Ignora linhas vazias.
            if (empty(array_filter($linha))) {
                continue;
            }

            FieldCreator::create($linha);

        }

    }

}
