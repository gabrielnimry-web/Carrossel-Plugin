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

namespace local_coursecarousel\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * AJAX endpoint for the dashboard course carousel.
 */
final class get_available_courses extends \external_api {
    /**
     * Describe the endpoint parameters.
     *
     * @return \external_function_parameters Endpoint parameters.
     */
    public static function execute_parameters(): \external_function_parameters {
        return new \external_function_parameters([]);
    }

    /**
     * Return the available-courses carousel.
     *
     * @return array Endpoint response.
     */
    public static function execute(): array {
        global $USER;

        require_login();
        self::validate_context(\context_system::instance());
        return [
            'html' => \local_coursecarousel\local\dashboard_content::render(
                (int)$USER->id,
                (string)$USER->firstname
            ),
        ];
    }

    /**
     * Describe the endpoint response.
     *
     * @return \external_single_structure Endpoint response structure.
     */
    public static function execute_returns(): \external_single_structure {
        return new \external_single_structure([
            'html' => new \external_value(PARAM_RAW, 'Rendered dashboard carousel HTML'),
        ]);
    }
}
